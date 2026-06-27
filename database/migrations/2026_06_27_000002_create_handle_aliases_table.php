<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Old profile handles kept alive after an approved name change.
 *
 * When a creator's URL handle changes (users.littlelink_name), the previous
 * handle is recorded here pointing at the same user. `littlelink()` resolves a
 * miss against this table and 301-redirects to the creator's current canonical
 * handle, so old /@oldname links never break.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handle_aliases', function (Blueprint $table) {
            $table->id();
            $table->string('alias', 191)->unique(); // previous handle, lowercased
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handle_aliases');
    }
};
