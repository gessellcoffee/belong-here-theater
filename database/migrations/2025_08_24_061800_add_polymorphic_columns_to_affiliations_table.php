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
        Schema::table('affiliations', function (Blueprint $table) {
            // Add polymorphic columns to support any affiliatable model
            $table->string('affiliatable_type')->after('type');
            $table->unsignedBigInteger('affiliatable_id')->after('affiliatable_type');
            
            // Add index for polymorphic relationship
            $table->index(['affiliatable_type', 'affiliatable_id']);
            
            // Add role column to specify the type of affiliation (organizer, sponsor, venue, etc.)
            $table->string('role')->nullable()->after('affiliatable_id')
                ->comment('Role in the affiliation: organizer, sponsor, venue, performer, etc.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliations', function (Blueprint $table) {
            $table->dropIndex(['affiliatable_type', 'affiliatable_id']);
            $table->dropColumn(['affiliatable_type', 'affiliatable_id', 'role']);
        });
    }
};
