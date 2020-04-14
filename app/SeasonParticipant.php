<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SeasonParticipant extends Model
{
	public $timestamps = false;

    protected $fillable = [
        'season_id', 'team_id', 'user_id', 'paid_clauses', 'clauses_received'
    ];

    public function season()
    {
        return $this->hasOne('App\Season', 'id', 'season_id');
    }

    public function team()
    {
        return $this->hasOne('App\Team', 'id', 'team_id');
    }

    public function players()
    {
        return $this->hasmany('App\SeasonPlayer', 'participant_id', 'id');
    }

    public function favorites()
    {
        return $this->hasmany('App\FavoritePlayer', 'participant_id', 'id');
    }

    public function trades_received()
    {
        return $this->hasmany('App\Trade', 'participant2_id', 'id');
    }

    public function trades_sent()
    {
        return $this->hasmany('App\Trade', 'participant1_id', 'id');
    }

    public function trades_received_pending()
    {
        return $trades = Trade::where('participant2_id', '=', $this->id)->where('state', '=', 'pending')->count();
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function cash_history()
    {
        return $this->hasmany('App\SeasonParticipantCashHistory', 'participant_id', 'id');
    }

    public function scopeSeasonId($query, $seasonID)
    {
        if (trim($seasonID) !="") {
            $query->where("season_id", "=", $seasonID);
        }
    }

    public function budget() {
        $budget = 0;
        foreach ($this->cash_history as $cash_history) {
            if ($cash_history->movement == 'E') {
                $budget = $budget + $cash_history->amount;
            } elseif ($cash_history->movement == 'S') {
                $budget = $budget - $cash_history->amount;
            }
        }
        return $budget;
    }

    public function budget_formatted() {
        return $this->budget() . " M";
    }

    public function salaries() {
        $salaries = 0;
        foreach ($this->players as $player) {
            $salaries = $salaries + $player->salary;
        }
        return $salaries;
    }

    public function salaries_formatted() {
        return $this->salaries() . " M";
    }

    public function salaries_avg() {
        if ($this->players->count() > 0) {
            return $this->salaries() / $this->players->count();
        }
        return '0';
    }

    public function logo() {
        if ($this->season->participant_has_team) {
            if ($this->team_id) {
                return $this->team->getLogoFormatted();
            } else {
                return asset('img/team_no_image.png');
            }
        } else {
            if ($this->user_id) {
                return $this->user->avatar();
            } else {
                return asset('img/user_unknown.png');
            }
        }
    }

    public function name() {
        if ($this->season->participant_has_team) {
            if ($this->team_id) {
                return $this->team->name;
            } else {
                return "undefined";
            }
        } else {
            if ($this->user_id) {
                return $this->user->name;
            } else {
                return "undefined";
            }
        }
    }

    public function sub_name() {
        if ($this->season->participant_has_team) {
            if ($this->user_id) {
                return $this->user->name;
            } else {
                return "undefined";
            }
        } else {
            return "";
        }
    }

    public function slug() {
        if ($this->season->participant_has_team) {
            if ($this->team_id) {
                return $this->team->slug;
            } else {
                return "undefined";
            }
        } else {
            if ($this->user_id) {
                return $this->user->name;
            } else {
                return "undefined";
            }
        }
    }

    public function team_avg_overall() {
        return SeasonPlayer::
            leftJoin('players', 'players.id', '=', 'season_players.player_id')
            ->select('players.overall_rating')
            ->seasonId($this->season->id)
            ->where('participant_id', '=', $this->id)
            ->avg('players.overall_rating');
    }

    public function team_avg_age() {
        return SeasonPlayer::
            leftJoin('players', 'players.id', '=', 'season_players.player_id')
            ->select('players.age')
            ->seasonId($this->season->id)
            ->where('participant_id', '=', $this->id)
            ->avg('players.age');
    }

    public function top_players() {
        return SeasonPlayer::
            leftJoin('players', 'players.id', '=', 'season_players.player_id')
            ->select('season_players.*')
            ->seasonId($this->season->id)
            ->where('participant_id', '=', $this->id)
            ->orderBy('players.overall_rating', 'desc')
            ->take(3)->get();
    }

    public function top_defs() {
        return SeasonPlayer::
            leftJoin('players', 'players.id', '=', 'season_players.player_id')
            ->select('season_players.*')
            ->seasonId($this->season->id)
            ->where('participant_id', '=', $this->id)
            ->where(function($q) {
                $q->where('players.position', '=', 'DFC')
                  ->orWhere('players.position', '=', 'LI')
                  ->orWhere('players.position', '=', 'LD')
                  ->orWhere('players.position', '=', 'CAD')
                  ->orWhere('players.position', '=', 'CAI');
            })
            ->orderBy('players.overall_rating', 'desc')
            ->take(3)->get();
    }

    public function top_mids() {
        return SeasonPlayer::
            leftJoin('players', 'players.id', '=', 'season_players.player_id')
            ->select('season_players.*')
            ->seasonId($this->season->id)
            ->where('participant_id', '=', $this->id)
            ->where(function($q) {
                $q->where('players.position', '=', 'MCD')
                  ->orWhere('players.position', '=', 'MC')
                  ->orWhere('players.position', '=', 'MCO')
                  ->orWhere('players.position', '=', 'MI')
                  ->orWhere('players.position', '=', 'MD');
            })
            ->orderBy('players.overall_rating', 'desc')
            ->take(3)->get();
    }

    public function top_forws() {
        return SeasonPlayer::
            leftJoin('players', 'players.id', '=', 'season_players.player_id')
            ->select('season_players.*')
            ->seasonId($this->season->id)
            ->where('participant_id', '=', $this->id)
            ->where(function($q) {
                $q->where('players.position', '=', 'DC')
                  ->orWhere('players.position', '=', 'SD')
                  ->orWhere('players.position', '=', 'EI')
                  ->orWhere('players.position', '=', 'ED');
            })
            ->orderBy('players.overall_rating', 'desc')
            ->take(3)->get();
    }

    public function young_players() {
        return SeasonPlayer::
            leftJoin('players', 'players.id', '=', 'season_players.player_id')
            ->select('season_players.*')
            ->seasonId($this->season->id)
            ->where('participant_id', '=', $this->id)
            ->orderBy('players.age', 'asc')
            ->take(3)->get();
    }

    public function veteran_players() {
        return SeasonPlayer::
            leftJoin('players', 'players.id', '=', 'season_players.player_id')
            ->select('season_players.*')
            ->seasonId($this->season->id)
            ->where('participant_id', '=', $this->id)
            ->orderBy('players.age', 'desc')
            ->take(3)->get();
    }

    public function last_results() {

        $matches = SeasonCompetitionMatch::
            leftJoin('playoffs_rounds_clashes', 'playoffs_rounds_clashes.id', '=', 'season_competitions_matches.clash_id')
            ->leftJoin('season_competitions_phases_groups_leagues_days', 'season_competitions_phases_groups_leagues_days.id', '=', 'season_competitions_matches.day_id')
            ->leftJoin('season_competitions_phases_groups_participants as local_group_participant', 'local_group_participant.id', '=', 'season_competitions_matches.local_id')
            ->leftJoin('season_competitions_phases_groups_participants as visitor_group_participant', 'visitor_group_participant.id', '=', 'season_competitions_matches.visitor_id')
            ->leftJoin('season_participants as local_participant', 'local_participant.id', '=', 'local_group_participant.participant_id')
            ->leftJoin('season_participants as visitor_participant', 'visitor_participant.id', '=', 'visitor_group_participant.participant_id')
            ->select('season_competitions_matches.*')
            ->where(function($q) {
                $q->whereNotNull('season_competitions_matches.local_score')
                  ->whereNotNull('season_competitions_matches.visitor_score');
            })
            ->where(function($q) {
                $q->where('local_participant.id', '=', $this->id)
                  ->OrWhere('visitor_participant.id', '=', $this->id);
            })
            ->orderBy('season_competitions_matches.date_update_result', 'desc')
            ->take(5)->get();

        return $matches;
    }

    public function pending_matches()
    {
        $matches = SeasonCompetitionMatch::
            leftJoin('playoffs_rounds_clashes', 'playoffs_rounds_clashes.id', '=', 'season_competitions_matches.clash_id')
            ->leftJoin('season_competitions_phases_groups_leagues_days', 'season_competitions_phases_groups_leagues_days.id', '=', 'season_competitions_matches.day_id')
            ->leftJoin('season_competitions_phases_groups_participants as local_group_participant', 'local_group_participant.id', '=', 'season_competitions_matches.local_id')
            ->leftJoin('season_competitions_phases_groups_participants as visitor_group_participant', 'visitor_group_participant.id', '=', 'season_competitions_matches.visitor_id')
            ->leftJoin('season_participants as local_participant', 'local_participant.id', '=', 'local_group_participant.participant_id')
            ->leftJoin('season_participants as visitor_participant', 'visitor_participant.id', '=', 'visitor_group_participant.participant_id')
            ->select('season_competitions_matches.*')
            ->where('season_competitions_matches.active', '=', 1)
            ->where(function($q) {
                $q->whereNull('season_competitions_matches.local_score')
                  ->whereNull('season_competitions_matches.visitor_score');
            })
            ->where(function($q) {
                $q->where('local_participant.id', '=', $this->id)
                  ->OrWhere('visitor_participant.id', '=', $this->id);
            })
            ->get();

        return $matches->count();
    }

    public function clauses_received_limit() {
        if ($this->clauses_received < $this->season->max_clauses_received) {
            return false;
        }
        return true;
    }

    public function clauses_paid_limit() {
        if ($this->paid_clauses < $this->season->max_clauses_paid) {
            return false;
        }
        return true;
    }

    public function max_players_limit() {
        if ($this->players->count() < $this->season->max_players) {
            return false;
        }
        return true;
    }

    public function min_players_limit() {
        if ($this->players->count() > $this->season->min_players) {
            return false;
        }
        return true;
    }

}
