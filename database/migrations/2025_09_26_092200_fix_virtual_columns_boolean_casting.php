<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check and add missing virtual columns with proper boolean casting
        try {
            DB::statement("
                ALTER TABLE users ADD COLUMN email_notifications_enabled BOOLEAN
                AS (CAST(JSON_EXTRACT(preferences, '$.email_notifications') AS UNSIGNED))
                VIRTUAL
            ");
            DB::statement("CREATE INDEX idx_users_email_notifications_enabled ON users (email_notifications_enabled)");
        } catch (\Exception $e) {
            // Column already exists, ignore
        }

        try {
            DB::statement("
                ALTER TABLE users ADD COLUMN notifications_enabled BOOLEAN
                AS (CAST(JSON_EXTRACT(notification_settings, '$.enabled') AS UNSIGNED))
                VIRTUAL
            ");
            DB::statement("CREATE INDEX idx_users_notifications_enabled ON users (notifications_enabled)");
        } catch (\Exception $e) {
            // Column already exists, ignore
        }

        try {
            DB::statement("
                ALTER TABLE users ADD COLUMN preferred_language VARCHAR(10)
                AS (JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.language')))
                VIRTUAL
            ");
            DB::statement("CREATE INDEX idx_users_preferred_language ON users (preferred_language)");
        } catch (\Exception $e) {
            // Column already exists, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes first
        DB::statement("DROP INDEX idx_users_email_notifications_enabled ON users");
        DB::statement("DROP INDEX idx_users_notifications_enabled ON users");
        DB::statement("DROP INDEX idx_users_preferred_language ON users");

        // Drop the fixed virtual columns
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_notifications_enabled',
                'notifications_enabled',
                'preferred_language'
            ]);
        });

        // Restore original virtual columns (broken ones)
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email_notifications_enabled')
                ->virtualAs("json_extract(preferences, '$.email_notifications')")
                ->nullable()
                ->index();

            $table->boolean('notifications_enabled')
                ->virtualAs("json_extract(notification_settings, '$.enabled')")
                ->nullable()
                ->index();

            $table->string('preferred_language')
                ->virtualAs("json_extract(preferences, '$.language')")
                ->nullable()
                ->index();
        });
    }
};