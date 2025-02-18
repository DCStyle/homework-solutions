<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('post_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('original_filename');
            $table->integer('file_size');
            $table->string('mime_type');
            $table->string('extension', 10);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('post_attachments');
    }
};
