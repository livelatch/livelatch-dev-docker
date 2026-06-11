<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->string('slug')->unique()->after('id');
            $table->string('name')->after('slug');
            $table->string('status')->default('published')->after('name');
            $table->string('visibility')->default('public')->after('status');
            $table->string('pricing_type')->default('free')->after('visibility');
            $table->unsignedBigInteger('current_version_id')->nullable()->after('pricing_type');
        });
    }

    public function down()
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'name',
                'status',
                'visibility',
                'pricing_type',
                'current_version_id',
            ]);
        });
    }
};