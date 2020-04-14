<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SeasonParticipant;
use App\SeasonCompetitionPhaseGroupParticipant;
use App\Post;
use App\Press;
use App\SeasonCompetitionMatch;
use App\Season;

class ClubController extends Controller
{
    public function clubs($season_slug = null)
    {
        $season = $this->get_season($season_slug);
        $season_slug = $season->slug;
        $seasons = Season::orderBy('name', 'asc')->get();

        $participants = $this->get_participants($season_slug);
        return view('clubs.index', compact('participants', 'season', 'season_slug', 'seasons'));
    }

    public function club($season_slug, $slug)
    {
        $participants = $this->get_participants($season_slug);
        $participant = $this->get_participant($season_slug, $slug);

        $par = SeasonCompetitionPhaseGroupParticipant::where('participant_id', '=', $participant->id)->first();

        return view('clubs.club', compact('participants', 'participant', 'par', 'season_slug'));
    }

    public function clubRoster($season_slug, $slug)
    {
        $participants = $this->get_participants($season_slug);
        $participant = $this->get_participant($season_slug, $slug);

        return view('clubs.roster', compact('participants', 'participant', 'season_slug'));
    }

    public function clubEconomy($season_slug, $slug)
    {
        $participants = $this->get_participants($season_slug);
        $participant = $this->get_participant($season_slug, $slug);

        $cash = 0;
        foreach ($participant->cash_history as $cash_history) {
            if ($cash_history->movement == "E") {
                $cash += $cash_history->amount;
                $cash_history['cash'] = $cash;
            } else {
                $cash -= $cash_history->amount;
                $cash_history['cash'] = $cash;
            }
        }

        return view('clubs.economy', compact('participants', 'participant', 'cash_history', 'season_slug'));
    }

    public function clubCalendar($season_slug, $slug)
    {
        // $aux = SeasonCompetitionMatch::all();
        // foreach ($aux as $match) {
        //     if ($match->day_id) {
        //         if ($match->day) {
        //             if ($match->day->league) {
        //                 $match->competition_id = $match->competition()->id;
        //                 if (is_null($match->date_limit)) {
        //                     $match->date_limit = $match->day->date_limit;
        //                 }
        //             }
        //         }
        //     } elseif ($match->clash_id) {
        //         if ($match->clash) {
        //             if ($match->clash->round) {
        //                 $match->competition_id = $match->competition()->id;
        //                 if (is_null($match->date_limit)) {
        //                     if (is_null($match->clash->date_limit)) {
        //                         $match->date_limit = $match->clash->round->date_limit;
        //                     } else {
        //                         $match->date_limit = $match->clash->date_limit;
        //                     }
        //                 }
        //             }
        //         }
        //     }
        //     $match->save();
        // }
        // return redirect()->route('club', [$season_slug, $slug])->with('info', 'Update succesflly.');

        $participants = $this->get_participants($season_slug);
        $participant = $this->get_participant($season_slug, $slug);

        $season = $this->get_season($season_slug);

        $matches = SeasonCompetitionMatch::
            leftJoin('playoffs_rounds_clashes', 'playoffs_rounds_clashes.id', '=', 'season_competitions_matches.clash_id')
            ->leftJoin('season_competitions_phases_groups_leagues_days', 'season_competitions_phases_groups_leagues_days.id', '=', 'season_competitions_matches.day_id')
            ->leftJoin('season_competitions_phases_groups_participants as local_group_participant', 'local_group_participant.id', '=', 'season_competitions_matches.local_id')
            ->leftJoin('season_competitions_phases_groups_participants as visitor_group_participant', 'visitor_group_participant.id', '=', 'season_competitions_matches.visitor_id')
            ->leftJoin('season_participants as local_participant', 'local_participant.id', '=', 'local_group_participant.participant_id')
            ->leftJoin('season_participants as visitor_participant', 'visitor_participant.id', '=', 'visitor_group_participant.participant_id')
            ->select('season_competitions_matches.*')
            ->where(function ($query) use ($participant) {
                $query->where('local_participant.id', '=', $participant->id)
                      ->orWhere('visitor_participant.id', '=', $participant->id);
            })
            ->orderBy('season_competitions_matches.date_limit', 'asc')
            ->orderBy('season_competitions_matches.competition_id', 'asc')
            ->orderBy('season_competitions_phases_groups_leagues_days.order', 'asc')
            ->orderBy('playoffs_rounds_clashes.order', 'asc')
            ->orderBy('season_competitions_matches.order', 'asc')
            ->get();

        return view('clubs.calendar', compact('participants', 'participant', 'matches', 'season_slug', 'season'));
    }

    public function pendingMatches($season_slug, $slug)
    {
        $participants = $this->get_participants($season_slug);
        $participant = $this->get_participant($season_slug, $slug);

        $season = $this->get_season($season_slug);

        $matches = SeasonCompetitionMatch::
            leftJoin('playoffs_rounds_clashes', 'playoffs_rounds_clashes.id', '=', 'season_competitions_matches.clash_id')
            ->leftJoin('season_competitions_phases_groups_leagues_days', 'season_competitions_phases_groups_leagues_days.id', '=', 'season_competitions_matches.day_id')
            ->leftJoin('season_competitions_phases_groups_participants as local_group_participant', 'local_group_participant.id', '=', 'season_competitions_matches.local_id')
            ->leftJoin('season_competitions_phases_groups_participants as visitor_group_participant', 'visitor_group_participant.id', '=', 'season_competitions_matches.visitor_id')
            ->leftJoin('season_participants as local_participant', 'local_participant.id', '=', 'local_group_participant.participant_id')
            ->leftJoin('season_participants as visitor_participant', 'visitor_participant.id', '=', 'visitor_group_participant.participant_id')
            ->select('season_competitions_matches.*')
            ->where('season_competitions_matches.active', '=', 1)
            ->where(function ($query) use ($participant) {
                $query->where('local_participant.id', '=', $participant->id)
                      ->orWhere('visitor_participant.id', '=', $participant->id);
            })
            ->where(function ($query) use ($participant) {
                $query->whereNull('season_competitions_matches.local_score')
                      ->WhereNull('season_competitions_matches.visitor_score');
            })
            ->orderBy('season_competitions_matches.date_limit', 'asc')
            ->orderBy('season_competitions_matches.competition_id', 'asc')
            ->orderBy('season_competitions_phases_groups_leagues_days.order', 'asc')
            ->orderBy('playoffs_rounds_clashes.order', 'asc')
            ->orderBy('season_competitions_matches.order', 'asc')
            ->get();

        return view('clubs.pending_matches', compact('participants', 'participant', 'matches', 'season_slug', 'season'));
    }

    public function clubPress($season_slug, $slug)
    {
        $participants = $this->get_participants($season_slug);
        $participant = $this->get_participant($season_slug, $slug);
        $presses = Press::where('participant_id', '=', $participant->id)->orderBy('created_at', 'desc')->get();

        return view('clubs.press', compact('participants', 'participant', 'presses', 'season_slug'));
    }

    public function clubPressAdd($season_slug, $slug)
    {
        if (auth()->guest()) {
            return back()->with('info', 'La página ha expirado debido a la inactividad.');
        } else {
            if (!user_is_participant(auth()->user()->id)) {
                return back()->with('error', 'No eres participante del torneo.');
            } else {
                $participant = $this->get_participant($season_slug, $slug);
                if (auth()->user()->id != $participant->user_id) {
                    return back()->with('error', 'No puedes crear notas de prensa de otros equipos.');
                } else {
                    $participant = $this->get_participant($season_slug, $slug);
                    $press = new Press;
                    $press->participant_id = $participant->id;
                    $press->title = request()->title;
                    $press->description = request()->description;
                    $press->save();
                    if ($press->save()) {
                        $post = Post::create([
                            'type' => 'press',
                            'transfer_id' => null,
                            'match_id' => null,
                            'press_id' => $press->id,
                            'category' => 'RUEDA DE PRENSA - ' . $participant->team->name,
                            'title' => $press->title,
                            'description' => $press->description,
                            'img' => 'img/microphone.png',
                        ]);

                        $club_link = 'https://lpx.es/clubs/'.$season_slug.'/'.$participant->team->slug.'/sala-de-prensa';
                        $club_name = $participant->team->name;
                        $user_name = $participant->user->name;
                        $title = "\xF0\x9F\x92\xAC Declaraciones de $user_name ($club_name) \xF0\x9F\x92\xAC";
                        $text = "$title\n\n";
                        $text .= "    <b>$press->title</b>\n";
                        $text .= "    " . $press->description . "\n\n";
                        $text .= "\xF0\x9F\x8F\xA0 <a href='$club_link'>Sala de prensa de $club_name</a>\n";
                        $this->telegram_notification_channel($text);
                        return back()->with('success', 'Nota de prensa enviada correctamente.');
                    }
                }
            }
        }
    }


    // helpers functions

    protected function get_season($season_slug)
    {
        if (is_null($season_slug)) {
            return active_season();
        } else {
            return Season::where('slug', '=', $season_slug)->first();
        }
    }

    protected function get_participants($season_slug)
    {
        $season = $this->get_season($season_slug);
        return SeasonParticipant::
            leftJoin('teams', 'teams.id', '=', 'season_participants.team_id')
            ->leftJoin('users', 'users.id', '=', 'season_participants.user_id')
            ->select('season_participants.*', 'teams.name as team_name', 'users.name as user_name')
            ->seasonId($season->id)->orderBy('teams.name', 'asc')->get();
    }

    protected function get_participant($season_slug, $slug)
    {
        $season = $this->get_season($season_slug);
        return SeasonParticipant::
            leftJoin('teams', 'teams.id', '=', 'season_participants.team_id')
            ->leftJoin('users', 'users.id', '=', 'season_participants.user_id')
            ->select('season_participants.*', 'teams.name as team_name', 'users.name as user_name')
            ->seasonId($season->id)
            ->where('teams.slug', '=', $slug)
            ->first();
    }
}
