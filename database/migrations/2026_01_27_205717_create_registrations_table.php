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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');
            $table->unsignedTinyInteger('age');
            $table->enum('gender', ['Male', 'Female']);
            $table->string('email');

            $table->unsignedSmallInteger('travelers')->default(1);
            $table->string('destination');
            $table->unsignedSmallInteger('stay_duration'); // days

            $table->enum('travel_package', ['Economic', 'Comfortable', 'VIP']);

            $table->enum('status', ['pending', 'processing', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->index(['email', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
