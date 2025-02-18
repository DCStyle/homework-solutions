<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('post_attachments', function (Blueprint $table) {
            $table->string('storage_path')->nullable()->after('extension');
        });
    }

    public function down()
    {
        Schema::table('post_attachments', function (Blueprint $table) {
            $table->dropColumn('storage_path');
        });
    }
};
