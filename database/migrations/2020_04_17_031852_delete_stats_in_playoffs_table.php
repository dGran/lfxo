<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteStatsInPlayoffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('playoffs', function (Blueprint $table) {
            $table->dropColumn('stats_mvp');
            $table->dropColumn('stats_goals');
            $table->dropColumn('stats_assists');
            $table->dropColumn('stats_yellow_cards');
            $table->dropColumn('stats_red_cards');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('playoffs', function (Blueprint $table) {
            $table->boolean("stats_mvp")->default(false);
            $table->boolean("stats_goals")->default(false);
            $table->boolean("stats_assists")->default(false);
            $table->boolean("stats_yellow_cards")->default(false);
            $table->boolean("stats_red_cards")->default(false);
        });
    }
}
