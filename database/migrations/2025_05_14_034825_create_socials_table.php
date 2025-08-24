<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('socials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('icon');
            $table->foreignId('entity_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('entity_type', ['user', 'entity']);
            $table->timestamps();
        });

        // For SQLite, we can use triggers to enforce our constraints
        // This trigger ensures entity_type matches the provided ID
        DB::unprepared('CREATE TRIGGER enforce_entity_type_match
            BEFORE INSERT ON socials
            FOR EACH ROW
            BEGIN
                SELECT CASE
                    WHEN (NEW.entity_type = "user" AND (NEW.user_id IS NULL OR NEW.entity_id IS NOT NULL))
                        OR (NEW.entity_type = "entity" AND (NEW.entity_id IS NULL OR NEW.user_id IS NOT NULL))
                    THEN RAISE(ABORT, "Entity type must match the provided ID")
                END;
            END;
        ');

        // This trigger ensures at least one ID is provided
        DB::unprepared('CREATE TRIGGER enforce_id_provided
            BEFORE INSERT ON socials
            FOR EACH ROW
            BEGIN
                SELECT CASE
                    WHEN NEW.user_id IS NULL AND NEW.entity_id IS NULL
                    THEN RAISE(ABORT, "Either user_id or entity_id must be provided")
                END;
            END;
        ');

        // Also enforce constraints on updates
        DB::unprepared('CREATE TRIGGER enforce_entity_type_match_update
            BEFORE UPDATE ON socials
            FOR EACH ROW
            BEGIN
                SELECT CASE
                    WHEN (NEW.entity_type = "user" AND (NEW.user_id IS NULL OR NEW.entity_id IS NOT NULL))
                        OR (NEW.entity_type = "entity" AND (NEW.entity_id IS NULL OR NEW.user_id IS NOT NULL))
                    THEN RAISE(ABORT, "Entity type must match the provided ID")
                END;
            END;
        ');

        DB::unprepared('CREATE TRIGGER enforce_id_provided_update
            BEFORE UPDATE ON socials
            FOR EACH ROW
            BEGIN
                SELECT CASE
                    WHEN NEW.user_id IS NULL AND NEW.entity_id IS NULL
                    THEN RAISE(ABORT, "Either user_id or entity_id must be provided")
                END;
            END;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the triggers first
        DB::unprepared('DROP TRIGGER IF EXISTS enforce_entity_type_match');
        DB::unprepared('DROP TRIGGER IF EXISTS enforce_id_provided');
        DB::unprepared('DROP TRIGGER IF EXISTS enforce_entity_type_match_update');
        DB::unprepared('DROP TRIGGER IF EXISTS enforce_id_provided_update');

        Schema::dropIfExists('socials');
    }
};
