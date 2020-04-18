<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMatchIdTransferIdTradeIdInSeasonParticipantCashHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('season_participant_cash_history', function (Blueprint $table) {
            $table->integer('match_id')->unsigned()->nullable();
            $table->integer('transfer_id')->unsigned()->nullable();
            $table->integer('trade_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('season_participant_cash_history', function (Blueprint $table) {
            $table->dropColumn('match_id');
            $table->dropColumn('transfer_id');
            $table->dropColumn('trade_id');
        });
    }
}
