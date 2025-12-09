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
        Schema::create('payment_links', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('reference');
            $table->string('reference_1');
            $table->enum('delivery_type', ['email', 'sms']);
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('email_subject')->nullable();
            $table->string('email_body')->nullable();
            $table->string('email_file_path')->nullable();
            $table->string('sms_body')->nullable();
            $table->string('status')->default('pending');
            $table->string('invoice_currency');
            $table->string('invoice_amount');
            $table->string('tax_type');
            $table->string('tax_amount');
            $table->string('total_amount');
            $table->string('invoice_valid_from');
            $table->string('terms_and_conditions')->nullable();
            $table->string('payment_link_url')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->softDeletes(); // This adds the deleted_at timestamp column
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_links');
    }
};
