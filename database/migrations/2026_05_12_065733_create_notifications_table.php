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
    Schema::create('notifications', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('members', 'userId')->onDelete('cascade');
        $table->string('title'); // Simpler: "Problem Accepted"
        $table->string('message'); // Simpler: "Your Binary Search problem is live!"
        $table->string('type'); // Simpler: "success", "error", or "info"
        $table->string('link')->nullable(); // To redirect user when they click it
        $table->timestamps(); // Keeps track of when it was sent
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
