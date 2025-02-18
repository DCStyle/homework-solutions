<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('post_attachments', function (Blueprint $table) {
            $table->foreignId('post_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('post_attachments', function (Blueprint $table) {
            $table->foreignId('post_id')->nullable(false)->change();
        });
    }
};
