<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropCompetitionsStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('competitions_stats');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('competitions_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("competition_id")->unsigned();
            $table->integer("match_id")->unsigned();
            $table->integer("player_id")->unsigned();
            $table->integer("mvp")->nullable();
            $table->integer("goals")->nullable();
            $table->integer("assists")->nullable();
            $table->integer("red_cards")->nullable();
            $table->integer("yellow_cards")->nullable();
        });
    }
}
