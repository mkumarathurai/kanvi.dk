<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polls', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('public_id', 24)->unique();
            $table->string('title', 140);
            $table->enum('type', ['date'])->default('date');
            $table->enum('status', ['open', 'finalized', 'closed', 'archived'])->default('open');
            $table->string('timezone')->default('Europe/Copenhagen');
            $table->string('locale', 10)->default('da');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('poll_options', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('poll_id')->constrained()->cascadeOnDelete();
            $table->enum('kind', ['date'])->default('date');
            $table->date('date_value');
            $table->unsignedInteger('sort_order');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['poll_id', 'deleted_at']);
        });

        Schema::create('poll_admin_access', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('poll_id')->constrained()->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->char('token_hash', 64)->unique();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_admin_access');
        Schema::dropIfExists('poll_options');
        Schema::dropIfExists('polls');
    }
};
