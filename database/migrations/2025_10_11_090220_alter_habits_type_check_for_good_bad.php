<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF;');

        DB::statement('DROP TABLE IF EXISTS habits_tmp');

        DB::statement("
            CREATE TABLE habits_tmp (
                id integer primary key autoincrement,
                user_id integer not null,
                name varchar not null,
                type varchar not null CHECK (type in ('good_habit','bad_habit')),
                is_active integer not null default 1,
                created_at datetime,
                updated_at datetime,
                foreign key(user_id) references users(id) on delete cascade
            )
        ");

        DB::statement("
            INSERT INTO habits_tmp (id, user_id, name, type, is_active, created_at, updated_at)
            SELECT id, user_id, name,
                   CASE type
                     WHEN 'positive' THEN 'good_habit'
                     WHEN 'stop'     THEN 'bad_habit'
                     ELSE type
                   END as type,
                   is_active, created_at, updated_at
            FROM habits
        ");

        // swap
        DB::statement('DROP TABLE habits');
        DB::statement('ALTER TABLE habits_tmp RENAME TO habits');

        DB::statement('PRAGMA foreign_keys=ON;');
    }

    public function down(): void
    {
    }
};
