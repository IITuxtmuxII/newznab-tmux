<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateForumTableThreads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('forum_threads')) {
            Schema::create('forum_threads', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_category')->unsigned();
                $table->foreignIdFor(config('forum.integration.user_model'), 'author_id');
                $table->string('title');
                $table->boolean('pinned');
                $table->boolean('locked');

                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('forum_threads');
    }
}
