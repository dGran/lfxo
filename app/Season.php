<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
	public $timestamps = false;

    protected $fillable = [
        'name', 'slug', 'num_participants', 'participant_has_team', 'use_economy', 'use_rosters', 'players_db_id', 'min_players', 'max_players', 'initial_budget', 'salary_cap', 'free_players_salary', 'free_players_new_salary', 'free_players_cost', 'free_players_remuneration', 'max_clauses_paid', 'max_clauses_received', 'rules', 'salaries_paid'
    ];

	public function scopeName($query, $name)
	{
		if (trim($name) !="") {
			$query->where("name", "LIKE", "%$name%");
		}
	}

    public function participants()
    {
        return $this->hasmany('App\SeasonParticipant', 'season_id', 'id');
    }

    public function hasParticipants()
    {
    	if ($this->participants) {
    		return true;
    	} else {
    		return false;
    	}
    }

    public function players()
    {
        return $this->hasmany('App\SeasonPlayer', 'season_id', 'id');
    }
}
