<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Telegram\Bot\Laravel\Facades\Telegram;

use App\SeasonParticipant;
use App\SeasonParticipantCashHistory as Cash;
use App\GeneralSetting;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // Telegram
    public function telegram_notifications() {
    	return $notifications = GeneralSetting::first()->telegram_notifications;
    }

    public function telegram_source() {
        return $source = GeneralSetting::first()->telegram_source;
    }

    public function get_telegram_chat() {
        $source = $this->telegram_source();
        if ($source == 'production') {
            $chat_id = env('TELEGRAM_CHANNEL_ID');
        } else {
            $chat_id = env('TELEGRAM_TEST_CHANNEL_ID');
        }
        return $chat_id;
    }

    public function telegram_notification_channel($text) {
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
        dd(env('TELEGRAM_ADMIN_CHANNEL_ID'));
		Telegram::sendMessage([
		    'chat_id' => env('TELEGRAM_ADMIN_CHANNEL_ID'),
		    'parse_mode' => 'HTML',
		    'text' => $text
		]);
    }

    public function telegram_updatedActivity()
    {
        $activity = Telegram::getUpdates();
        dd($activity);
    }
    // END: Telegram


    // Competitions
    protected function add_cash_history($participant_id, $description, $amount, $movement) {
        $cash = new Cash;
        $cash->participant_id = $participant_id;
        $cash->description = $description;
        $cash->amount = $amount;
        $cash->movement = $movement;
        $cash->save();
    }
    // END: Competitions

    // meter todas las funciones que pueden ser utiles en mas controladores

}
