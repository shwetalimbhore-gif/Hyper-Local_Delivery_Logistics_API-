<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('hub_id')->nullable()->constrained('hubs')->onDelete('set null');
            $table->string('employee_id', 50)->unique();
            $table->enum('vehicle_type', ['bike', 'scooter', 'bicycle', 'car', 'truck'])->default('bike');
            $table->string('vehicle_number', 50)->nullable();
            $table->string('vehicle_model', 100)->nullable();
            $table->string('license_number', 50)->nullable();
            $table->decimal('max_weight_capacity', 10, 2)->default(50.00);
            $table->decimal('max_size_capacity', 10, 2)->default(100.00);
            $table->enum('status', ['available', 'busy', 'offline'])->default('available');
            $table->integer('total_deliveries')->default(0);
            $table->integer('successful_deliveries')->default(0);
            $table->integer('failed_deliveries')->default(0);
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->decimal('earnings', 10, 2)->default(0.00);
            $table->boolean('is_verified')->default(false);
            $table->date('joined_date')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riders');
    }
};
