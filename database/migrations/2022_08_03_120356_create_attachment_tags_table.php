<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wotz\MediaLibrary\Models\AttachmentTag;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachment_tags', function (Blueprint $table) {
            $table->id();
            $table->string('title');

            $table->foreignIdFor(AttachmentTag::class, 'parent_attachment_tag_id')
                ->nullable()
                ->constrained('attachment_tags')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attachment_tags');
    }
};
