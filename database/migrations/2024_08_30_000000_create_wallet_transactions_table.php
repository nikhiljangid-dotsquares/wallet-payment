<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdraw', 'send', 'receive']);
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->unsignedBigInteger('related_user_id')->nullable();
            $table->string('description')->nullable();
            $table->decimal('admin_commission', 15, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('related_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
}; 