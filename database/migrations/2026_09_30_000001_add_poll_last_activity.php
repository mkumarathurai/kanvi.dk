<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nullable so the previous release and its queue workers keep working during a deploy.
        // A poll without this timestamp is never purged.
        Schema::table('polls', fn (Blueprint $table) => $table->timestamp('last_activity_at')->nullable()->index());
        DB::table('polls')->whereNull('last_activity_at')->update(['last_activity_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('polls', fn (Blueprint $table) => $table->dropColumn('last_activity_at'));
    }
};
