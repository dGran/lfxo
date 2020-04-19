<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SeasonCompetitionStat extends Model
{
	public $timestamps = false;
	protected $table = 'season_competitions_stats';

    public function competition()
    {
        return $this->hasOne('App\SeasonCompetition', 'id', 'competition_id');
    }

    public function match()
    {
        return $this->hasOne('App\SeasonCompetitionMatch', 'id', 'match_id');
    }

    public function player()
    {
        return $this->hasOne('App\SeasonPlayer', 'id', 'player_id');
    }

    public function is_player_local()
    {
        if ($this->match->local_participant->participant->id == $this->player->participant_id) {
            return true;
        } else {
            return false;
        }
    }

    public function is_player_visitor()
    {
        if ($this->match->visitor_participant->participant->id == $this->player->participant_id) {
            return true;
        } else {
            return false;
        }
    }

    public function stat_detail($stat, $competition_id, $player_id)
    {
        return SeasonCompetitionStat::where('competition_id', '=', $competition_id)
            ->where('player_id', '=', $player_id)
            ->where($stat, '>' , 0)
            ->orderBy('match_id', 'asc')
            ->get();
    }
}
