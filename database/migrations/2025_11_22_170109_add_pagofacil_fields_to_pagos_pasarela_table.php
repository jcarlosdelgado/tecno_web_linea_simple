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
        Schema::table('pagos_pasarela', function (Blueprint $table) {
            // Pago Fácil specific fields
            $table->string('pf_transaction_id', 100)->nullable()->after('transaction_id');
            $table->string('payment_method_transaction_id', 100)->nullable()->after('pf_transaction_id');
            $table->integer('pf_status')->nullable()->after('estado');
            $table->dateTime('expiration_date')->nullable()->after('pf_status');
            $table->longText('qr_base64')->nullable()->after('expiration_date');
            $table->string('checkout_url', 500)->nullable()->after('qr_base64');
            $table->string('deep_link', 500)->nullable()->after('checkout_url');
            $table->string('qr_content_url', 500)->nullable()->after('deep_link');
            $table->string('universal_url', 500)->nullable()->after('qr_content_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos_pasarela', function (Blueprint $table) {
            $table->dropColumn([
                'pf_transaction_id',
                'payment_method_transaction_id',
                'pf_status',
                'expiration_date',
                'qr_base64',
                'checkout_url',
                'deep_link',
                'qr_content_url',
                'universal_url'
            ]);
        });
    }
};
