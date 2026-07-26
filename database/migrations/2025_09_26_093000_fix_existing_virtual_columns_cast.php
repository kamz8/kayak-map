<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing broken virtual columns
        DB::statement("ALTER TABLE users DROP COLUMN email_notifications_enabled");
        DB::statement("ALTER TABLE users DROP COLUMN notifications_enabled");
        DB::statement("ALTER TABLE users DROP COLUMN preferred_language");

        // Add fixed virtual columns with proper CAST
        DB::statement("
            ALTER TABLE users ADD COLUMN email_notifications_enabled TINYINT(1)
            AS (CAST(JSON_EXTRACT(preferences, '$.email_notifications') AS UNSIGNED))
            VIRTUAL
        ");

        DB::statement("
            ALTER TABLE users ADD COLUMN notifications_enabled TINYINT(1)
            AS (CAST(JSON_EXTRACT(notification_settings, '$.enabled') AS UNSIGNED))
            VIRTUAL
        ");

        DB::statement("
            ALTER TABLE users ADD COLUMN preferred_language VARCHAR(255)
            AS (JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.language')))
            VIRTUAL
        ");

        // Recreate indexes
        DB::statement("CREATE INDEX users_email_notifications_enabled_index ON users (email_notifications_enabled)");
        DB::statement("CREATE INDEX users_notifications_enabled_index ON users (notifications_enabled)");
        DB::statement("CREATE INDEX users_preferred_language_index ON users (preferred_language)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the fixed virtual columns
        DB::statement("ALTER TABLE users DROP COLUMN email_notifications_enabled");
        DB::statement("ALTER TABLE users DROP COLUMN notifications_enabled");
        DB::statement("ALTER TABLE users DROP COLUMN preferred_language");

        // Restore original broken virtual columns
        DB::statement("
            ALTER TABLE users ADD COLUMN email_notifications_enabled TINYINT(1)
            AS (JSON_EXTRACT(preferences, '$.email_notifications'))
            VIRTUAL
        ");

        DB::statement("
            ALTER TABLE users ADD COLUMN notifications_enabled TINYINT(1)
            AS (JSON_EXTRACT(notification_settings, '$.enabled'))
            VIRTUAL
        ");

        DB::statement("
            ALTER TABLE users ADD COLUMN preferred_language VARCHAR(255)
            AS (JSON_EXTRACT(preferences, '$.language'))
            VIRTUAL
        ");

        // Recreate indexes
        DB::statement("CREATE INDEX users_email_notifications_enabled_index ON users (email_notifications_enabled)");
        DB::statement("CREATE INDEX users_notifications_enabled_index ON users (notifications_enabled)");
        DB::statement("CREATE INDEX users_preferred_language_index ON users (preferred_language)");
    }
};