<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SeasonCompetitionPhaseGroupLeague extends Model
{
	public $timestamps = false;
	protected $table = 'season_competitions_phases_groups_leagues';

    protected $fillable = ['group_id', 'allow_draws', 'win_points', 'draw_points', 'lose_points', 'play_amount', 'play_ontime_amount', 'win_amount', 'draw_amount', 'lose_amount'];

    public function days()
    {
        return $this->hasMany('App\SeasonCompetitionPhaseGroupLeagueDay', 'league_id', 'id');
    }

    public function table_zones()
    {
        return $this->hasMany('App\SeasonCompetitionPhaseGroupLeagueTableZone', 'league_id', 'id');
    }

    public function group()
    {
        return $this->hasOne('App\SeasonCompetitionPhaseGroup', 'id', 'group_id');
    }

    public function generate_table()
    {
        $group_participants = SeasonCompetitionPhaseGroupParticipant::where('group_id', '=', $this->group->id)->get();
        $table_participants = collect();

        foreach ($group_participants as $key => $participant) {
            $data = $this->get_table_data_participant($participant->id);
            $table_participants->push([
                'participant' => $participant,
                'pj' => $data['pj'],
                'pg' => $data['pg'],
                'pe' => $data['pe'],
                'pp' => $data['pp'],
                'ps' => $data['ps'],
                'gf' => $data['gf'],
                'gc' => $data['gc'],
                'avg' => $data['avg'],
                'avg_p' => 0,
                'pts' => $data['pts'],
            ]);
        }
        $table_participants = $table_participants->sortByDesc('gf')->sortByDesc('avg')->sortByDesc('pts')->values();

        foreach ($table_participants as $key => $tp) {
            $filtered = $table_participants->filter(function ($value, $key) use ($tp) {
                if ($value['pts'] == $tp['pts']) {
                    return $value;
                }
            })->values();

            if (count($filtered) == 2) {
                if ($filtered[0]['participant'] == $tp['participant']) {
                    $p1 = $filtered[0]['participant'];
                    $p2 = $filtered[1]['participant'];
                } else {
                    $p1 = $filtered[1]['participant'];
                    $p2 = $filtered[0]['participant'];
                }
                $matches = SeasonCompetitionPhaseGroupLeagueDay::select('season_competitions_phases_groups_leagues_days.*', 'season_competitions_matches.*')
                    ->join('season_competitions_matches', 'season_competitions_matches.day_id', '=', 'season_competitions_phases_groups_leagues_days.id')
                    ->where(function ($query) use ($p1, $p2) {
                        $query->where('season_competitions_matches.local_id', '=', $p1->id)
                              ->Where('season_competitions_matches.visitor_id', '=', $p2->id);
                    })
                    ->orWhere(function ($query) use ($p1, $p2) {
                        $query->where('season_competitions_matches.local_id', '=', $p2->id)
                              ->Where('season_competitions_matches.visitor_id', '=', $p1->id);
                    })
                    ->get();

                $p1_goals = 0;
                $p2_goals = 0;
                foreach ($matches as $match) {
                    if ($match->local_id == $p1->id) {
                        $p1_goals += $match->local_score;
                        $p2_goals += $match->visitor_score;
                    } else {
                        $p1_goals += $match->visitor_score;
                        $p2_goals += $match->local_score;
                    }
                }

                if ($p1_goals > $p2_goals) {
                    $tp_aux = $table_participants->toArray();
                    $tp_aux[$key]['avg_p'] = 1;
                    $table_participants = collect($tp_aux);
                }
            }
        }

        $table_participants = $table_participants->sortByDesc('gf')->sortByDesc('avg')->sortByDesc('avg_p')->sortByDesc('pts')->values();

        $table_participants2 = collect();
        $zones = [];
        foreach ($this->table_zones as $key => $table_zone) {
            $zones[$key] = SeasonCompetitionPhaseGroupLeagueTableZone::where('league_id', '=', $this->id)->where('position', '=', $key+1)->first()->table_zone;
        }

        foreach ($table_participants as $key => $tp) {
            $table_participants2->push([
                'participant' => $table_participants[$key]['participant'],
                'pj' => $table_participants[$key]['pj'],
                'pg' => $table_participants[$key]['pg'],
                'pe' => $table_participants[$key]['pe'],
                'pp' => $table_participants[$key]['pp'],
                'ps' => $table_participants[$key]['ps'],
                'gf' => $table_participants[$key]['gf'],
                'gc' => $table_participants[$key]['gc'],
                'avg' => $table_participants[$key]['avg'],
                'pts' => $table_participants[$key]['pts'],
                'table_zone' => $zones[$key],
            ]);
        }
        $table_participants = $table_participants2;

        return $table_participants;
    }

    protected function get_table_data_participant($participant_id)
    {
        $matches = SeasonCompetitionPhaseGroupLeagueDay::select('season_competitions_phases_groups_leagues_days.*', 'season_competitions_matches.*')
            ->join('season_competitions_matches', 'season_competitions_matches.day_id', '=', 'season_competitions_phases_groups_leagues_days.id')
            ->where('season_competitions_matches.local_id', '=', $participant_id)
            ->orwhere('season_competitions_matches.visitor_id', '=', $participant_id)
            ->get();

        $data = [
            "pj" => 0,
            "pg" => 0,
            "pe" => 0,
            "pp" => 0,
            "ps" => 0,
            "gf" => 0,
            "gc" => 0,
            "avg" => 0,
            "pts" => 0
        ];

        foreach ($matches as $match) {
            if (!is_null($match->local_score) && !is_null($match->visitor_score))
            {
                $data['pj'] = $data['pj'] + 1;

                if ($participant_id == $match->local_id) { //local
                    if ($match->local_score > $match->visitor_score) {
                        $data['pg'] = $data['pg'] + 1;
                        $data['pts'] = $data['pts'] + intval($this->win_points);
                    } elseif ($match->local_score == $match->visitor_score) {
                        $data['pe'] = $data['pe'] + 1;
                        $data['pts'] = $data['pts'] + intval($this->draw_points);
                    } else {
                        $data['pp'] = $data['pp'] + 1;
                        $data['pts'] = $data['pts'] + intval($this->lose_points);
                    }
                    $data['gf'] = $data['gf'] + $match->local_score;
                    $data['gc'] = $data['gc'] + $match->visitor_score;

                } else { //visitor
                    if ($match->visitor_score > $match->local_score) {
                        $data['pg'] = $data['pg'] + 1;
                        $data['pts'] = $data['pts'] + intval($this->win_points);
                    } elseif ($match->local_score == $match->visitor_score) {
                        $data['pe'] = $data['pe'] + 1;
                        $data['pts'] = $data['pts'] + intval($this->draw_points);
                    } else {
                        $data['pp'] = $data['pp'] + 1;
                        $data['pts'] = $data['pts'] + intval($this->lose_points);
                    }
                    $data['gf'] = $data['gf'] + $match->visitor_score;
                    $data['gc'] = $data['gc'] + $match->local_score;
                }

                if ($match->sanctioned_id && ($participant_id == $match->sanctioned_id )) {
                    $data['ps'] = $data['ps'] + 1;
                }
            }
        }
        $data['avg'] = $data['gf'] - $data['gc'];
        return $data;
    }

    public function table_participant_position($position)
    {
        $pos = $position -1;
        $tp = $this->generate_table();
        return $tp[$pos]['participant'];
    }

    public function total_matches()
    {
        $counter = 0;
        foreach ($this->days as $day) {
            $counter += $day->matches->count();
        }
        return $counter;
    }

    public function played_matches()
    {
        $counter = 0;
        foreach ($this->days as $day) {
            foreach ($day->matches as $match) {
                if (!is_null($match->local_score) && !is_null($match->visitor_score)) {
                    $counter++;
                }
            }
        }
        return $counter;
    }

    public function pending_matches()
    {
        $counter = 0;
        foreach ($this->days as $day) {
            foreach ($day->matches as $match) {
                if (is_null($match->local_score) && is_null($match->visitor_score)) {
                    $counter++;
                }
            }
        }
        return $counter;
    }

    public function has_winner()
    {
        if ($this->pending_matches() == 0) {
            if ($this->group->phase->is_last()) {
                return true;
            }
            return false;
        }
        return false;
    }

}
