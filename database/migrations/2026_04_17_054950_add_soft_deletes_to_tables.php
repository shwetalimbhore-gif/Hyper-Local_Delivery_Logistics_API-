<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add soft delete to users table
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft delete to riders table
        if (Schema::hasTable('riders') && !Schema::hasColumn('riders', 'deleted_at')) {
            Schema::table('riders', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft delete to parcels table
        if (Schema::hasTable('parcels') && !Schema::hasColumn('parcels', 'deleted_at')) {
            Schema::table('parcels', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft delete to hubs table
        if (Schema::hasTable('hubs') && !Schema::hasColumn('hubs', 'deleted_at')) {
            Schema::table('hubs', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft delete to payments table
        if (Schema::hasTable('payments') && !Schema::hasColumn('payments', 'deleted_at')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('riders', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('parcels', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('hubs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
