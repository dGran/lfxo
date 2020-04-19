<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\SeasonParticipant;
use App\SeasonPlayer;
use App\SeasonParticipantCashHistory as Cash;
use App\SeasonCompetitionStat;

use App\Events\TableWasDeleted;

use Telegram\Bot\Laravel\Facades\Telegram;
use App\GeneralSetting;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	protected function add_cash_history($participant_id, $match_id, $transfer_id, $trade_id, $description, $amount, $movement) {
	    $cash = new Cash;
	    $cash->participant_id = $participant_id;
	    $cash->match_id = $match_id;
	    $cash->transfer_id = $transfer_id;
	    $cash->trade_id = $trade_id;
	    $cash->description = $description;
	    $cash->amount = $amount;
	    $cash->movement = $movement;
	    $cash->save();
	}

    protected function assing_stats($match) {
    	$competition = $match->competition();

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

	protected function destroy_group($group) {
	    foreach ($group->participants as $participant) {
	        $participant->delete();
	    }
	    if ($group->phase->mode == 'league') {
	        foreach ($group->league->days as $day) {
	            foreach ($day->matches as $match) {
	                foreach ($match->stats as $stat) {
	                    $stat->delete();
	                }
	                foreach ($match->cash_histories as $cash) {
	                    $cash->delete();
	                }
	                $match->delete();
	            }
	            $day->delete();
	        }
	        foreach ($group->league->table_zones as $table_zone) {
	            $table_zone->delete();
	        }
	        $group->league->delete();
	    } else {
	        foreach ($group->playoff->rounds as $round) {
	        	foreach ($round->clashes as $clash) {
		            foreach ($clash->matches as $match) {
		                foreach ($match->stats as $stat) {
		                    $stat->delete();
		                }
		                foreach ($match->cash_histories as $cash) {
		                    $cash->delete();
		                }
		                $match->delete();
		            }
		            $clash->delete();
		        }
				foreach ($round->participants as $participant) {
					$participant->delete();
				}
	            $round->delete();
	        }
	        $group->playoff->delete();
	    }

	    event(new TableWasDeleted($group, $group->name));
	    $group->delete();
	}

	protected function destroy_phase($phase) {
	    foreach ($phase->groups as $group) {
	        $this->destroy_group($group);
	    }
	    event(new TableWasDeleted($phase, $phase->name));
	    $phase->delete();
	}

	protected function destroy_competition($competition) {
	    foreach ($competition->phases as $phase) {
			$this->destroy_phase($phase);
	    }
        if ($competition->isLocalImg()) {
            if (\File::exists(public_path($competition->img))) {
                \File::delete(public_path($competition->img));
            }
        }
	    event(new TableWasDeleted($competition, $competition->name));
	    $competition->delete();
	}

	protected function telegram_notifications() {
		return $notifications = GeneralSetting::first()->telegram_notifications;
	}

	protected function telegram_source() {
	    return $source = GeneralSetting::first()->telegram_source;
	}

	protected function get_telegram_chat() {
	    $source = $this->telegram_source();
	    if ($source == 'production') {
	        $chat_id = env('TELEGRAM_CHANNEL_ID');
	    } else {
	        $chat_id = env('TELEGRAM_TEST_CHANNEL_ID');
	    }
	    return $chat_id;
	}

	protected function telegram_notification_channel($text) {
		if ($this->telegram_notifications()) {
	        $chat_id = $this->get_telegram_chat();
			Telegram::sendMessage([
			    'chat_id' => $chat_id,
			    'parse_mode' => 'HTML',
			    'text' => $text
			]);
		}
	}

	protected function telegram_notification_admin($text) {
		Telegram::sendMessage([
		    'chat_id' => env('TELEGRAM_ADMIN_CHANNEL_ID'),
		    'parse_mode' => 'HTML',
		    'text' => $text
		]);
	}

	protected function telegram_updatedActivity()
	{
	    $activity = Telegram::getUpdates();
	    dd($activity);
	}
}
