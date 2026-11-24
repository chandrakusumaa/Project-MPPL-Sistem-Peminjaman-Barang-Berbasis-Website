<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('organizations') || Schema::hasColumn('organizations', 'payment_account')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->string('payment_account', 255)->nullable()->after('damage_charge_policy');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('organizations') || ! Schema::hasColumn('organizations', 'payment_account')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('payment_account');
        });
    }
};
