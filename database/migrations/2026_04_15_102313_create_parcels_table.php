<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcels', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number', 50)->unique();
            $table->string('reference_number', 50)->unique()->nullable();

            // Sender Information
            $table->string('sender_name');
            $table->string('sender_phone', 20);
            $table->string('sender_email')->nullable();
            $table->text('sender_address');

            // Receiver Information
            $table->string('receiver_name');
            $table->string('receiver_phone', 20);
            $table->string('receiver_email')->nullable();
            $table->text('receiver_address');

            // Parcel Details
            $table->string('parcel_name');
            $table->text('parcel_description')->nullable();
            $table->decimal('weight', 10, 2);
            $table->decimal('size', 10, 2);
            $table->enum('parcel_type', ['document', 'package', 'fragile', 'liquid', 'electronics'])->default('package');

            // Delivery Charges
            $table->decimal('delivery_charge', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'online'])->default('cash');
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');

            // Status Management
            $table->foreignId('status_id')->default(1)->constrained('parcel_statuses');

            // Hub Assignment
            $table->foreignId('source_hub_id')->constrained('hubs');

            // Rider Assignment
            $table->foreignId('assigned_rider_id')->nullable()->constrained('riders')->onDelete('set null');

            // Timestamps for tracking
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('out_for_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_delivery_at')->nullable();
            $table->timestamp('returned_at')->nullable();

            // Delivery Attempts
            $table->integer('delivery_attempts')->default(0);
            $table->text('failure_reason')->nullable();

            // Additional Info
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();

            // Indexes for performance
            $table->index('tracking_number');
            $table->index('status_id');
            $table->index('assigned_rider_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcels');
    }
};
