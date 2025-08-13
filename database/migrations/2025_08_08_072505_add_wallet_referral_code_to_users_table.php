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
            $table->string('wallet_referral_code', 20)->nullable()->after('password');
            $table->string('wallet_referred_by', 20)->nullable()->after('wallet_referral_code'); // Code used during registration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wallet_referral_code');
            $table->dropColumn('wallet_referred_by');
        });
    }
};
