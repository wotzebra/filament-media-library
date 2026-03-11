<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wotz\MediaLibrary\Models\Attachment;
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
        Schema::create('attachment_attachment_tag', function (Blueprint $table) {
            $table->foreignIdFor(Attachment::class)
                ->constrained('attachments')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignIdFor(AttachmentTag::class)
                ->constrained('attachment_tags')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attachment_attachment_tag');
    }
};
