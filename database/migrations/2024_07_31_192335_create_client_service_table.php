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
        Schema::create('client_service', function (Blueprint $table) {
            $table->bigIncrements('id'); 
            $table->foreignId('client_id')
                ->references('id')->on('clients')
                ->onDelete('cascade');  
            $table->foreignId('service_id')
                ->references('id')->on('services')
                ->onDelete('cascade');
            $table->unique(['client_id', 'service_id']);
            $table->dateTime('enrollement_date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_service');
    }
};
