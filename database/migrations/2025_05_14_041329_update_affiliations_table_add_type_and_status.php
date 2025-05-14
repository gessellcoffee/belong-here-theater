<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Updates the affiliations table to:
     * 1. Modify the 'type' column to use an enum with specific affiliation types
     * 2. Add a confirmation_status column
     */
    public function up(): void
    {
        Schema::table('affiliations', function (Blueprint $table) {
            // Drop the existing type column to recreate it with enum
            $table->dropColumn('type');
        });

        Schema::table('affiliations', function (Blueprint $table) {
            // Recreate the type column with enum values
            // B2B = Business to Business
            // U2U = User to User
            // B2U = Business to User
            // U2B = User to Business
            $table->enum('type', ['B2B', 'U2U', 'B2U', 'U2B'])->after('id');
            
            // Add confirmation status column
            // null = pending, true = confirmed, false = rejected
            $table->boolean('confirmation_status')->nullable()->after('user_id')
                  ->comment('null = pending, true = confirmed, false = rejected');
            
            // Add columns for the requesting and confirming entities
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->after('confirmation_status');
            $table->foreignId('requested_by_company_id')->nullable()->constrained('companies')->after('requested_by_user_id');
            $table->timestamp('confirmed_at')->nullable()->after('requested_by_company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliations', function (Blueprint $table) {
            // Drop the new columns
            $table->dropColumn([
                'confirmation_status',
                'requested_by_user_id',
                'requested_by_company_id',
                'confirmed_at'
            ]);
            
            // Drop the type column to recreate it as string
            $table->dropColumn('type');
        });

        Schema::table('affiliations', function (Blueprint $table) {
            // Recreate the original type column
            $table->string('type')->after('id');
        });
    }
};
