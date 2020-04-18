<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TableZone;
use App\SeasonPlayer;
use App\SeasonCompetitionStat;
use App\SeasonParticipant;
use App\SeasonParticipantCashHistory as Cash;
use App\SeasonCompetitionPhaseGroup;
use App\SeasonCompetitionPhaseGroupParticipant;
use App\SeasonCompetitionPhaseGroupLeague;
use App\SeasonCompetitionPhaseGroupLeagueDay;
use App\SeasonCompetitionMatch;
use App\SeasonCompetitionPhaseGroupLeagueTableZone;
use App\Post;

use Illuminate\Database\Eloquent\Collection;

use App\Events\TableWasSaved;
use App\Events\TableWasDeleted;
use App\Events\TableWasUpdated;
use App\Events\TableWasImported;

class SeasonCompetitionPhaseGroupLeagueController extends Controller
{
    public function index($competition_slug, $phase_slug, $group_slug)
    {
    	$group = SeasonCompetitionPhaseGroup::where('slug', '=', $group_slug)->firstOrFail();
        $league = $this->check_league($group);
        $league = $league->fresh();

    	$table_zones = TableZone::orderBy('name', 'asc')->get();

        return view('admin.seasons_competitions_phases_groups_leagues.index', compact('group', 'league', 'table_zones'));
    }

    public function save($competition_slug, $phase_slug, $group_slug, $id)
    {
    	$group = SeasonCompetitionPhaseGroup::where('slug', '=', $group_slug)->firstOrFail();
        $league = SeasonCompetitionPhaseGroupLeague::find($id);

        if ($league) {

        	foreach ($group->participants as $key => $p) {
        		$pos = $key + 1;
        		$pos_value = request()->{"position".$pos};

        		$league_table_zone = SeasonCompetitionPhaseGroupLeagueTableZone::where('league_id', '=', $league->id)->where('position', '=', $pos)->first();
        		if ($league_table_zone) {
        			$league_table_zone->table_zone_id = $pos_value;
        			$league_table_zone->save();
        		}
        	}

            $data = request()->all();
            $data['group_id'] = $group->id;

            $league->fill($data);
            if ($league->isDirty()) {
                $league->update($data);

                if ($league->update()) {
                    event(new TableWasUpdated($league, $league->group->name));
                    return back()->with('success', 'Configuración de liga guardada correctamente');
                } else {
                    return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
                }
            }
            return back()->with('info', 'No se han detectado cambios en la configuración de la liga.');
        } else {
        	return back()->with('warning', 'Acción cancelada. La liga que estabas editando ya no existe, se ha regenerado el archivo.');
        }
    }

    public function calendar($competition_slug, $phase_slug, $group_slug)
    {
    	$group = SeasonCompetitionPhaseGroup::where('slug', '=', $group_slug)->firstOrFail();
        $league = $this->check_league($group);

        return view('admin.seasons_competitions_phases_groups_leagues.calendar', compact('group', 'league'));
    }

    public function calendar_generate($competition_slug, $phase_slug, $group_slug)
    {
    	$group = SeasonCompetitionPhaseGroup::where('slug', '=', $group_slug)->firstOrFail();
        $league = $this->check_league($group);

        // we check if there are already days and games, if so, we don't generate new calendar
        $played_matches = 0;
        if ($league->days->count() > 0) {
        	foreach ($league->days as $day) {
				if ($day->matches->count() > 0) {
					foreach ($day->matches as $match) {
                        if (!is_null($match->local_score) && !is_null($match->visitor_score)) {
                            $played_matches++;
                        }
					}
				}
        	}
        }

        if ($played_matches == 0) {
    		$second_round = request()->second_round ? 1 : 0;
    		$inverse_order = request()->inverse_order ? 1 : 0;
            $this->generate_days($league->id, $second_round, $inverse_order);
            // comprobar si funciona cuando los participantes son impares y con otros numeros de participantes pares

            return back()->with('success', 'Se han generado las jornadas de la liga correctamente.');
        } else {
            return back()->with('error', 'No es posible generar el calendario ya que existen partidos jugados, debes resetear todos los partidos jugados previamente.');
        }
    }

    public function activate_day($day_id)
    {
        $day = SeasonCompetitionPhaseGroupLeagueDay::find($day_id);
        $day->active = 1;
        $day->save();

        if ($day->save()) {
            foreach ($day->matches as $match) {
                $match->active = $day->active;
                $match->save();
            }

            // generate new (post)
            $post = Post::create([
                'type' => 'default',
                'transfer_id' => null,
                'match_id' => null,
                'category' => $day->competition_name(),
                'title' => "Jornada " . $day->order . " disponible",
                'description' => "Los partidos ya están disponibles para jugar. Suerte a todos!",
                'img' => $day->league->group->phase->competition->img,
            ]);
            // telegram notification
            $season_slug = $day->league->group->phase->competition->season->slug;
            $competition_slug = $day->league->group->phase->competition->slug;
            $competition = $day->league->group->phase->competition->name;
            $calendar_link = 'https://lfxo.es/competiciones/' . $season_slug . '/' . $competition_slug . '/partidos';
            $title = "\xE2\x9A\xBD " . $day->day_name() . ", partidos disponibles";
            $text = "$title\n\n\n";
            foreach ($day->matches as $match) {
                $team_local = $match->local_participant->participant->name();
                $team_visitor = $match->visitor_participant->participant->name();
                $text .= "<b>    " . $team_local . " vs " .$team_visitor . "</b>\n";
            }
            $text .= "\n\n";
            $text .= "\xF0\x9F\x93\x85 <a href='$calendar_link'>Calendario $competition</a>\n";
            $this->telegram_notification_channel($text);

            return back()->with('success', 'Jornada activada correctamente');
        } else {
            return back()->with('error', 'Se ha producido un error, la jornada no se ha activado');
        }
    }

    public function desactivate_day($day_id)
    {
        $day = SeasonCompetitionPhaseGroupLeagueDay::find($day_id);
        $day->active = 0;
        $day->save();

        if ($day->save()) {
            foreach ($day->matches as $match) {
                $match->active = $day->active;
                $match->save();
            }
            return back()->with('success', 'Jornada desactivada correctamente');
        } else {
            return back()->with('error', 'Se ha producido un error, la jornada no se ha desactivado');
        }
    }

    public function edit_day_limit($day_id) {
        $day = SeasonCompetitionPhaseGroupLeagueDay::find($day_id);
        return view('admin.seasons_competitions_phases_groups_leagues.calendar.day_limit', compact('day'))->render();
    }

    public function update_day_limit($day_id) {
        $day = SeasonCompetitionPhaseGroupLeagueDay::find($day_id);
        $day->date_limit = request()->date_limit;
        $day->save();

        if ($day->save()) {
            foreach ($day->matches as $match) {
                $match->date_limit = $day->date_limit;
                $match->save();
                event(new TableWasUpdated($match, 'Editado plazo límite del partido ' . $match->id ));
            }
            event(new TableWasUpdated($day, 'Editado plazo límite de la jornada ' . $day->order ));

            return back()->with('success', 'Actualizada fecha límite de la jornada correctamente');
        }

        return back()->with('error', 'Se ha producido un error, la fecha no se ha actualizado');
    }

    public function edit_match_limit($match_id) {
        $match = SeasonCompetitionMatch::find($match_id);
        return view('admin.seasons_competitions_phases_groups_leagues.calendar.match_limit', compact('match'))->render();
    }

    public function update_match_limit($match_id) {
        $match = SeasonCompetitionMatch::find($match_id);
        $match->date_limit = request()->date_limit;
        $match->save();

        if ($match->save()) {
            event(new TableWasUpdated($match, 'Editado plazo límite del partido ' . $match->id ));

            return back()->with('success', 'Actualizada fecha límite del partido correctamente');
        }

        return back()->with('error', 'Se ha producido un error, la fecha no se ha actualizado');
    }

    public function table($competition_slug, $phase_slug, $group_slug)
    {
    	$group = SeasonCompetitionPhaseGroup::where('slug', '=', $group_slug)->firstOrFail();
        $league = $this->check_league($group);

		$group_participants = SeasonCompetitionPhaseGroupParticipant::where('group_id', '=', $league->group->id)->get();
        $table_participants = $league->generate_table();

        return view('admin.seasons_competitions_phases_groups_leagues.table', compact('group', 'league', 'table_participants'));
    }

    public function editMatch($competition_slug, $phase_slug, $group_slug, $id)
    {
    	$group = SeasonCompetitionPhaseGroup::where('slug', '=', $group_slug)->firstOrFail();
        $match = SeasonCompetitionMatch::find($id);

        return view('admin.seasons_competitions_phases_groups_leagues.calendar.match', compact('match', 'group'))->render();
    }

    public function updateMatch($match_id)
    {
        $match = SeasonCompetitionMatch::find($match_id);

        if ($match->local_score == null && $match->visitor_score == null) {
            $match->local_score = intval(request()->local_score);
            $match->visitor_score = intval(request()->visitor_score);
            $match->user_update_result = auth()->user()->id;
            $match->date_update_result = now();
            if (request()->sanctioned_id > 0) {
                $match->sanctioned_id = request()->sanctioned_id;
            }
            $match->save();

            if ($match->competition()->has_stats()) {
                $local_players = SeasonPlayer::where('participant_id', '=', $match->local_participant->participant->id)->get();
                foreach ($local_players as $player) {
                    if ($match->competition()->stats_goals) {
                        $goals = request()->{"stats_goals_".$player->id};
                    } else {
                        $goals = 0;
                    }
                    if ($match->competition()->stats_assists) {
                        $assists = request()->{"stats_assists_".$player->id};
                    } else {
                        $assists = 0;
                    }
                    if ($match->competition()->stats_yellow_cards) {
                        $yellow_cards = request()->{"stats_yellow_cards_".$player->id};
                    } else {
                        $yellow_cards = 0;
                    }
                    if ($match->competition()->stats_red_cards) {
                        $red_cards = request()->{"stats_red_cards_".$player->id};
                    } else {
                        $red_cards = 0;
                    }
                    if ($goals > 0 || $assists > 0 || $yellow_cards > 0 || $red_cards > 0) {
                        $stat = new SeasonCompetitionStat;
                        $stat->match_id = $match->id;
                        $stat->competition_id = $match->competition()->id;
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
                    if ($match->competition()->stats_goals) {
                        $goals = request()->{"stats_goals_".$player->id};
                    } else {
                        $goals = 0;
                    }
                    if ($match->competition()->stats_assists) {
                        $assists = request()->{"stats_assists_".$player->id};
                    } else {
                        $assists = 0;
                    }
                    if ($match->competition()->stats_yellow_cards) {
                        $yellow_cards = request()->{"stats_yellow_cards_".$player->id};
                    } else {
                        $yellow_cards = 0;
                    }
                    if ($match->competition()->stats_red_cards) {
                        $red_cards = request()->{"stats_red_cards_".$player->id};
                    } else {
                        $red_cards = 0;
                    }
                    if ($goals > 0 || $assists > 0 || $yellow_cards > 0 || $red_cards > 0) {
                        $stat = new SeasonCompetitionStat;
                        $stat->match_id = $match->id;
                        $stat->competition_id = $match->competition()->id;
                        $stat->player_id = $player->id;
                        if ($goals > 0) { $stat->goals = $goals; }
                        if ($assists > 0) { $stat->assists = $assists; }
                        if ($yellow_cards > 0) { $stat->yellow_cards = $yellow_cards; }
                        if ($red_cards > 0) { $stat->red_cards = $red_cards; }
                        $stat->save();
                    }
                }
            }

            // economy
            if ($match->local_id != $match->sanctioned_id) {
                $this->add_cash_history(
                    $match->local_participant->participant->id,
                    $match->id,
                    NULL,
                    NULL,
                    'Partido jugado, ' . $match->match_name(),
                    $match->day->league->play_amount,
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
                    $match->day->league->play_amount,
                    'E'
                );
            }

            if ($match->sanctioned_id == 0) {
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
            } else {
                if ($match->local_id != $match->sanctioned_id) {
                    $this->add_cash_history(
                        $match->local_participant->participant->id,
                        $match->id,
                        NULL,
                        NULL,
                        'Partido jugado en plazo, ' . $match->match_name(),
                        $match->day->league->play_ontime_amount,
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
                        $match->day->league->play_ontime_amount,
                        'E'
                    );
                }
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
            if ($match->sanctioned_id == 0) {
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
            } else {
                if ($match->local_id != $match->sanctioned_id) {
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
                }
                if ($match->visitor_id != $match->sanctioned_id) {
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
            }

            // telegram notification
            $competition = $match->day->league->group->phase->competition->name;
            $competition_slug = $match->day->league->group->phase->competition->slug;
            $season_slug = $match->day->league->group->phase->competition->season->slug;
            $team_local = $match->local_participant->participant->name();
            $team_local_slug = $match->local_participant->participant->team->slug;
            $team_local_budget = $match->local_participant->participant->budget();
            $user_local = $match->local_participant->participant->sub_name();
            $team_visitor = $match->visitor_participant->participant->name();
            $team_visitor_slug = $match->visitor_participant->participant->team->slug;
            $team_visitor_budget = $match->visitor_participant->participant->budget();
            $user_visitor = $match->visitor_participant->participant->sub_name();
            $score = $match->local_score . '-' . $match->visitor_score;
            if ($match->sanctioned_id == 0) {
                $local_amount = $match->day->league->play_amount + $local_points;
                if ($play_in_limit) {
                    $local_amount += $match->day->league->play_ontime_amount;
                }
                $visitor_amount = $match->day->league->play_amount + $visitor_points;
                if ($play_in_limit) {
                    $visitor_amount += $match->day->league->play_ontime_amount;
                }
            } else {
                if ($match->local_id == $match->sanctioned_id) {
                    $local_amount = 0;
                    $visitor_amount = $match->day->league->play_amount + $visitor_points + $match->day->league->play_ontime_amount;
                } else {
                    $local_amount = $match->day->league->play_amount + $local_points + $match->day->league->play_ontime_amount;
                    $visitor_amount = 0;
                }
            }
            $local_economy = "    \xF0\x9F\x92\xB0" . $team_local . " (" . $user_local . ") <b>ingresa</b> " . number_format($local_amount, 2, ",", ".") . " mill.\n";
            $local_club_link = 'https://lfxo.es/clubs/' . $team_local_slug . '/economia';
            $local_economy_link = "    <a href='$local_club_link'>Historial de economia</a>\n\n";

            $visitor_economy = "    \xF0\x9F\x92\xB0" . $team_visitor . " (" . $user_visitor . ") <b>ingresa</b> " . number_format($visitor_amount, 2, ",", ".") . " mill.\n";
            $visitor_club_link = 'https://lfxo.es/clubs/' . $team_visitor_slug . '/economia';
            $visitor_economy_link = "    <a href='$visitor_club_link'>Historial de economia</a>\n\n\n";

            $table_link = 'https://lfxo.es/competiciones/' . $season_slug . '/' . $competition_slug . '/clasificacion';
            $calendar_link = 'https://lfxo.es/competiciones/' . $season_slug . '/' . $competition_slug . '/partidos';
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
            $text .= $local_economy;
            $text .= $local_economy_link;
            $text .= $visitor_economy;
            $text .= $visitor_economy_link;
            $text .= "\xF0\x9F\x93\x85 <a href='$calendar_link'>Calendario $competition</a>\n";
            $text .= "\xF0\x9F\x93\x8A <a href='$table_link'>Clasificación $competition</a>\n";

            $this->telegram_notification_channel($text);

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

            // generate new (post)
            $post = Post::create([
                'type' => 'result',
                'transfer_id' => null,
                'match_id' => $match_id,
                'category' => $match->match_name(),
                'title' => "$team_local $score $team_visitor",
                'description' => null,
                'img' => $match->day->league->group->phase->competition->img,
            ]);

            return back()->with('success', 'Resultado registrado correctamente.');
        } else {
            return back()->with('error', 'El resultado ya está registrado.');
        }
    }

    public function editMatchStats($match_id)
    {
        $match = SeasonCompetitionMatch::find($match_id);

        return view('admin.seasons_competitions_phases_groups_leagues.calendar.match_stats', compact('match'))->render();
    }

    public function updateMatchStats($match_id)
    {
        $match = SeasonCompetitionMatch::find($match_id);

        // delete old stats
        $stats = SeasonCompetitionStat::where('match_id', '=', $match->id)->get();
        foreach ($stats as $stat) {
            $stat->delete();
        }

        // save new stats
        if ($match->competition()->has_stats()) {
            $local_players = SeasonPlayer::where('participant_id', '=', $match->local_participant->participant->id)->get();
            foreach ($local_players as $player) {
                if ($match->day->league->stats_goals) {
                    $goals = request()->{"stats_goals_".$player->id};
                } else {
                    $goals = 0;
                }
                if ($match->day->league->stats_assists) {
                    $assists = request()->{"stats_assists_".$player->id};
                } else {
                    $assists = 0;
                }
                if ($match->day->league->stats_yellow_cards) {
                    $yellow_cards = request()->{"stats_yellow_cards_".$player->id};
                } else {
                    $yellow_cards = 0;
                }
                if ($match->day->league->stats_red_cards) {
                    $red_cards = request()->{"stats_red_cards_".$player->id};
                } else {
                    $red_cards = 0;
                }
                if ($goals > 0 || $assists > 0 || $yellow_cards > 0 || $red_cards > 0) {
                    $stat = new SeasonCompetitionStat;
                    $stat->match_id = $match->id;
                    $stat->day_id = $match->day->id;
                    $stat->league_id = $match->day->league->id;
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
                if ($match->day->league->stats_goals) {
                    $goals = request()->{"stats_goals_".$player->id};
                } else {
                    $goals = 0;
                }
                if ($match->day->league->stats_assists) {
                    $assists = request()->{"stats_assists_".$player->id};
                } else {
                    $assists = 0;
                }
                if ($match->day->league->stats_yellow_cards) {
                    $yellow_cards = request()->{"stats_yellow_cards_".$player->id};
                } else {
                    $yellow_cards = 0;
                }
                if ($match->day->league->stats_red_cards) {
                    $red_cards = request()->{"stats_red_cards_".$player->id};
                } else {
                    $red_cards = 0;
                }
                if ($goals > 0 || $assists > 0 || $yellow_cards > 0 || $red_cards > 0) {
                    $stat = new SeasonCompetitionStat;
                    $stat->match_id = $match->id;
                    $stat->day_id = $match->day->id;
                    $stat->league_id = $match->day->league->id;
                    $stat->player_id = $player->id;
                    if ($goals > 0) { $stat->goals = $goals; }
                    if ($assists > 0) { $stat->assists = $assists; }
                    if ($yellow_cards > 0) { $stat->yellow_cards = $yellow_cards; }
                    if ($red_cards > 0) { $stat->red_cards = $red_cards; }
                    $stat->save();
                }
            }
        }

        return back()->with('success', 'Estadísticas del partido actualizadas correctamente.');
    }

    public function resetMatch($match_id)
    {
        $match = SeasonCompetitionMatch::find($match_id);

        // obtains data before change $match
        $match_limit = new \Carbon\Carbon($match->date_limit);
        $date_update_result = new \Carbon\Carbon($match->date_update_result);
        if ($match_limit > $date_update_result) {
            $play_in_limit = true;
        } else {
            $play_in_limit = false;
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

        if ($match->local_id == $match->sanctioned_id) {
            $local_sanctioned = true;
        } else {
            $local_sanctioned = false;
        }
        if ($match->visitor_id == $match->sanctioned_id) {
            $visitor_sanctioned = true;
        } else {
            $visitor_sanctioned = false;
        }

        $match->local_score = null;
        $match->visitor_score = null;
        $match->sanctioned_id = null;
        $match->user_update_result = null;
        $match->date_update_result = null;
        $match->save();

        if ($match->save()) {
            // delete stadistics
            $stats = SeasonCompetitionStat::where('match_id', '=', $match->id)->get();
            foreach ($stats as $stat) {
                $stat->delete();
            }
            // reset economy
            if (!$local_sanctioned) {
                $this->add_cash_history(
                    $match->local_participant->participant->id,
                    $match->id,
                    NULL,
                    NULL,
                    'Reset - Partido jugado, ' . $match->match_name(),
                    $match->day->league->play_amount,
                    'S'
                );
            }
            if (!$visitor_sanctioned) {
                $this->add_cash_history(
                    $match->visitor_participant->participant->id,
                    $match->id,
                    NULL,
                    NULL,
                    'Reset - Partido jugado, ' . $match->match_name(),
                    $match->day->league->play_amount,
                    'S'
                );
            }
            if ($play_in_limit) {
                if (!$local_sanctioned) {
                    $this->add_cash_history(
                        $match->local_participant->participant->id,
                        $match->id,
                        NULL,
                        NULL,
                        'Reset - Partido jugado en plazo, ' . $match->match_name(),
                        $match->day->league->play_ontime_amount,
                        'S'
                    );
                }
                if (!$visitor_sanctioned) {
                    $this->add_cash_history(
                        $match->visitor_participant->participant->id,
                        $match->id,
                        NULL,
                        NULL,
                        'Reset - Partido jugado en plazo, ' . $match->match_name(),
                        $match->day->league->play_ontime_amount,
                        'S'
                    );
                }
            }
            if ($local_points > 0) {
                if (!$local_sanctioned) {
                    $this->add_cash_history(
                        $match->local_participant->participant->id,
                        $match->id,
                        NULL,
                        NULL,
                        'Reset - Puntos obtenidos (' . $local_result . ') en partido, ' . $match->match_name(),
                        $local_points,
                        'S'
                    );
                }
            }
            if ($visitor_points > 0) {
                if (!$visitor_sanctioned) {
                    $this->add_cash_history(
                        $match->visitor_participant->participant->id,
                        $match->id,
                        NULL,
                        NULL,
                        'Reset - Puntos obtenidos (' . $visitor_result . ') en partido, ' . $match->match_name(),
                        $visitor_points,
                        'S'
                    );
                }
            }
            // END::reset economy

            // telegram notification
            $competition = $match->day->league->group->phase->competition->name;
            $competition_slug = $match->day->league->group->phase->competition->slug;
            $season_slug = $match->day->league->group->phase->competition->season->slug;
            $team_local = $match->local_participant->participant->name();
            $team_visitor = $match->visitor_participant->participant->name();
            $table_link = 'https://lfxo.es/competiciones/' . $season_slug . '/' . $competition_slug . '/clasificacion';
            $calendar_link = 'https://lfxo.es/competiciones/' . $season_slug . '/' . $competition_slug . '/partidos';
            $title = "\xF0\x9F\x9A\xA9 Partido reseteado \xF0\x9F\x94\x99" . ' - ' . $match->match_name();

            $text = "$title\n\n";
            $text .= "    <b>$team_local vs $team_visitor</b>\n";
            $text .= "    Resetado por la administración\n\n\n";
            $text .= "\xF0\x9F\x93\x85 <a href='$calendar_link'>Calendario $competition</a>\n";
            $text .= "\xF0\x9F\x93\x8A <a href='$table_link'>Clasificación $competition</a>\n";

            $this->telegram_notification_channel($text);

            // generate new (post)
            $post = Post::create([
                'type' => 'result',
                'transfer_id' => null,
                'match_id' => $match_id,
                'category' => $match->match_name(),
                'title' => "$team_local vs $team_visitor, reseteado",
                'description' => 'Partido reseteado por la administración, resultado, estadísticas y economía',
                'img' => $match->day->league->group->phase->competition->img,
            ]);


            return back()->with('success', 'Resultado reseteado correctamente.');
        }

        return back()->with('error', 'Se ha producido un error. El partido no se ha reseteado');

    }

    public function stats($competition_slug, $phase_slug, $group_slug)
    {
    	$group = SeasonCompetitionPhaseGroup::where('slug', '=', $group_slug)->firstOrFail();
    	$league = $this->check_league($group);

		$stats_goals = SeasonCompetitionStat::select('player_id', \DB::raw('SUM(goals) as goals'))
			->where('league_id', '=', $league->id)
			->whereNotNull('goals')
            ->groupBy('player_id')
            ->orderBy('goals', 'desc')
            ->get();
		$stats_assists = SeasonCompetitionStat::select('player_id', \DB::raw('SUM(assists) as assists'))
			->where('league_id', '=', $league->id)
			->whereNotNull('assists')
            ->groupBy('player_id')
            ->orderBy('assists', 'desc')
            ->get();
		$stats_yellow_cards = SeasonCompetitionStat::select('player_id', \DB::raw('SUM(yellow_cards) as yellow_cards'))
			->where('league_id', '=', $league->id)
			->whereNotNull('yellow_cards')
            ->groupBy('player_id')
            ->orderBy('yellow_cards', 'desc')
            ->get();
		$stats_red_cards = SeasonCompetitionStat::select('player_id', \DB::raw('SUM(red_cards) as red_cards'))
			->where('league_id', '=', $league->id)
			->whereNotNull('red_cards')
            ->groupBy('player_id')
            ->orderBy('red_cards', 'desc')
            ->get();

        return view('admin.seasons_competitions_phases_groups_leagues.stats', compact('stats_goals', 'stats_assists', 'stats_yellow_cards', 'stats_red_cards', 'group', 'league'));
    }





    // HELPER FUNCTIONS

    protected function check_league($group)
    {
        $league = SeasonCompetitionPhaseGroupLeague::where('group_id', '=', $group->id)->get()->first();

        if (!$league) {
        	$league = new SeasonCompetitionPhaseGroupLeague;
        	$league->group_id = $group->id;
        	$league->save();
        	$league = SeasonCompetitionPhaseGroupLeague::where('group_id', '=', $group->id)->get()->first();
        }

        if ($league->table_zones->count() == 0) {
            for ($i=0; $i < $league->group->num_participants; $i++) {
                $table_zones = new SeasonCompetitionPhaseGroupLeagueTableZone;
                $table_zones->league_id = $league->id;
                $table_zones->position = $i+1;
                $table_zones->save();
            }
        }

        return $league;
    }

    protected function generate_days($league_id, $second_round, $inverse_order)
    {
    	$league = SeasonCompetitionPhaseGroupLeague::find($league_id);
    	$days = SeasonCompetitionPhaseGroupLeagueDay::where('league_id', '=', $league_id)->orderBy('order', 'desc')->get();
        foreach ($days as $day) {
            foreach ($day->matches as $match) {
                $match->delete();
            }
            $day->delete();
        }
        $days = SeasonCompetitionPhaseGroupLeagueDay::where('league_id', '=', $league_id)->orderBy('order', 'desc')->get();
    	if ($days->count() > 0) {
    		$next_day = $days->first()->order + 1;
    	} else {
    		$next_day = 1;
    	}

    	$group_participants = SeasonCompetitionPhaseGroupParticipant::where('group_id', '=', $league->group->id)->inRandomOrder()->get();
		$participants = [];
		$i = 1;
		foreach ($group_participants as $participant) {
			$participants[$i] = $participant;
			$i++;
		}

		$num_participants = $league->group->num_participants;

		if ($num_participants % 2 == 0) { // num_participantes par
			$num_participants = $i-1;
		} else { // num_participantes impar
			$num_participants = $i;
		}

	    $num_players = ($num_participants > 0) ? (int)$num_participants : 4;
	    // If necessary, round up number of players to nearest even number.  -- / / Si el número necesario, reunir a los jugadores de número par más cercano.
	    $num_players += $num_players % 2;

	    // Generate matches for each round
	    for ($round = 1; $round < $num_players; $round++) {
	    	$day = new SeasonCompetitionPhaseGroupLeagueDay;
	    	$day->league_id = $league_id;
	    	$day->order = $next_day;
	    	$day->save();

			$day_to_repeat[$round]=$day->id;

	        $players_done = array();

	        // Match each player, except the last one
	        for ($player = 1; $player < $num_players; $player++) {
	            if (!in_array($player, $players_done)) {
	                // Select opponent.
	                $opponent = $round - $player;
	                $opponent += ($opponent < 0) ? $num_players : 1;

	                // Securing opponent is not the current player
	                if ($opponent != $player) {
	                    if (($player + $opponent) % 2 == 0 xor $player < $opponent) {
	                    	if ($participants[$player]->id > 0 && $participants[$opponent]->id > 0) {

							   	$match = new SeasonCompetitionMatch;
							   	$match->day_id = $day->id;
                                $match->clash_id = 0;
							   	$match->local_id = $participants[$player]->id;
							   	$match->local_user_id = $participants[$player]->participant->user->id;
							   	$match->visitor_id = $participants[$opponent]->id;
							   	$match->visitor_user_id = $participants[$opponent]->participant->user->id;
							   	$match->save();
	                    	}
	                    } else {
	                        if ($participants[$opponent]->id > 0 && $participants[$player]->id > 0) {
							   	$match = new SeasonCompetitionMatch;
							   	$match->day_id = $day->id;
                                $match->clash_id = 0;
							   	$match->local_id = $participants[$opponent]->id;
							   	$match->local_user_id = $participants[$opponent]->participant->user->id;
							   	$match->visitor_id = $participants[$player]->id;
							   	$match->visitor_user_id = $participants[$player]->participant->user->id;
							   	$match->save();
	                        }
	                    }
	                    // This pair of players are done for this round.
	                    $players_done[] = $player;
	                    $players_done[] = $opponent;
	                }
	            }
	        }

	        // Match the last player
	        if ($round % 2 == 0) {
	            $opponent = ($round + $num_players) / 2;
	            if ($participants[$num_players]->id > 0 && $participants[$opponent]->id > 0) {

				   	$match = new SeasonCompetitionMatch;
				   	$match->day_id = $day->id;
                    $match->clash_id = 0;
				   	$match->local_id = $participants[$num_players]->id;
				   	$match->local_user_id = $participants[$num_players]->participant->user->id;
				   	$match->visitor_id = $participants[$opponent]->id;
				   	$match->visitor_user_id = $participants[$opponent]->participant->user->id;
				   	$match->save();
	            }
	        } else {
	            $opponent = ($round + 1) / 2;
				if ($participants[$opponent]->id > 0 && $participants[$num_players]->id > 0) {
				   	$match = new SeasonCompetitionMatch;
				   	$match->day_id = $day->id;
                    $match->clash_id = 0;
				   	$match->local_id = $participants[$opponent]->id;
				   	$match->local_user_id = $participants[$opponent]->participant->user->id;
				   	$match->visitor_id = $participants[$num_players]->id;
				   	$match->visitor_user_id = $participants[$num_players]->participant->user->id;
				   	$match->save();
				}
	        }
	        $next_day++;
	    }

		// we created the days and matches of the second round
	    if ($second_round) {
	    	if ($inverse_order) {
		    	for ($i=(count($day_to_repeat)); $i > 0; $i --) {
		    		$copy_day = SeasonCompetitionPhaseGroupLeagueDay::find($day_to_repeat[$i]);

					// first we create the new day
		    		$day = new SeasonCompetitionPhaseGroupLeagueDay;
		    		$day->league_id = $league_id;
		    		$day->order = $next_day;
		    		$day->save();

					// now we create the matches of the day going through the matches of the day of the first round
		    		foreach ($copy_day->matches as $copy_match) {
		    			$match = new SeasonCompetitionMatch;
		    			$match->day_id = $day->id;
                        $match->clash_id = 0;
		    			$match->local_id = $copy_match->visitor_id;
		    			$match->local_user_id = $copy_match->visitor_user_id;
		    			$match->visitor_id = $copy_match->local_id;
		    			$match->visitor_user_id = $copy_match->local_user_id;
		    			$match->save();
		    		}
		    		$next_day++;
		    	}
	    	} else {
		    	for ($i=1; $i < (count($day_to_repeat)+1); $i ++) {
		    		$copy_day = SeasonCompetitionPhaseGroupLeagueDay::find($day_to_repeat[$i]);

					// first we create the new day
		    		$day = new SeasonCompetitionPhaseGroupLeagueDay;
		    		$day->league_id = $league_id;
		    		$day->order = $next_day;
		    		$day->save();

					// now we create the matches of the day going through the matches of the day of the first round
		    		foreach ($copy_day->matches as $copy_match) {
		    			$match = new SeasonCompetitionMatch;
		    			$match->day_id = $day->id;
                        $match->clash_id = 0;
		    			$match->local_id = $copy_match->visitor_id;
		    			$match->local_user_id = $copy_match->visitor_user_id;
		    			$match->visitor_id = $copy_match->local_id;
		    			$match->visitor_user_id = $copy_match->local_user_id;
		    			$match->save();
		    		}
		    		$next_day++;
		    	}
	    	}
	    }
    }

    protected function get_table_data_participant($league_id, $participant_id)
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
	    	$league = SeasonCompetitionPhaseGroupLeague::find($match->league_id);
	    	if (!is_null($match->local_score) && !is_null($match->visitor_score))
	    	{
	    		$data['pj'] = $data['pj'] + 1;

		    	if ($participant_id == $match->local_id) { //local
		    		if ($match->local_score > $match->visitor_score) {
						$data['pg'] = $data['pg'] + 1;
						$data['pts'] = $data['pts'] + intval($league->win_points);
		    		} elseif ($match->local_score == $match->visitor_score) {
		    			$data['pe'] = $data['pe'] + 1;
		    			$data['pts'] = $data['pts'] + intval($league->draw_points);
		    		} else {
		    			$data['pp'] = $data['pp'] + 1;
		    			$data['pts'] = $data['pts'] + intval($league->lose_points);
		    		}
		    		$data['gf'] = $data['gf'] + $match->local_score;
		    		$data['gc'] = $data['gc'] + $match->visitor_score;

		    	} else { //visitor
		    		if ($match->visitor_score > $match->local_score) {
						$data['pg'] = $data['pg'] + 1;
						$data['pts'] = $data['pts'] + intval($league->win_points);
		    		} elseif ($match->local_score == $match->visitor_score) {
		    			$data['pe'] = $data['pe'] + 1;
		    			$data['pts'] = $data['pts'] + intval($league->draw_points);
		    		} else {
		    			$data['pp'] = $data['pp'] + 1;
		    			$data['pts'] = $data['pts'] + intval($league->lose_points);
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
}
