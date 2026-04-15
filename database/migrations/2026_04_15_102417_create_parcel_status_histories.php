<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcel_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcel_id')->constrained('parcels')->onDelete('cascade');
            $table->foreignId('status_id')->constrained('parcel_statuses');
            $table->foreignId('from_status_id')->nullable()->constrained('parcel_statuses');
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();

            $table->index(['parcel_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcel_status_histories');
    }
};
