<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Klucz główny
            $table->id();

            // Podstawowe dane z indeksami
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->string('email', 100);
            $table->string('password')->nullable();
            $table->timestamp('email_verified_at')->nullable()->index();
            $table->string('phone', 20)->nullable();
            $table->boolean('phone_verified')->default(false)->index();

            // Profil
            $table->text('bio')->nullable();
            $table->string('location', 100)->nullable()->index();
            $table->date('birth_date')->nullable()->index();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable()->index();

            // JSON pola
            $table->json('preferences')->nullable();
            $table->json('notification_settings')->nullable();

            // Generowane kolumny dla często wyszukiwanych wartości JSON
            $table->boolean('email_notifications_enabled')->virtualAs("json_extract(preferences, '$.email_notifications')")->nullable()->index();
            $table->boolean('notifications_enabled')->virtualAs("json_extract(notification_settings, '$.enabled')")->nullable()->index();
            $table->string('preferred_language')->virtualAs("json_extract(preferences, '$.language')")->nullable()->index();

            // Status z indeksami
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_login_at')->nullable()->index();
            $table->string('last_login_ip', 45)->nullable()->index();

            // Standardowe pola
            $table->rememberToken()->index();
            $table->softDeletes()->index();
            $table->timestamps();

            // Kluczowe indeksy
            $table->unique('email');
            $table->unique('phone');

            // Kompozytowe indeksy
            $table->index(['email', 'password']);
            $table->index(['email', 'is_active']);
            $table->index(['first_name', 'last_name']);
            $table->index(['last_login_at', 'is_active']);
            $table->index(['created_at', 'is_active']);
            $table->index(['email', 'email_verified_at']);
            $table->index(['phone', 'phone_verified']);

            // Fulltext indeksy
            $table->fullText(['first_name', 'last_name', 'email']);
            $table->fullText('bio');
        });

        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider', 50);
            $table->string('provider_id', 100);
            $table->string('provider_token')->nullable();
            $table->string('provider_refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable()->index(); // tylko jeden indeks
            $table->string('provider_nickname', 100)->nullable()->index();
            $table->timestamps();

            // Indeksy bez duplikatów
            $table->unique(['provider', 'provider_id']);
            $table->index('provider_token');
            $table->index(['user_id', 'provider']);
        });

        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_id', 100);
            $table->string('device_type', 20);
            $table->string('device_name', 100)->index();
            $table->string('push_token')->nullable();
            $table->timestamp('last_used_at')->index();
            $table->timestamps();

            $table->unique('device_id');
            $table->index('push_token');
            $table->index(['user_id', 'device_type']);
            $table->index(['device_type', 'last_used_at']);
            $table->index(['user_id', 'last_used_at']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 100)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable()->index();

            $table->index('token');
            $table->index(['email', 'created_at']);
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_id', 100)->nullable();
            $table->longText('payload');
            $table->integer('last_activity');

            $table->index('user_id');
            $table->index('last_activity');
            $table->index('ip_address');
            $table->index('device_id');
            $table->index(['user_id', 'last_activity']);
            $table->index(['ip_address', 'last_activity']);
        });

        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('code', 20);
            $table->string('type', 20);
            $table->boolean('used')->default(false)->index();
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->index('code');
            $table->index(['user_id', 'type', 'used']);
            $table->index(['type', 'used', 'expires_at']);
            $table->index(['user_id', 'type', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_codes');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('social_accounts');

        // Usunięcie indeksów JSON przed usunięciem tabeli
        DB::statement('ALTER TABLE users DROP INDEX IF EXISTS preferences_index');
        DB::statement('ALTER TABLE users DROP INDEX IF EXISTS notification_settings_index');

        Schema::dropIfExists('users');
    }
};
