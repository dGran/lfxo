<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SeasonCompetitionMatch extends Model
{
	public $timestamps = false;
	protected $table = 'season_competitions_matches';

    protected $fillable = ['day_id', 'clash_id', 'local_id', 'local_user_id', 'local_score', 'visitor_id', 'visitor_user_id', 'visitor_score', 'santioned_id', 'date_limit', 'active'];

    public function day()
    {
        return $this->hasOne('App\SeasonCompetitionPhaseGroupLeagueDay', 'id', 'day_id');
    }

    public function clash()
    {
        return $this->hasOne('App\PlayOffRoundClash', 'id', 'clash_id');
    }

    public function cash_histories()
    {
        return $this->hasMany('App\SeasonParticipantCashHistory', 'match_id', 'id');
    }

    public function competition() {
        $match = SeasonCompetitionMatch::find($this->id);
        if ($match) {
            if ($this->day_id) {
                return $this->day->league->group->phase->competition;
            } else {
                return $this->clash->round->playoff->group->phase->competition;
            }
        }
    }

    public function group() {
        if ($this->day_id) {
            return $this->day->league->group;
        } else {
            return $this->clash->round->playoff->group;
        }
    }

    public function date_limit_match()
    {
        if (!$this->date_limit) {
            if ($this->day_id) {
                return $this->day->date_limit;
            } else {
                if ($this->clash->date_limit) {
                    return $this->clash->date_limit;
                } else {
                    return $this->clash->round->date_limit;
                }
            }
        } else {
            return $this->date_limit;
        }
    }

    public function clash_prev_match()
    {
        return $prev_match = SeasonCompetitionMatch::where('clash_id', '=', $this->clash_id)->where('order', '=', 1)->first();
    }

    public function stats()
    {
        return $this->hasMany('App\SeasonCompetitionStat', 'match_id', 'id');
    }

    public function local_participant()
    {
        return $this->hasOne('App\SeasonCompetitionPhaseGroupParticipant', 'id', 'local_id');
    }

    public function visitor_participant()
    {
        return $this->hasOne('App\SeasonCompetitionPhaseGroupParticipant', 'id', 'visitor_id');
    }

    public function sanctioned_participant()
    {
        return $this->hasOne('App\SeasonCompetitionPhaseGroupParticipant', 'id', 'sanctioned_id');
    }

    public function getDateLimit_date()
    {
        return $date = \Carbon\Carbon::parse($this->date_limit)->format('Y-m-d');
    }

    public function getDateLimit_time()
    {
        return $date = \Carbon\Carbon::parse($this->date_limit)->format('H:i');
    }

    public function winner()
    {
        if (!is_null($this->local_score) && !is_null($this->visitor_score)) {
            if ($this->local_score > $this->visitor_score) {
                return $this->local_participant->participant->id;
            } elseif ($this->visitor_score > $this->local_score) {
                return $this->visitor_participant->participant->id;
            } else {
                return null;
            }
        } else {
            return -1;
        }
    }

    public function player_match_stats($player_id) {
        return $stats = SeasonCompetitionStat::where('match_id', '=', $this->id)->where('player_id', '=', $player_id)->first();
    }

    public function local_own_goals() {
        $goals = SeasonCompetitionStat::where('match_id', '=', $this->id)->where('goals', '>', 0)->get();
        $local_goals = 0;

        foreach ($goals as $goal) {
            if ($goal->player->participant->id == $this->local_participant->participant->id) {
                $local_goals += $goal->goals;
            }
        }
        $own_goals = $this->local_score - $local_goals;
        if ($own_goals == 0) {
            return null;
        }
        return $own_goals;
    }

    public function local_own_assists() {
        $assists = SeasonCompetitionStat::where('match_id', '=', $this->id)->where('assists', '>', 0)->get();
        $local_assists = 0;

        foreach ($assists as $assist) {
            if ($assist->player->participant->id == $this->local_participant->participant->id) {
                $local_assists += $assist->assists;
            }
        }
        $own_assists = $this->local_score - $local_assists;
        if ($own_assists == 0) {
            return null;
        }
        return $own_assists;
    }

    public function visitor_own_goals() {
        $goals = SeasonCompetitionStat::where('match_id', '=', $this->id)->where('goals', '>', 0)->get();
        $visitor_goals = 0;

        foreach ($goals as $goal) {
            if ($goal->player->participant->id == $this->visitor_participant->participant->id) {
                $visitor_goals += $goal->goals;
            }
        }
        $own_goals = $this->visitor_score - $visitor_goals;
        if ($own_goals == 0) {
            return null;
        }
        return $own_goals;
    }

    public function visitor_own_assists() {
        $assists = SeasonCompetitionStat::where('match_id', '=', $this->id)->where('assists', '>', 0)->get();
        $visitor_assists = 0;

        foreach ($assists as $assist) {
            if ($assist->player->participant->id == $this->visitor_participant->participant->id) {
                $visitor_assists += $assist->assists;
            }
        }
        $own_assists = $this->visitor_score - $visitor_assists;
        if ($own_assists == 0) {
            return null;
        }
        return $own_assists;
    }

    public function match_name()
    {
        if ($this->day) {
            $competition = $this->day->league->group->phase->competition;
            $phase = $this->day->league->group->phase;
            $group = $this->day->league->group;

            $match_name = $competition->name;
            if ($competition->phases->count() > 1) {
                // $match_name .= ' - ' . $phase->name;
            }
            if ($phase->groups->count() > 1) {
                $match_name .= ' - ' . $group->name;
            }
            return $match_name . ' - Jornada ' . $this->day->order;

        } else { //playoffs
            $competition = $this->clash->round->playoff->group->phase->competition;
            $phase = $this->clash->round->playoff->group->phase;
            $group = $this->clash->round->playoff->group;
            $playoff = $this->clash->round->playoff;
            $round = $this->clash->round;

            $match_name = $competition->name;
            if ($competition->phases->count() > 1) {
                // $match_name .= ' - ' . $phase->name;
            }
            if ($phase->groups->count() > 1) {
                $match_name .= ' - ' . $group->name;
            }
            if ($playoff->rounds->count() > 1) {
                $match_name .= ' - ' . $round->name;
            }
            if ($round->round_trip) {
                if ($this->order == 1) {
                    $match_name .= " - Partido de ida";
                } else {
                    $match_name .= " - Partido de vuelta";
                }
            }

            return $match_name;
        }
    }

    public function match_name_array()
    {
        $array = [];
        if ($this->day) {
            $competition = $this->day->league->group->phase->competition;
            $phase = $this->day->league->group->phase;
            $group = $this->day->league->group;

            $array['competition'] = $competition->name;
            if ($competition->phases->count() > 1) {
                $array['phase'] = $phase->name;
            }
            if ($phase->groups->count() > 1) {
                $array['group'] = $group->name;
            }
            $array['day'] = $this->day->order;
        } else { //playoffs
            $competition = $this->clash->round->playoff->group->phase->competition;
            $phase = $this->clash->round->playoff->group->phase;
            $group = $this->clash->round->playoff->group;
            $playoff = $this->clash->round->playoff;
            $round = $this->clash->round;

            $array['competition'] = $competition->name;
            if ($competition->phases->count() > 1) {
                $array['phase'] = $phase->name;
            }
            if ($phase->groups->count() > 1) {
                $array['group'] = $group->name;
            }
            if ($playoff->rounds->count() > 1) {
                $array['round'] = $round->name;
            }
            if ($round->round_trip) {
                if ($this->order == 1) {
                    $array['match'] = "Ida";
                } else {
                    $array['match'] = "Vuelta";
                }
            }
        }
        return $array;
    }

    public function match_result()
    {
        return $this->local_participant->participant->name() . ' ' . $this->local_score . '-' . $this->visitor_score . ' ' . $this->visitor_participant->participant->name();
    }
}
