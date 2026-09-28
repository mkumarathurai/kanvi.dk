<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_admin_access', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable();
        });
        Schema::create('admin_recovery_links', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('admin_access_id')->constrained('poll_admin_access');
            $table->string('token_hash', 64)->unique();
            $table->string('email', 254);
            $table->boolean('register_email');
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_recovery_links');
        Schema::table('poll_admin_access', fn (Blueprint $table) => $table->dropColumn('email_verified_at'));
    }
};
