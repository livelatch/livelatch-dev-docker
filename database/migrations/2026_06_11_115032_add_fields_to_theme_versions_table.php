<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_versions', function (Blueprint $table) {
            $table->unsignedBigInteger('theme_id')->after('id');
            $table->string('version')->default('1.0.0')->after('theme_id');
            $table->string('status')->default('published')->after('version');
            $table->string('s3_asset_prefix')->nullable()->after('status');
            $table->json('manifest')->nullable()->after('s3_asset_prefix');

            $table->foreign('theme_id')
                ->references('id')
                ->on('themes')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('theme_versions', function (Blueprint $table) {
            $table->dropForeign(['theme_id']);

            $table->dropColumn([
                'theme_id',
                'version',
                'status',
                's3_asset_prefix',
                'manifest',
            ]);
        });
    }
};