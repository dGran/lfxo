<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Post;
use App\PlayOff;
use App\PlayOffStat;
use App\PlayOffRound;
use App\PlayOffRoundClash;
use App\PlayOffRoundParticipant;
use App\SeasonCompetitionMatch;
use App\SeasonCompetitionPhaseGroup;
use App\SeasonCompetitionPhaseGroupParticipant;
use App\SeasonParticipant;
use App\SeasonParticipantCashHistory as Cash;

use App\Events\TableWasSaved;
use App\Events\TableWasDeleted;
use App\Events\TableWasUpdated;
use App\Events\TableWasImported;


class PlayOffController extends Controller
{
    public function index($competition_slug, $phase_slug, $group_slug)
    {
    	$group = SeasonCompetitionPhaseGroup::where('slug', '=', $group_slug)->firstOrFail();
        $playoff = $this->check_playoff($group);

        return view('admin.seasons_competitions_phases_groups_playoffs.index', compact('group', 'playoff'));
    }

    public function update($id) {
    	$playoff = PlayOff::find($id);
    	$playoff->predefined_rounds = request()->predefined_rounds;
    	$playoff->save();

    	return back()->with('success', 'Configuración del playoff actualizada correctamente');
    }

    public function roundUpdate($round_id) {
    	$round = PlayOffRound::find($round_id);
    	$round->name = request()->name;
        if (!is_null(request()->playoff_type)) {
    	   $round->round_trip = request()->playoff_type == 0 ? 0 : 1;
        }
		$round->double_value = request()->playoff_type == 2 ? 1 : 0;
    	$round->date_limit = request()->date_limit;
        $round->win_amount = is_null(request()->win_amount) ? 0 : request()->win_amount;
        $round->play_amount = is_null(request()->play_amount) ? 0 : request()->play_amount;
        $round->play_ontime_amount = is_null(request()->play_ontime_amount) ? 0 : request()->play_ontime_amount;
    	$round->save();

    	foreach ($round->clashes as $clash) {
    		$clash->date_limit = $round->date_limit;
    		$clash->save();
    		foreach ($clash->matches as $match) {
    			$match->date_limit = $clash->date_limit;
    			$match->save();
    		}
    	}

    	return back()->with('success', 'Configuración de ronda "' . $round->name . '" actualizada correctamente');
    }

    public function generate_rounds($playoff_id, $rounds)
    {
    	$playoff = PlayOff::find($playoff_id);

    	if ($rounds == 0) {
	        $participants = $playoff->group->participants->count();
	        $rounds = 1;
	        while ($participants > 2) {
	        	$rounds++;
	        	$participants = intval($participants / 2);
	        }
	    }

        $playoff->num_rounds = $rounds;
        $playoff->save();


		for ($i=1; $i < $playoff->num_rounds+1; $i++) {
			$round = new PlayOffRound;
			$round->playoff_id = $playoff->id;
			$round->name = "Ronda " . $i;
            $round->order = $i;
            if ($i == 1) { // copy participants for round 1
                $round->num_participants = $playoff->group->participants->count();
            } else {
                $last_round = PlayOffRound::where('playoff_id', '=', $playoff->id)->where('order', '=', $i-1)->get();
                $round->num_participants = $last_round->first()->num_participants / 2;
            }
            $round->play_amount = 0;
            $round->win_amount = 0;
            $round->play_ontime_amount = 0;
			$round->save();

			if ($i == 1) { // copy participants for round 1
				foreach ($playoff->group->participants as $group_participant) {
					$round_participant = new PlayOffRoundParticipant;
					$round_participant->round_id = $round->id;
					$round_participant->participant_id = $group_participant->id;
					$round_participant->save();
				}
			}

            // generate empty_clashes
            for ($x=0; $x < ($round->num_participants / 2); $x++) {
                $clash = new PlayOffRoundClash;
                $clash->round_id = $round->id;
                $clash->order = $x + 1;
                $clash->local_id = null;
                $clash->visitor_id = null;
                $clash->save();
            }
		}

        // once created the rounds and the pairings we assign the positions in the following rounds of the predefined table
        if ($playoff->predefined_rounds) {
            foreach ($playoff->rounds as $round) {
                $next_round = PlayOffRound::where('playoff_id', '=', $playoff->id)->where('order', '=', $round->order + 1)->first();
                if ($next_round) {
                    foreach ($round->clashes as $clash) {
                        if ($clash->order % 2 == 0) {
                            $destiny_clash_order = $clash->order / 2;
                        } else {
                            $destiny_clash_order = ($clash->order + 1) / 2;
                        }

                        $destiny_clash = PlayOffRoundClash::where('round_id', $next_round->id)->where('order', '=', $destiny_clash_order)->first();

                        $clash->clash_destiny_id = $destiny_clash->id;
                        $clash->save();
                    }
                }
            }
        }

		return back()->with('success', 'Se han generado las rondas correctamente');
    }

    public function reset_rounds($playoff_id)
    {
    	$playoff = PlayOff::find($playoff_id);

    	foreach ($playoff->rounds as $round) {
    		foreach ($round->clashes as $clash) {
    			foreach ($clash->matches as $match) {
    				$match->delete();
    			}
    			$clash->delete();
    		}
    		foreach ($round->participants as $participant) {
    			$participant->delete();
    		}
    		$round->delete();
    	}

		return back()->with('success', 'Se han eliminado todas las rondas y sus emparejamientos correctamente');
    }


    public function rounds($competition_slug, $phase_slug, $group_slug)
    {
    	$group = SeasonCompetitionPhaseGroup::where('slug', '=', $group_slug)->firstOrFail();
        $playoff = PlayOff::where('group_id', '=', $group->id)->get()->first();

        return view('admin.seasons_competitions_phases_groups_playoffs.rounds', compact('group', 'playoff'));
    }


    public function generate_clashes($round_id)
    {
    	$round = PlayOffRound::findOrFail($round_id);
		foreach ($round->clashes as $clash) {
			foreach ($clash->matches as $match) {
				$match->delete();
			}
			// $clash->delete();
		}

        $round_clashes = PlayOffRoundClash::where('round_id', '=', $round->id)->orderBy('order', 'asc')->get();
		$round_participants = PlayOffRoundParticipant::where('round_id', '=', $round->id)->inRandomOrder()->get();

        $part = 0;
        foreach ($round_clashes as $clash) {
			$clash->local_id = $round_participants[$part]->participant_id;
            $part++;
			$clash->visitor_id = $round_participants[$part]->participant_id;
            $part++;
			$clash->save();
			$this->generate_matches($clash);
        }
		return back()->with('success', 'Emparejamientos sorteados correctamente');
    }

    public function generate_empty_clashes($round_id)
    {
    	$round = PlayOffRound::findOrFail($round_id);
		foreach ($round->clashes as $clash) {
			foreach ($clash->matches as $match) {
				$match->delete();
			}
			$clash->delete();
		}

		$round_participants = PlayOffRoundParticipant::where('round_id', '=', $round->id)->inRandomOrder()->get();
		for ($i=0; $i < ($round_participants->count() / 2); $i++) {
			$clash = new PlayOffRoundClash;
			$clash->round_id = $round->id;
			$clash->order = $i + 1;
			$clash->local_id = null;
			$clash->visitor_id = null;
			$clash->save();
		}

		// if ($round->round_trip) {
		// 	$clashes = PlayOffRoundClash::where('round_id', '=', $round->id)->get();
		// 	foreach ($clashes as $clash) {
		// 		$second_clash = new PlayOffRoundClash;
		// 		$second_clash->round_id = $round->id;
		// 		$second_clash->order = $clash->order;
		// 		$second_clash->local_id = $clash->visitor_id;
		// 		$second_clash->visitor_id = $clash->local_id;
		// 		$second_clash->return_match = 1;
		// 		$second_clash->save();
		// 		$part++;
		// 	}
		// }

		return back()->with('success', 'Emparejamientos generados correctamente');
	}

	public function restore_clashes($round_id)
	{
    	$round = PlayOffRound::findOrFail($round_id);
		foreach ($round->clashes as $clash) {
			foreach ($clash->matches as $match) {
				$match->delete();
			}
			$clash->delete();
		}
		return back()->with('success', 'Los emparejamientos de la ronda se han restaurado correctamente');
	}

	public function assing_local_participant_in_clash($clash_id)
	{
		$clash = PlayOffRoundClash::find($clash_id);
		$round_id = $clash->round->id;

        $round_participants = PlayOffRoundParticipant::select('*')
        		->where('round_id', '=', $round_id)
                ->whereNotIn('participant_id', function($query) use ($round_id) {
                   $query->select('local_id')->from('playoffs_rounds_clashes')->whereNotNull('local_id')->where('round_id', '=', $round_id);
                })
                ->whereNotIn('participant_id', function($query) use ($round_id) {
                   $query->select('visitor_id')->from('playoffs_rounds_clashes')->whereNotNull('visitor_id')->where('round_id', '=', $round_id);
                })
                ->get();

        return view('admin.seasons_competitions_phases_groups_playoffs.rounds.assing_local_participant', compact('clash', 'round_participants'))->render();
	}

	public function update_assing_local_participant_in_clash($clash_id)
	{
		$clash = PlayOffRoundClash::find($clash_id);
		$clash->local_id = request()->clash_participant;
		$clash->save();

		if ($clash->local_id && $clash->visitor_id) {
			$this->generate_matches($clash);
		}

		return back()->with('success', 'Participante local asignado al emparejamiento correctamente');
	}

	public function assing_visitor_participant_in_clash($clash_id)
	{
		$clash = PlayOffRoundClash::find($clash_id);
		$round_id = $clash->round->id;

        $round_participants = PlayOffRoundParticipant::select('*')
        		->where('round_id', '=', $round_id)
                ->whereNotIn('participant_id', function($query) use ($round_id) {
                   $query->select('visitor_id')->from('playoffs_rounds_clashes')->whereNotNull('visitor_id')->where('round_id', '=', $round_id);
                })
                ->whereNotIn('participant_id', function($query) use ($round_id) {
                   $query->select('local_id')->from('playoffs_rounds_clashes')->whereNotNull('local_id')->where('round_id', '=', $round_id);
                })
                ->get();

        return view('admin.seasons_competitions_phases_groups_playoffs.rounds.assing_visitor_participant', compact('clash', 'round_participants'))->render();
	}

	public function update_assing_visitor_participant_in_clash($clash_id)
	{
		$clash = PlayOffRoundClash::find($clash_id);
		$clash->visitor_id = request()->clash_participant;
		$clash->save();

		if ($clash->local_id && $clash->visitor_id) {
			$this->generate_matches($clash);
		}

		return back()->with('success', 'Participante visitante asignado al emparejamiento correctamente');
	}

	public function liberate_local_participant_in_clash($clash_id)
	{
		$clash = PlayOffRoundClash::find($clash_id);
		$clash->local_id = NULL;
		$clash->save();

		foreach ($clash->matches as $match) {
			$match->delete();
		}

		return back()->with('success', 'Participante local eliminado correctamente');
	}

	public function liberate_visitor_participant_in_clash($clash_id)
	{
		$clash = PlayOffRoundClash::find($clash_id);
		$clash->visitor_id = NULL;
		$clash->save();

		foreach ($clash->matches as $match) {
			$match->delete();
		}

		return back()->with('success', 'Participante visitante eliminado correctamente');
	}

	public function editMatch($match_id)
	{
        $match = SeasonCompetitionMatch::find($match_id);
    	$group = $match->clash->round->playoff->group;

        return view('admin.seasons_competitions_phases_groups_playoffs.rounds.match', compact('match', 'group'))->render();
	}

    public function updateMatch($match_id)
    {
        $match = SeasonCompetitionMatch::find($match_id);
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

            if ($match->competition()->has_stats()) {
                $this->assing_stats($match);
            }

            if ($playoff->group->phase->competition->season->use_economy) {
                $this->assing_economy($match);
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

            $this->generate_result_post($match);

            $this->generate_telegram_notification($match);

            return back()->with('success', 'Resultado registrado correctamente.');
        } else {
            return back()->with('error', 'El resultado ya está registrado.');
        }
    }






    // HELPERS FUNCTIONS

    protected function check_playoff($group)
    {
        $playoff = PlayOff::where('group_id', '=', $group->id)->get()->first();

        if (!$playoff) {
        	$playoff = new PlayOff;
        	$playoff->group_id = $group->id;
        	$playoff->save();
        	$playoff = PlayOff::where('group_id', '=', $group->id)->get()->first();
        }

        // if ($playoff->rounds->count() == 0) {
        // 	if ($playoff->num_rounds == 0) {
        // 		$this->generate_rounds($playoff, null);
        // 	}
        // 	if ($playoff->num_rounds == 1) {
        // 		$this->generate_rounds($playoff, 1);
        // 	}
        // }

        return $playoff;
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

    protected function assing_economy($match) {
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

    protected function generate_result_post($match) {
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
            'img' => $match->clash->round->playoff->group->phase->competition->img,
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

    protected function generate_telegram_notification($match) {
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
            $local_club_link = 'https://lpx.es/clubs/' . $team_local_slug . '/economia';
            $local_economy_link = "    <a href='$local_club_link'>Historial de economia</a>\n\n";

            $visitor_economy = "    \xF0\x9F\x92\xB0" . $team_visitor . " (" . $user_visitor . ") <b>ingresa</b> " . number_format($visitor_amount, 2, ",", ".") . " mill.\n";
            $visitor_club_link = 'https://lpx.es/clubs/' . $team_visitor_slug . '/economia';
            $visitor_economy_link = "    <a href='$visitor_club_link'>Historial de economia</a>\n\n\n";
        }

        $table_link = 'https://lpx.es/competiciones/clasificacion/' . $season_slug . '/' . $competition_slug;
        $calendar_link = 'https://lpx.es/competiciones/partidos/' . $season_slug . '/' . $competition_slug;
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