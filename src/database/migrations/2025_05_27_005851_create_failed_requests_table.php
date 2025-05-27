<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('failed_requests', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint');
            $table->json('payload');
            $table->string('status')->default('pending');
            $table->integer('response_code')->nullable();
            $table->unsignedBigInteger('proposal_id')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamps();

            $table->foreign('proposal_id')->references('id')->on('proposals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_requests');
    }
};

