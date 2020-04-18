<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SeasonCompetition;
use App\SeasonCompetitionStat;
use App\SeasonCompetitionPhase;
use App\SeasonCompetitionPhaseGroup;
use App\SeasonCompetitionPhaseGroupLeague;
use App\SeasonCompetitionPhaseGroupLeagueDay;
use App\SeasonCompetitionPhaseGroupParticipant;
use App\SeasonCompetitionPhaseGroupLeagueTableZone;
use App\PlayOff;
use App\PlayOffRoundClash;
use App\PlayOffRoundParticipant;

use App\Season;
use App\SeasonCompetitionMatch;
use App\SeasonPlayer;
use App\SeasonParticipant;
use App\Post;
use App\SeasonParticipantCashHistory as Cash;

class CompetitionController extends Controller
{
    public function index($season_slug = null)
    {
    	if (is_null($season_slug)) {
    		$season = active_season();
    	} else {
    		$season = Season::where('slug', '=', $season_slug)->first();
    	}
    	$season_slug = $season->slug;
    	$seasons = Season::orderBy('name', 'asc')->get();

    	$competitions = SeasonCompetition::where('season_id', '=', $season->id)->orderBy('name', 'asc')->get();
        return view('competitions.index', compact('competitions', 'season', 'season_slug', 'seasons'));
    }

    public function table($season_slug, $competition_slug, $phase_slug = null, $group_slug = null)
    {
    	if (is_null($season_slug)) {
    		$season = active_season();
    	} else {
    		$season = Season::where('slug', '=', $season_slug)->first();
    	}

    	$competition = SeasonCompetition::where('slug', '=', $competition_slug)->firstOrFail();
    	$competitions = SeasonCompetition::where('season_id', '=', $season->id)->orderBy('name', 'asc')->get();

		if ($competition->isConfigured()) {
			if (!$phase_slug) {
				$phase = SeasonCompetitionPhase::where('competition_id', '=', $competition->id)
				->where('active', '=', 1)->orderBy('id', 'desc')->firstOrFail();
			} else {
				$phase = SeasonCompetitionPhase::where('slug', '=', $phase_slug)->first();
			}
			$game_mode = $this->check_game_mode($phase);
		} else {
			return back()->with('error', 'La competición está en fase de configuración');
		}

		if ($game_mode == 'league') { // league

			if ($phase->groups->count()>0) {
				if (!$group_slug) {
					$group = SeasonCompetitionPhaseGroup::where('phase_id', '=', $phase->id)->firstOrFail();
				} else {
					$group = SeasonCompetitionPhaseGroup::where('slug', '=', $group_slug)->first();
				}
				$league = $this->check_league($group);
			} else {
				return back()->with('error', 'La competición está en fase de configuración');
			}

			$group_participants = SeasonCompetitionPhaseGroupParticipant::where('group_id', '=', $league->group->id)->get();
			if ($group_participants->count() == 0) {
				return back()->with('error', 'la liga no esta configurada');
			}

			$table_participants = $league->generate_table();

	        return view('competitions.league.table', compact('group', 'league', 'table_participants', 'competitions', 'competition'));

		} else { // playoffs

			if ($phase->groups->count()>0) {
				if (!$group_slug) {
					$group = SeasonCompetitionPhaseGroup::where('phase_id', '=', $phase->id)->firstOrFail();
				} else {
					$group = SeasonCompetitionPhaseGroup::where('slug', '=', $group_slug)->first();
				}
				$playoff = $this->check_playoff($group);
			} else {
				return back()->with('error', 'La competición está en fase de configuración');
			}

	        return view('competitions.playoffs.table', compact('group', 'playoff', 'competitions', 'competition'));
	        // return redirect()->route('competitions.calendar', [$season_slug, $competition_slug, $phase_slug, $group_slug]);
		}
    }

    public function calendar($season_slug, $competition_slug, $phase_slug = null, $group_slug = null)
    {
    	if (is_null($season_slug)) {
    		$season = active_season();
    	} else {
    		$season = Season::where('slug', '=', $season_slug)->first();
    	}

    	$competition = SeasonCompetition::where('slug', '=', $competition_slug)->firstOrFail();
    	$competitions = SeasonCompetition::where('season_id', '=', $season->id)->orderBy('name', 'asc')->get();

		if ($competition->phases->count()>0) {
			if (!$phase_slug) {
				$phase = SeasonCompetitionPhase::where('competition_id', '=', $competition->id)
				->where('active', '=', 1)->orderBy('id', 'desc')->firstOrFail();
			} else {
				$phase = SeasonCompetitionPhase::where('slug', '=', $phase_slug)->first();
			}
			$game_mode = $this->check_game_mode($phase);
		} else {
			return back()->with('error', 'La competición está en fase de configuración');
		}

		if ($game_mode == 'league') { // league
			if ($phase->groups->count()>0) {
				if (!$group_slug) {
					$group = SeasonCompetitionPhaseGroup::where('phase_id', '=', $phase->id)->firstOrFail();
				} else {
					$group = SeasonCompetitionPhaseGroup::where('slug', '=', $group_slug)->first();
				}
				$league = $this->check_league($group);
			} else {
				return back()->with('error', 'La competición está en fase de configuración');
			}

	        // $league = $this->check_league($group);

	        return view('competitions.league.calendar', compact('group', 'league', 'competitions', 'competition', 'season'));
		} else { // playoffs
			if ($phase->groups->count()>0) {
				if (!$group_slug) {
					$group = SeasonCompetitionPhaseGroup::where('phase_id', '=', $phase->id)->firstOrFail();
				} else {
					$group = SeasonCompetitionPhaseGroup::where('slug', '=', $group_slug)->first();
				}
				$playoff = $this->check_playoff($group);
			} else {
				return back()->with('error', 'La competición está en fase de configuración');
			}

	        return view('competitions.playoffs.calendar', compact('group', 'playoff', 'competitions', 'competition', 'season'));
		}
    }

    public function match($match_id)
    {
    	$match = SeasonCompetitionMatch::find($match_id);
    	if ($match) {
    		$season = $match->competition()->season;
    		$competitions = SeasonCompetition::where('season_id', '=', $season->id)->orderBy('name', 'asc')->get();
    		$competition = $match->competition();
    		$group = $match->group();
			if ($match->group()->phase->mode == 'league') { // league
				$league = $match->day->league;
				return view('competitions.match', compact('match', 'competitions', 'group', 'league', 'competition'));
			} else { // playoffs
				$playoff = $match->clash->round->playoff;
				return view('competitions.match', compact('match', 'competitions', 'group', 'playoff', 'competition'));
			}
    	} else {
    		return back()->with('error', 'El partido no existe');
    	}
    }

    public function stats($season_slug, $competition_slug, $participant_id = null)
    {
    	if (is_null($participant_id)) {
    		$participant_id = 0;
    		$participant = null;
    	} else {
    		$participant = SeasonParticipant::find($participant_id);
    	}

    	if (is_null($season_slug)) {
    		$season = active_season();
    	} else {
    		$season = Season::where('slug', '=', $season_slug)->first();
    	}

    	$competition = SeasonCompetition::where('slug', '=', $competition_slug)->firstOrFail();
    	$competitions = SeasonCompetition::where('season_id', '=', $season->id)->orderBy('name', 'asc')->get();

		if ($competition->phases->count()>0) {
			$phase = SeasonCompetitionPhase::where('competition_id', '=', $competition->id)
					 ->where('active', '=', 1)->orderBy('id', 'desc')->firstOrFail();
			$game_mode = $this->check_game_mode($phase);
		} else {
			return back()->with('error', 'La competición está en fase de configuración');
		}

		if ($game_mode == 'league') { // league

			if ($phase->groups->count()>0) {
				$group = SeasonCompetitionPhaseGroup::where('phase_id', '=', $phase->id)->firstOrFail();
				$league = $this->check_league($group);
			} else {
				return back()->with('error', 'La competición está en fase de configuración');
			}

			if ($competition->has_stats()) {
				$group_participants = SeasonCompetitionPhaseGroupParticipant::where('group_id', '=', $league->group->id)->get();

				if ($group_participants->count() == 0) {
					return back()->with('error', 'la liga no esta configurada');
				}

				$stats_goals = SeasonCompetitionStat::select('season_competitions_stats.player_id', \DB::raw('SUM(season_competitions_stats.goals) as goals'))
					->leftjoin('season_players', 'season_competitions_stats.player_id', '=', 'season_players.id')
					->leftjoin('season_participants', 'season_players.participant_id', '=', 'season_participants.id')
					->where('season_competitions_stats.competition_id', '=', $competition->id);
		    	if ($participant_id > 0) {
					$stats_goals = $stats_goals->where('season_participants.id', '=', $participant_id);
		    	}
				$stats_goals = $stats_goals->whereNotNull('goals')
					->groupBy('season_competitions_stats.player_id')
					->orderBy('goals', 'desc')
					->get();

				$stats_assists = SeasonCompetitionStat::select('season_competitions_stats.player_id', \DB::raw('SUM(season_competitions_stats.assists) as assists'))
					->leftjoin('season_players', 'season_competitions_stats.player_id', '=', 'season_players.id')
					->leftjoin('season_participants', 'season_players.participant_id', '=', 'season_participants.id')
					->where('season_competitions_stats.competition_id', '=', $competition->id);
		    	if ($participant_id > 0) {
					$stats_assists = $stats_assists->where('season_participants.id', '=', $participant_id);
		    	}
				$stats_assists = $stats_assists->whereNotNull('assists')
					->groupBy('season_competitions_stats.player_id')
					->orderBy('assists', 'desc')
					->get();

				$stats_yellow_cards = SeasonCompetitionStat::select('season_competitions_stats.player_id', \DB::raw('SUM(yellow_cards) as yellow_cards'))
					->leftjoin('season_players', 'season_competitions_stats.player_id', '=', 'season_players.id')
					->leftjoin('season_participants', 'season_players.participant_id', '=', 'season_participants.id')
					->where('season_competitions_stats.competition_id', '=', $competition->id);
		    	if ($participant_id > 0) {
					$stats_yellow_cards = $stats_yellow_cards->where('season_participants.id', '=', $participant_id);
		    	}
				$stats_yellow_cards = $stats_yellow_cards->whereNotNull('yellow_cards')
					->groupBy('season_competitions_stats.player_id')
					->orderBy('yellow_cards', 'desc')
					->get();

				$stats_red_cards = SeasonCompetitionStat::select('season_competitions_stats.player_id', \DB::raw('SUM(red_cards) as red_cards'))
					->leftjoin('season_players', 'season_competitions_stats.player_id', '=', 'season_players.id')
					->leftjoin('season_participants', 'season_players.participant_id', '=', 'season_participants.id')
					->where('season_competitions_stats.competition_id', '=', $competition->id);
		    	if ($participant_id > 0) {
					$stats_red_cards = $stats_red_cards->where('season_participants.id', '=', $participant_id);
		    	}
				$stats_red_cards = $stats_red_cards->whereNotNull('red_cards')
					->groupBy('season_competitions_stats.player_id')
					->orderBy('red_cards', 'desc')
					->get();

		        return view('competitions.league.stats', compact('participant_id', 'participant', 'stats_goals', 'stats_assists', 'stats_yellow_cards', 'stats_red_cards', 'group', 'league', 'competitions', 'competition'));
			} else {
				return redirect()->route('competitions.table', [$season->slug, $competition_slug, $participant_id]);
			}

		} else { // playoffs

			if ($phase->groups->count()>0) {
				$group = SeasonCompetitionPhaseGroup::where('phase_id', '=', $phase->id)->firstOrFail();
				$playoff = $this->check_playoff($group);
			} else {
				return back()->with('error', 'La competición está en fase de configuración');
			}

			if ($phase->groups->count()>0) {
				$group = SeasonCompetitionPhaseGroup::where('phase_id', '=', $phase->id)->firstOrFail();
				$league = $this->check_league($group);
			} else {
				return back()->with('error', 'La competición está en fase de configuración');
			}

			if ($competition->has_stats()) {
				$group_participants = SeasonCompetitionPhaseGroupParticipant::where('group_id', '=', $league->group->id)->get();

				if ($group_participants->count() == 0) {
					return back()->with('error', 'la competición no esta configurada');
				}

				$stats_goals = SeasonCompetitionStat::select('season_competitions_stats.player_id', \DB::raw('SUM(season_competitions_stats.goals) as goals'))
					->leftjoin('season_players', 'season_competitions_stats.player_id', '=', 'season_players.id')
					->leftjoin('season_participants', 'season_players.participant_id', '=', 'season_participants.id')
					->where('season_competitions_stats.competition_id', '=', $competition->id);
		    	if ($participant_id > 0) {
					$stats_goals = $stats_goals->where('season_participants.id', '=', $participant_id);
		    	}
				$stats_goals = $stats_goals->whereNotNull('goals')
					->groupBy('season_competitions_stats.player_id')
					->orderBy('goals', 'desc')
					->get();

				$stats_assists = SeasonCompetitionStat::select('season_competitions_stats.player_id', \DB::raw('SUM(season_competitions_stats.assists) as assists'))
					->leftjoin('season_players', 'season_competitions_stats.player_id', '=', 'season_players.id')
					->leftjoin('season_participants', 'season_players.participant_id', '=', 'season_participants.id')
					->where('season_competitions_stats.competition_id', '=', $competition->id);
		    	if ($participant_id > 0) {
					$stats_assists = $stats_assists->where('season_participants.id', '=', $participant_id);
		    	}
				$stats_assists = $stats_assists->whereNotNull('assists')
					->groupBy('season_competitions_stats.player_id')
					->orderBy('assists', 'desc')
					->get();

				$stats_yellow_cards = SeasonCompetitionStat::select('season_competitions_stats.player_id', \DB::raw('SUM(yellow_cards) as yellow_cards'))
					->leftjoin('season_players', 'season_competitions_stats.player_id', '=', 'season_players.id')
					->leftjoin('season_participants', 'season_players.participant_id', '=', 'season_participants.id')
					->where('season_competitions_stats.competition_id', '=', $competition->id);
		    	if ($participant_id > 0) {
					$stats_yellow_cards = $stats_yellow_cards->where('season_participants.id', '=', $participant_id);
		    	}
				$stats_yellow_cards = $stats_yellow_cards->whereNotNull('yellow_cards')
					->groupBy('season_competitions_stats.player_id')
					->orderBy('yellow_cards', 'desc')
					->get();

				$stats_red_cards = SeasonCompetitionStat::select('season_competitions_stats.player_id', \DB::raw('SUM(red_cards) as red_cards'))
					->leftjoin('season_players', 'season_competitions_stats.player_id', '=', 'season_players.id')
					->leftjoin('season_participants', 'season_players.participant_id', '=', 'season_participants.id')
					->where('season_competitions_stats.competition_id', '=', $competition->id);
		    	if ($participant_id > 0) {
					$stats_red_cards = $stats_red_cards->where('season_participants.id', '=', $participant_id);
		    	}
				$stats_red_cards = $stats_red_cards->whereNotNull('red_cards')
					->groupBy('season_competitions_stats.player_id')
					->orderBy('red_cards', 'desc')
					->get();

		        return view('competitions.playoffs.stats', compact('stats_goals', 'stats_assists', 'stats_yellow_cards', 'stats_red_cards', 'group', 'playoff', 'competitions', 'competition'));
		    } else {
				return redirect()->route('competitions.table', [$season->slug, $competition_slug, $participant_id]);
		    }
		}
    }

    public function editMatch($season_slug, $competition_slug, $match_id)
    {
    	$competition = SeasonCompetition::where('slug', '=', $competition_slug)->firstOrFail();
        $match = SeasonCompetitionMatch::find($match_id);

        if ($match->day_id > 0) {
        	return view('competitions.league.calendar.match', compact('match', 'competition'))->render();
        } elseif ($match->clash_id > 0) {
        	return view('competitions.playoffs.calendar.match', compact('match', 'competition'))->render();
        }
    }

    public function updateMatch($season_slug, $competition_slug, $match_id) {
        $match = SeasonCompetitionMatch::find($match_id);
        $competition = SeasonCompetition::where('slug', '=', $competition_slug)->first();

        if ($match->day_id > 0) {
	        if ($match->local_score == null && $match->visitor_score == null) {
		        $match->local_score = intval(request()->local_score);
		        $match->visitor_score = intval(request()->visitor_score);
		        $match->user_update_result = auth()->user()->id;
		        $match->date_update_result = now();
		        $match->save();

	            if ($competition->has_stats()) {
	                $this->assing_stats($competition, $match);
	            }

	            $this->assing_league_economy($match);

	            if ($match->day->league->pending_matches() == 0) {
	                if ($match->day->league->group->phase->is_last()) {
	                    $table_participants = $match->day->league->generate_table();
	                    // generate wiiner post
	                    $post = Post::create([
	                        'type' => 'champion',
	                        'transfer_id' => null,
	                        'match_id' => $match->id,
	                        'category' => $match->competition()->name,
	                        'title' => $table_participants[0]['participant']->participant->name() . ' (' . $table_participants[0]['participant']->participant->sub_name() . ') se proclama campeón!',
	                        'description' => 'Enhorabuena por el título!, y a ' . $table_participants[1]['participant']->participant->name() . ' (' . $table_participants[1]['participant']->participant->sub_name() . ') por el subcampeonato',
	                        'img' => null,
	                    ]);
	                }
	            }

	            $this->generate_result_post($match, $competition);

				$this->generate_telegram_notification($match);

		        return back()->with('success', 'Resultado registrado correctamente.');
		    } else {
		    	return back()->with('error', 'El resultado ya está registrado, contacta con los administradores si algún dato no es correcto.');
		    }
        } elseif ($match->clash_id > 0) {
	        $playoff = $match->clash->round->playoff;
	        $round = $match->clash->round;
	        $clash = $match->clash;

	        if (is_null($match->local_score) && is_null($match->visitor_score)) {
	            $match->local_score = intval(request()->local_score);
	            $match->visitor_score = intval(request()->visitor_score);
	            if (!$match->clash->round->round_trip || ($match->clash->round->round_trip && $match->order == 2)) {
	                $match->penalties_local_score = request()->penalties_local_score;
	                $match->penalties_visitor_score = request()->penalties_visitor_score;
	            }
	            $match->user_update_result = auth()->user()->id;
	            $match->date_update_result = now();
	            if (request()->sanctioned_id > 0) {
	                $match->sanctioned_id = request()->sanctioned_id;
	            }
	            $match->save();

	            if ($competition->has_stats()) {
	                $this->assing_stats($competition, $match);
	            }

	            if ($playoff->group->phase->competition->season->use_economy) {
	                $this->assing_playoff_economy($match);
	            }

	            if ($clash->winner()) {
	                // we verify that it is not the last round
	                $destiny_clash = null;
	                if (!$round->is_last_round()) {
	                    // we include in playoffs_rounds_participants the classified participant
	                    $playoff_round_participant = new PlayOffRoundParticipant;
	                    $playoff_round_participant->round_id = $round->next_round()->id;
	                    $playoff_round_participant->participant_id = $clash->winner()->id;
	                    $playoff_round_participant->save();
	                    // END

	                    if ($playoff->predefined_rounds) {
	                        $destiny_clash = PlayOffRoundClash::find($match->clash->clash_destiny_id);
	                        if ($destiny_clash) {
	                            if ($match->clash->order % 2 == 0) {
	                                $destiny_clash->visitor_id = $match->clash->winner()->id;
	                            } else {
	                                $destiny_clash->local_id = $match->clash->winner()->id;
	                            }
	                            $destiny_clash->save();
	                            if (!is_null($destiny_clash->local_id) && !is_null($destiny_clash->visitor_id)) {
	                                $this->generate_matches($destiny_clash);
	                            }
	                        }
	                    }
	                }
	                $this->generate_round_post($match, $destiny_clash);
	            }

	            $this->generate_result_post($match, $competition);

	            $this->generate_telegram_notification($match);

	            return back()->with('success', 'Resultado registrado correctamente.');
	        } else {
	            return back()->with('error', 'El resultado ya está registrado.');
	        }
        }
    }

    public function matchDetails($season_slug, $competition_slug, $match_id) {
        $match = SeasonCompetitionMatch::find($match_id);

        return view('competitions.league.calendar.match_details', compact('match'))->render();
    }



    ///helpers

    protected function check_league($group)
    {
        $league = SeasonCompetitionPhaseGroupLeague::where('group_id', '=', $group->id)->get()->first();

        if (!$league) {
        	$league = new SeasonCompetitionPhaseGroupLeague;
        	$league->group_id = $group->id;
        	$league->save();
        	// $league = SeasonCompetitionPhaseGroupLeague::where('group_id', '=', $group->id)->get()->first();
        }

        return $league;
    }

    protected function check_playoff($group)
    {
        $playoff = PlayOff::where('group_id', '=', $group->id)->get()->first();

        if (!$playoff) {
        	$playoff = new PlayOff;
        	$playoff->group_id = $group->id;
        	$playoff->save();
        	// $playoff = PlayOff::where('group_id', '=', $group->id)->get()->first();
        }

        return $playoff;
    }

	protected function check_game_mode($phase)
	{
		return $phase->mode;
	}

    protected function generate_matches($clash)
    {
		$match = new SeasonCompetitionMatch;
		$match->clash_id = $clash->id;
		$match->order = 1;
		$match->local_id = $clash->local_id;
		$match->local_user_id = $clash->local_participant->participant->id;
		$match->visitor_id = $clash->visitor_id;
		$match->visitor_user_id = $clash->visitor_participant->participant->id;
        $match->active = 1;
		$match->save();

		if ($clash->round->round_trip) {
			$match = new SeasonCompetitionMatch;
			$match->clash_id = $clash->id;
			$match->order = 2;
			$match->local_id = $clash->visitor_id;
			$match->local_user_id = $clash->visitor_participant->participant->id;
			$match->visitor_id = $clash->local_id;
			$match->visitor_user_id = $clash->local_participant->participant->id;
            $match->active = 1;
			$match->save();
		}
    }

    protected function assing_stats($competition, $match) {
        $local_players = SeasonPlayer::where('participant_id', '=', $match->local_participant->participant->id)->get();
        foreach ($local_players as $player) {
            if ($competition->stats_goals) {
                $goals = request()->{"stats_goals_".$player->id};
            } else {
                $goals = 0;
            }
            if ($competition->stats_assists) {
                $assists = request()->{"stats_assists_".$player->id};
            } else {
                $assists = 0;
            }
            if ($competition->stats_yellow_cards) {
                $yellow_cards = request()->{"stats_yellow_cards_".$player->id};
            } else {
                $yellow_cards = 0;
            }
            if ($competition->stats_red_cards) {
                $red_cards = request()->{"stats_red_cards_".$player->id};
            } else {
                $red_cards = 0;
            }
            if ($goals > 0 || $assists > 0 || $yellow_cards > 0 || $red_cards > 0) {
                $stat = new SeasonCompetitionStat;
                $stat->match_id = $match->id;
                $stat->competition_id = $competition->id;
                $stat->player_id = $player->id;
                if ($goals > 0) { $stat->goals = $goals; }
                if ($assists > 0) { $stat->assists = $assists; }
                if ($yellow_cards > 0) { $stat->yellow_cards = $yellow_cards; }
                if ($red_cards > 0) { $stat->red_cards = $red_cards; }
                $stat->save();
            }
        }

        $visitor_players = SeasonPlayer::where('participant_id', '=', $match->visitor_participant->participant->id)->get();
        foreach ($visitor_players as $player) {
            if ($competition->stats_goals) {
                $goals = request()->{"stats_goals_".$player->id};
            } else {
                $goals = 0;
            }
            if ($competition->stats_assists) {
                $assists = request()->{"stats_assists_".$player->id};
            } else {
                $assists = 0;
            }
            if ($competition->stats_yellow_cards) {
                $yellow_cards = request()->{"stats_yellow_cards_".$player->id};
            } else {
                $yellow_cards = 0;
            }
            if ($competition->stats_red_cards) {
                $red_cards = request()->{"stats_red_cards_".$player->id};
            } else {
                $red_cards = 0;
            }
            if ($goals > 0 || $assists > 0 || $yellow_cards > 0 || $red_cards > 0) {
                $stat = new SeasonCompetitionStat;
                $stat->match_id = $match->id;
                $stat->competition_id = $competition->id;
                $stat->player_id = $player->id;
                if ($goals > 0) { $stat->goals = $goals; }
                if ($assists > 0) { $stat->assists = $assists; }
                if ($yellow_cards > 0) { $stat->yellow_cards = $yellow_cards; }
                if ($red_cards > 0) { $stat->red_cards = $red_cards; }
                $stat->save();
            }
        }
    }

    protected function assing_playoff_economy($match) {
        if ((!$match->clash->round->round_trip) || ($match->clash->round->round_trip && $match->order == 2)) {
            // economy
            if ($match->clash->winner()->id == $match->local_id) {
                $local_amount = $match->clash->round->play_amount + $match->clash->round->win_amount;
                $visitor_amount = $match->clash->round->play_amount;
            } else {
                $local_amount = $match->clash->round->play_amount;
                $visitor_amount = $match->clash->round->play_amount + $match->clash->round->win_amount;
            }

            if ($match->local_id != $match->sanctioned_id) {
                $this->add_cash_history(
                    $match->local_participant->participant->id,
                    $match->id,
                    NULL,
                    NULL,
                    'Partido jugado, ' . $match->match_name(),
                    $local_amount,
                    'E'
                );
            }
            if ($match->visitor_id != $match->sanctioned_id) {
                $this->add_cash_history(
                    $match->visitor_participant->participant->id,
                    $match->id,
                    NULL,
                    NULL,
                    'Partido jugado, ' . $match->match_name(),
                    $visitor_amount,
                    'E'
                );
            }

            if ($match->clash->round->play_ontime_amount > 0)
            {
                if ($match->sanctioned_id == 0) {
                    $match_limit = new \Carbon\Carbon($match->date_limit_match());
                    $date_update_result = new \Carbon\Carbon($match->date_update_result);
                    if ($match_limit > $date_update_result) {
                        $play_in_limit = true;
                    } else {
                        $play_in_limit = false;
                    }

                    if ($play_in_limit) {
                        $this->add_cash_history(
                            $match->local_participant->participant->id,
		                    $match->id,
		                    NULL,
		                    NULL,
                            'Partido jugado en plazo, ' . $match->match_name(),
                            $match->clash->round->play_ontime_amount,
                            'E'
                        );
                        $this->add_cash_history(
                            $match->visitor_participant->participant->id,
		                    $match->id,
		                    NULL,
		                    NULL,
                            'Partido jugado en plazo, ' . $match->match_name(),
                            $match->clash->round->play_ontime_amount,
                            'E'
                        );
                    }
                } else {
                    if ($match->local_id != $match->sanctioned_id) {
                        $this->add_cash_history(
                            $match->local_participant->participant->id,
		                    $match->id,
		                    NULL,
		                    NULL,
                            'Partido jugado en plazo, ' . $match->match_name(),
                            $match->clash->round->play_ontime_amount,
                            'E'
                        );
                    }
                    if ($match->visitor_id != $match->sanctioned_id) {
                        $this->add_cash_history(
                            $match->visitor_participant->participant->id,
		                    $match->id,
		                    NULL,
		                    NULL,
                            'Partido jugado en plazo, ' . $match->match_name(),
                            $match->clash->round->play_ontime_amount,
                            'E'
                        );
                    }
                }
            }
        }
    }

    protected function assing_league_economy($match) {
        // economy
    	$this->add_cash_history(
    		$match->local_participant->participant->id,
            $match->id,
            NULL,
            NULL,
    		'Partido jugado, ' . $match->match_name(),
    		$match->day->league->play_amount,
    		'E'
    	);
    	$this->add_cash_history(
    		$match->visitor_participant->participant->id,
            $match->id,
            NULL,
            NULL,
    		'Partido jugado, ' . $match->match_name(),
    		$match->day->league->play_amount,
    		'E'
    	);

        $match_limit = new \Carbon\Carbon($match->date_limit);
        $date_update_result = new \Carbon\Carbon($match->date_update_result);
		if ($match_limit > $date_update_result) {
			$play_in_limit = true;
		} else {
			$play_in_limit = false;
		}

        if ($play_in_limit) {
        	$this->add_cash_history(
        		$match->local_participant->participant->id,
	            $match->id,
	            NULL,
	            NULL,
        		'Partido jugado en plazo, ' . $match->match_name(),
        		$match->day->league->play_ontime_amount,
        		'E'
        	);
        	$this->add_cash_history(
        		$match->visitor_participant->participant->id,
	            $match->id,
	            NULL,
	            NULL,
        		'Partido jugado en plazo, ' . $match->match_name(),
        		$match->day->league->play_ontime_amount,
        		'E'
        	);
        }

    	if ($match->local_score > $match->visitor_score) {
			$local_points = $match->day->league->win_amount;
			$visitor_points = $match->day->league->lose_amount;
			$local_result = "victoria";
			$visitor_result = "derrota";
    	} elseif ($match->local_score < $match->visitor_score) {
			$local_points = $match->day->league->lose_amount;
			$visitor_points = $match->day->league->win_amount;
			$local_result = "derrota";
			$visitor_result = "victoria";
    	} else { // draw
			$local_points = $match->day->league->draw_amount;
			$visitor_points = $match->day->league->draw_amount;
			$local_result = "empate";
			$visitor_result = "empate";
    	}
    	if ($local_points > 0) {
        	$this->add_cash_history(
        		$match->local_participant->participant->id,
	            $match->id,
	            NULL,
	            NULL,
        		'Puntos obtenidos (' . $local_result . ') en partido, ' . $match->match_name(),
        		$local_points,
        		'E'
        	);
    	}
    	if ($visitor_points > 0) {
        	$this->add_cash_history(
        		$match->visitor_participant->participant->id,
	            $match->id,
	            NULL,
	            NULL,
        		'Puntos obtenidos (' . $visitor_result . ') en partido, ' . $match->match_name(),
        		$visitor_points,
        		'E'
        	);
    	}
    }

    protected function generate_result_post($match, $competition) {
        $team_local = $match->local_participant->participant->name();
        $team_visitor = $match->visitor_participant->participant->name();
        $score = $match->local_score . '-' . $match->visitor_score;

        $post = Post::create([
            'type' => 'result',
            'transfer_id' => null,
            'match_id' => $match->id,
            'category' => $match->match_name(),
            'title' => "$team_local $score $team_visitor",
            'description' => null,
            'img' => $competition->img,
        ]);
    }

    protected function generate_round_post($match, $destiny_clash) {
        if ($destiny_clash) {
            if ($destiny_clash->local_id > 0 && $destiny_clash->visitor_id > 0) {
                if ($match->clash->winner()->id == $destiny_clash->local_id) {
                    $description = "Se enfrentará a " . $destiny_clash->visitor_participant->participant->name() . " de " . $destiny_clash->visitor_participant->participant->sub_name();
                } else {
                    $description = "Se enfrentará a " . $destiny_clash->local_participant->participant->name() . " de " . $destiny_clash->local_participant->participant->sub_name();
                }
            } else {
                $clash_rival = PlayOffRoundClash::where('round_id', '=', $match->clash->round->id)
                    ->where('clash_destiny_id', '=', $destiny_clash->id)
                    ->where('local_id', '<>', $match->clash->winner()->id)
                    ->where('visitor_id', '<>', $match->clash->winner()->id)
                    ->first();
                if ($clash_rival && $clash_rival->local_participant && $clash_rival->visitor_participant) {
                    $description = "Espera rival de la eliminatoria " . $clash_rival->local_participant->participant->name() . ' vs ' . $clash_rival->visitor_participant->participant->name();
                } else {
                    $description = "Sus posibles rivales pendiente de disputar rondas anteriores";
                }
            }
            $post = Post::create([
                'type' => 'default',
                'transfer_id' => null,
                'match_id' => null,
                'category' => $match->competition()->name,
                'title' => $match->clash->winner()->participant->name() . ' (' . $match->clash->winner()->participant->sub_name() . ') clasificado para ' . $destiny_clash->round->name,
                'description' => $description,
                'img' => $match->clash->winner()->participant->logo(),
            ]);
        } else {
            if ($match->clash->round->is_last_round()) {
                $post = Post::create([
                    'type' => 'champion',
                    'transfer_id' => null,
                    'match_id' => $match->id,
                    'category' => $match->competition()->name,
                    'title' => $match->clash->winner()->participant->name() . ' (' . $match->clash->winner()->participant->sub_name() . ') se proclama campeón!',
                    'description' => 'Enhorabuena por el título!, y a ' . $match->clash->loser()->participant->name() . ' (' . $match->clash->loser()->participant->sub_name() . ') por el subcampeonato',
                    'img' => null,
                ]);
            } else {
                $post = Post::create([
                    'type' => 'default',
                    'transfer_id' => null,
                    'match_id' => null,
                    'category' => $match->competition()->name,
                    'title' => $match->clash->winner()->participant->name() . ' (' . $match->clash->winner()->participant->sub_name() . ') clasificado para ' . $match->clash->round->next_round()->name,
                    'description' => 'Su rival en la siguiente ronda se conocerá tras el sorteo',
                    'img' => $match->clash->winner()->participant->logo(),
                ]);
            }
        }
    }

    protected function generate_league_telegram_notification($match) {
        // telegram notification
        $competition = $match->day->league->group->phase->competition->name;
		$team_local = $match->local_participant->participant->name();
		$team_local_slug = $match->local_participant->participant->team->slug;
		$team_local_budget = $match->local_participant->participant->budget();
		$user_local = $match->local_participant->participant->sub_name();
		$team_visitor = $match->visitor_participant->participant->name();
		$team_visitor_slug = $match->visitor_participant->participant->team->slug;
		$team_visitor_budget = $match->visitor_participant->participant->budget();
		$user_visitor = $match->visitor_participant->participant->sub_name();
		$score = $match->local_score . '-' . $match->visitor_score;
		$local_amount = $match->day->league->play_amount + $local_points;
		if ($play_in_limit) {
			$local_amount += $match->day->league->play_ontime_amount;
		}
		$visitor_amount = $match->day->league->play_amount + $visitor_points;
		if ($play_in_limit) {
			$visitor_amount += $match->day->league->play_ontime_amount;
		}
    	$local_economy = "    \xF0\x9F\x92\xB0" . $team_local . " (" . $user_local . ") <b>ingresa</b> " . number_format($local_amount, 2, ",", ".") . " mill.\n";
		$local_club_link = 'https://lfxo.es/clubs/' . $team_local_slug . '/economia';
    	$local_economy_link = "    <a href='$local_club_link'>Historial de economia</a>\n\n";

    	$visitor_economy = "    \xF0\x9F\x92\xB0" . $team_visitor . " (" . $user_visitor . ") <b>ingresa</b> " . number_format($visitor_amount, 2, ",", ".") . " mill.\n";
		$visitor_club_link = 'https://lfxo.es/clubs/' . $team_visitor_slug . '/economia';
    	$visitor_economy_link = "    <a href='$visitor_club_link'>Historial de economia</a>\n\n\n";

		$table_link = 'https://lfxo.es/competiciones/clasificacion/' . $season_slug . '/' . $competition_slug;
		$calendar_link = 'https://lfxo.es/competiciones/partidos/' . $season_slug . '/' . $competition_slug;
		// if ($match->day->league->group->phase->groups->count() > 1) {
		// 	$table_link .= '/' . $match->day->league->group->phase->slug . '/' . $match->day->league->group->phase->group->slug;
		// 	$calendar_link .= '/' . $match->day->league->group->phase->slug . '/' . $match->day->league->group->phase->group->slug;
		// } else {
		// 	if ($competition->phases->count() > 1) {
		// 		$table_link .= '/' . $match->day->league->group->phase->slug;
		// 		$calendar_link .= '/' . $match->day->league->group->phase->slug;
		// 	}
		// }
		// dd($table_link);
		$title = "\xE2\x9A\xBD Partido jugado \xF0\x9F\x8E\xAE" . ' - ' . $match->match_name();

		$text = "$title\n\n";
		$text .= "    <b>$team_local $score $team_visitor</b>\n\n\n";
		$text .= $local_economy;
		$text .= $local_economy_link;
		$text .= $visitor_economy;
		$text .= $visitor_economy_link;
		$text .= "\xF0\x9F\x93\x85 <a href='$calendar_link'>Calendario $competition</a>\n";
		$text .= "\xF0\x9F\x93\x8A <a href='$table_link'>Clasificación $competition</a>\n";

		$this->telegram_notification_channel($text);
    }

    protected function generate_playoff_telegram_notification($match) {
        $competition = $match->clash->round->playoff->group->phase->competition->name;
        $competition_slug = $match->clash->round->playoff->group->phase->competition->slug;
        $season_slug = $match->clash->round->playoff->group->phase->competition->season->slug;
        $team_local = $match->local_participant->participant->name();
        $team_local_slug = $match->local_participant->participant->team->slug;
        $user_local = $match->local_participant->participant->sub_name();
        $team_visitor = $match->visitor_participant->participant->name();
        $team_visitor_slug = $match->visitor_participant->participant->team->slug;
        $user_visitor = $match->visitor_participant->participant->sub_name();
        $score = $match->local_score . '-' . $match->visitor_score;
        $match_limit = new \Carbon\Carbon($match->date_limit_match());
        $date_update_result = new \Carbon\Carbon($match->date_update_result);
        if ($match_limit > $date_update_result) {
            $play_in_limit = true;
        } else {
            $play_in_limit = false;
        }
        if (($match->clash->round->round_trip == 1 && $match->order == 2) || $match->clash->round->round_trip == 0) {
            if ($match->sanctioned_id == 0) {
                if ($match->clash->winner()->id == $match->local_id) {
                    $local_amount = $match->clash->round->play_amount + $match->clash->round->win_amount;
                    $visitor_amount = $match->clash->round->play_amount;
                } else {
                    $local_amount = $match->clash->round->play_amount;
                    $visitor_amount = $match->clash->round->play_amount + $match->clash->round->win_amount;
                }
                if ($play_in_limit) {
                    $local_amount += $match->clash->round->play_ontime_amount;
                    $visitor_amount += $match->clash->round->play_ontime_amount;
                }
            } else {
                if ($match->local_id == $match->sanctioned_id) {
                    $local_amount = 0;
                    $visitor_amount = $match->clash->round->play_amount + $match->clash->round->win_amount + $match->clash->round->play_ontime_amount;
                } else {
                    $local_amount = $match->clash->round->play_amount + $match->clash->round->win_amount + $match->clash->round->play_ontime_amount;
                    $visitor_amount = 0;
                }
            }
            $local_economy = "    \xF0\x9F\x92\xB0" . $team_local . " (" . $user_local . ") <b>ingresa</b> " . number_format($local_amount, 2, ",", ".") . " mill.\n";
            $local_club_link = 'https://lfxo.es/clubs/' . $team_local_slug . '/economia';
            $local_economy_link = "    <a href='$local_club_link'>Historial de economia</a>\n\n";

            $visitor_economy = "    \xF0\x9F\x92\xB0" . $team_visitor . " (" . $user_visitor . ") <b>ingresa</b> " . number_format($visitor_amount, 2, ",", ".") . " mill.\n";
            $visitor_club_link = 'https://lfxo.es/clubs/' . $team_visitor_slug . '/economia';
            $visitor_economy_link = "    <a href='$visitor_club_link'>Historial de economia</a>\n\n\n";
        }

        $table_link = 'https://lfxo.es/competiciones/clasificacion/' . $season_slug . '/' . $competition_slug;
        $calendar_link = 'https://lfxo.es/competiciones/partidos/' . $season_slug . '/' . $competition_slug;
        $title = "\xE2\x9A\xBD Partido jugado \xF0\x9F\x8E\xAE" . ' - ' . $match->match_name();

        $text = "$title\n\n";
        if ($match->sanctioned_id == 0) {
            $text .= "    <b>$team_local $score $team_visitor</b>\n\n\n";
        } else {
            if ($match->local_id == $match->sanctioned_id) {
                $text .= "    <b>$team_local $score $team_visitor</b>\n";
                $text .= "    $team_local sancionado\n\n\n";
            } else {
                $text .= "    <b>$team_local $score $team_visitor</b>\n";
                $text .= "    $team_visitor sancionado\n\n\n";
            }
        }
        if (($match->clash->round->round_trip == 1 && $match->order == 2) || $match->clash->round->round_trip == 0) {
            $text .= $local_economy;
            $text .= $local_economy_link;
            $text .= $visitor_economy;
            $text .= $visitor_economy_link;
        }
        $text .= "\xF0\x9F\x93\x85 <a href='$calendar_link'>Calendario $competition</a>\n";
        $text .= "\xF0\x9F\x93\x8A <a href='$table_link'>Playoff $competition</a>\n";


        $this->telegram_notification_channel($text);
    }

}
