<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_options', function (Blueprint $table) {
            $table->unique(['poll_id', 'id']);
        });
        Schema::create('participants', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('poll_id')->constrained()->cascadeOnDelete();
            $table->string('display_name', 80);
            $table->char('edit_token_hash', 64);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['poll_id', 'edit_token_hash']);
            $table->unique(['poll_id', 'id']);
        });
        Schema::create('responses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('poll_id');
            $table->ulid('participant_id');
            $table->ulid('poll_option_id');
            $table->enum('value', ['can', 'maybe', 'cannot']);
            $table->timestamps();
            $table->unique(['participant_id', 'poll_option_id']);
            $table->foreign(['poll_id', 'participant_id'])->references(['poll_id', 'id'])->on('participants')->cascadeOnDelete();
            $table->foreign(['poll_id', 'poll_option_id'])->references(['poll_id', 'id'])->on('poll_options')->cascadeOnDelete();
        });
        Schema::create('response_revisions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('participant_id')->constrained()->cascadeOnDelete();
            $table->char('editor_id', 32);
            $table->string('field_key', 26);
            $table->unsignedBigInteger('revision');
            $table->char('payload_hash', 64);
            $table->timestamps();
            $table->unique(['participant_id', 'editor_id', 'field_key'], 'response_revision_stream_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('response_revisions');
        Schema::dropIfExists('responses');
        Schema::dropIfExists('participants');
        Schema::table('poll_options', fn (Blueprint $table) => $table->dropUnique(['poll_id', 'id']));
    }
};
