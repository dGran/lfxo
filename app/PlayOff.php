<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlayOff extends Model
{
	public $timestamps = false;
	protected $table = 'playoffs';

    protected $fillable = ['group_id', 'predefined_rounds', 'rounds'];

    public function group()
    {
        return $this->hasOne('App\SeasonCompetitionPhaseGroup', 'id', 'group_id');
    }

    public function rounds()
    {
        return $this->hasMany('App\PlayOffRound', 'playoff_id', 'id');
    }

    public function winner()
    {
        $last_round = PlayOffRound::where('playoff_id', $this->id)->orderBy('id', 'desc')->first();
        if ($last_round->clashes->count() > 0) {
            $clash = $last_round->clashes->first();
            if ($clash->winner()) {
                return $clash->winner();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function subchampion()
    {
        $last_round = PlayOffRound::where('playoff_id', $this->id)->orderBy('id', 'desc')->first();
        if ($last_round->clashes->count() > 0) {
            $clash = $last_round->clashes->first();
            if ($clash->winner()) {
                return $clash->loser();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
