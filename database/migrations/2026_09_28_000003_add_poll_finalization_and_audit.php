<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->ulid('final_option_id')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->unsignedInteger('management_version')->default(0);
            $table->foreign(['id', 'final_option_id'], 'polls_final_option_same_poll')
                ->references(['poll_id', 'id'])->on('poll_options');
        });
        Schema::table('poll_admin_access', fn (Blueprint $table) => $table->unique(['poll_id', 'id']));
        Schema::create('poll_audit_entries', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('poll_id');
            $table->ulid('admin_access_id');
            $table->string('action', 40);
            $table->json('details');
            $table->timestamps();
            $table->foreign(['poll_id', 'admin_access_id'])->references(['poll_id', 'id'])->on('poll_admin_access')->cascadeOnDelete();
            $table->index(['poll_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_audit_entries');
        Schema::table('poll_admin_access', fn (Blueprint $table) => $table->dropUnique(['poll_id', 'id']));
        Schema::table('polls', function (Blueprint $table) {
            $table->dropForeign('polls_final_option_same_poll');
            $table->dropColumn(['final_option_id', 'finalized_at', 'management_version']);
        });
    }
};
