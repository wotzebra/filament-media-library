<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->unsignedSmallInteger('version')->default(1)->after('disk');
        });

        Schema::create('attachment_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('attachment_id')->constrained('attachments')->cascadeOnDelete();
            $table->unsignedSmallInteger('version_number');
            $table->string('name');
            $table->string('extension');
            $table->string('mime_type');
            $table->string('md5');
            $table->string('type');
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('disk');
            $table->json('format_data')->nullable();
            $table->unsignedBigInteger('replaced_by_user_id')->nullable();
            $table->timestamp('replaced_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachment_versions');

        Schema::table('attachments', function (Blueprint $table) {
            $table->dropColumn('version');
        });
    }
};
