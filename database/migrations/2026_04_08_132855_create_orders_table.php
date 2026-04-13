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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('customer_name');
            $table->string('customer_phone');

            $table->decimal('drop_address', 10 , 7);
            $table->decimal('drop_latitude' , 10 , 7);

            $table->decimal('total_amount', 10 , 2);

            $table->enum('status' , [
                'pending' ,
                'assigned' ,
                'picked_up' ,
                'delivered' ,
                'failed_delivered' ,
                'returned'
            ])->default('pending');

            $table->foreignId('rider_id')->nullable()->constrained()->nullableOnDelete();

            $table->enum('return_type' , ['hub' ,'owner'])->nullable();
            $table->foreignId('hub_id')->nullable()->constrained()->nullOnDelelte();
            $table->text('return_address')->nullable();

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('returned_at')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
