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
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('password');
            $table->string('stripe_account_id')->nullable()->after('stripe_customer_id');
            $table->string('wallet_referral_code', 20)->nullable()->after('stripe_account_id');
            $table->string('wallet_referred_by', 20)->nullable()->after('wallet_referral_code'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'wallet_referred_by',
                'wallet_referral_code',
                'stripe_account_id',
                'stripe_customer_id',
            ]);
        });
    }
};
