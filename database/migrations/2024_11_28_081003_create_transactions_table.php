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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('transaction_id');
            $table->enum('type', ['FUND', 'AIRTIME', 'WITHDRAWAL']);
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable();
            $table->enum('status', ['SUCCESS', 'FAILED'])->default('SUCCESS');
            $table->json('metadata')->nullable(); 
            $table->timestamps();
            //FOREIGN KEY
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
