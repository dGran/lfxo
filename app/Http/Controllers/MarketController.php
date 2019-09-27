<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Telegram\Bot\Laravel\Facades\Telegram;
use App\Player;
use App\Showcase;
use App\FavoritePlayer;
use App\Season;
use App\SeasonPlayer;
use App\SeasonParticipant;
use App\SeasonParticipantCashHistory as Cash;
use App\Transfer;
use App\Post;
use App\Trade;
use App\TradeDetail;


class MarketController extends Controller
{
    public function index()
    {
        $page = request()->page;

        $perPage = 10;

        $filterSeason = active_season()->id;

        $filterName = null;
        if (!is_null(request()->filterName)) {
        	$filterName = request()->filterName;
        }

        $filterParticipant = request()->filterParticipant;
        if ($filterParticipant == NULL) {
        	$filterParticipant = -1;
        }

    	//list of players
        $players = Transfer::select('transfers.*', 'season_players.participant_id', 'season_players.season_id', 'players.name', 'season_participants.clauses_received')
        // $players = Transfer::select('transfers.*', 'season_players.*', 'players.*', 'season_participants.*')
        	->leftjoin('season_players', 'season_players.id', '=', 'transfers.player_id')
        	->leftjoin('players', 'players.id', '=', 'season_players.player_id')
        	->leftjoin('season_participants', 'season_participants.id', '=', 'season_players.participant_id');
		$players->where('season_players.season_id', "=", $filterSeason);
        if (!is_null($filterName)) {
        	$players->where('players.name', "LIKE", "%$filterName%");
        }
        if ($filterParticipant >= 0) {
            $players = $players->where('season_players.participant_id', '=', $filterParticipant);
        }
		$players = $players->orderBy('transfers.created_at', 'desc');
        $players = $players->paginate($perPage, ['*'], 'page', $page);

	    //list of participants
        if (Season::find($filterSeason)->participant_has_team) {
            $participants = SeasonParticipant::
            leftJoin('teams', 'teams.id', '=', 'season_participants.team_id')
            ->select('season_participants.*', 'teams.name as team_name')
            ->seasonId($filterSeason)->orderBy('team_name', 'asc')->get();
        } else {
            $participants = SeasonParticipant::
            leftJoin('users', 'users.id', '=', 'season_participants.user_id')
            ->select('season_participants.*', 'users.name as user_name')
            ->seasonId($filterSeason)->orderBy('user_name', 'asc')->get();
        }
		//return view
        return view('market.index', compact('players', 'participants', 'filterName', 'filterParticipant', 'page'));
    }

    public function playerView($id)
    {
        $player = SeasonPlayer::find($id);
        if ($player) {
            return view('general_modals.player_view', compact('player'))->render();
        }
    }

    public function addFavoritePlayer($player_id, $participant_id)
    {
        if (SeasonPlayer::find($player_id) && SeasonParticipant::find($participant_id)) {
        	$favorite = new FavoritePlayer;
        	$favorite->player_id = $player_id;
        	$favorite->participant_id = $participant_id;
        	$favorite->save();
        }
    	$player = SeasonPlayer::find($player_id);
    	return view('market.partials.favorite', compact('player'))->render();
    }

    public function removeFavoritePlayer($player_id, $participant_id)
    {
    	$favorite = FavoritePlayer::where('player_id', '=', $player_id)->where('participant_id', '=', $participant_id)->first();
    	if ($favorite) {
    		$favorite->delete();
    	}
    	$player = SeasonPlayer::find($player_id);
    	return view('market.partials.favorite', compact('player'))->render();
    }

    public function signFreePlayer($id)
    {
    	if (auth()->guest()) {
    		return back()->with('info', 'La página ha expirado debido a la inactividad.');
    	} else {
	        $player = SeasonPlayer::find($id);
	        if ($player) {
	        	if (user_is_participant(auth()->user()->id)) {
	        		// validations
	        		if ($player->participant_id > 0) {
	        			return back()->with('error', 'No puedes fichar al jugador, ya ha sido fichado por ' . $player->participant->name() . '.');
	        		}
	        		if (participant_of_user()->max_players_limit()) {
	        			return back()->with('error', 'No puedes fichar al jugador. Actualmente tienes el máximo de jugadores en tu plantilla.');
	        		}
	        		if (participant_of_user()->budget() < $player->season->free_players_cost) {
	        			return back()->with('error', 'No puedes fichar al jugador. No dispones de los ' . $player->season->free_players_cost . ' mill. que cuesta el jugador en tu presupuesto.');
	        		}

	        		$participant_from = $player->participant_id;
	        		$participant_to = participant_of_user()->id;

		        	$this->add_cash_history(
		        		$participant_to,
		        		'Fichaje del agente libre ' . $player->player->name,
		        		$player->season->free_players_cost,
		        		'S'
		        	);

		        	$this->add_transfer(
		        		'free',
		        		$player->id,
		        		$participant_from,
		        		$participant_to,
		        		$player->season->free_players_cost
		        	);
		        	$transfer = Transfer::orderBy('id', 'desc')->first();

					$this->generate_new(
						'transfer',
						$transfer->id,
						NULL
		        	);

		        	$player->participant_id = $participant_to;
		        	$player->market_phrase = null;
		        	$player->untransferable = 0;
		        	$player->player_on_loan = 0;
		        	$player->transferable = 0;
		        	$player->sale_price = 0;
		        	$player->sale_auto_accept = 0;
		        	$player->price = $player->season->free_players_new_salary * 10;
		        	$player->salary = $player->season->free_players_new_salary;
		        	$player->save();
		        	if ($player->save()) {
		        		$this->manage_player_showcase($player);
		            	return back()->with('success', $player->player->name . ' ha fichado por tu equipo.');
		        	} else {
		        		return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
		        	}
	        	} else {
	        		return back()->with('error', 'Acción cancelada. No eres participante.');
	        	}
	        }
	        return back();
    	}
    }

    public function payClausePlayer($id)
    {
    	if (auth()->guest()) {
    		return back()->with('info', 'La página ha expirado debido a la inactividad.');
    	} else {
	        $player = SeasonPlayer::find($id);
	        if ($player) {
	        	if (user_is_participant(auth()->user()->id)) {
	        		$participant_from = $player->participant;
	        		$participant_to = participant_of_user();

	        		// validations
	        		if (!$player->allow_clause_pay) {
	        			return back()->with('error', 'No puedes pagar la claúsula de un jugador al cual ya le han pagado la claúsula.');
	        		}
	        		if ($participant_to->id == $player->participant_id) {
	        			return back()->with('error', 'No puedes pagar la claúsula de un jugador de tu equipo.');
	        		}
	        		if ($participant_to->clauses_paid_limit()) {
	        			return back()->with('error', 'No puedes pagar la claúsula del jugador. Ya has llegado al límite de claúsulas pagadas.');
	        		}
	        		if ($participant_from->clauses_received_limit()) {
	        			return back()->with('error', 'No puedes pagar la claúsula del jugador. ' . $participant_from->team->name .' ya ha llegado al límite de claúsulas recibidas.');
	        		}
	        		if ($participant_to->max_players_limit()) {
	        			return back()->with('error', 'No puedes pagar la claúsula del jugador. Actualmente tienes el máximo de jugadores en tu plantilla.');
	        		}
	        		if ($participant_to->budget() < $player->clause_price()) {
	        			return back()->with('error', 'No puedes pagar la claúsula del jugador. No dispones de los ' . $player->clause_price() . ' mill. que cuesta el jugador en tu presupuesto.');
	        		}
	        		// END::validations

	        		// generate cash movements
		        	$this->add_cash_history(
		        		$participant_to->id,
		        		'Pago de claúsula del jugador ' . $player->player->name,
		        		$player->price,
		        		'S'
		        	);
		        	$this->add_cash_history(
		        		$participant_to->id,
		        		'Impuestos del pago de claúsula del jugador ' . $player->player->name,
		        		$player->price * 0.10,
		        		'S'
		        	);
		        	$this->add_cash_history(
		        		$participant_from->id,
		        		'Pago de claúsula del jugador ' . $player->player->name,
		        		$player->price,
		        		'E'
		        	);
		        	// END::generate cash movements

		        	// save transfer
		        	$this->add_transfer(
		        		'clause',
		        		$player->id,
		        		$participant_from->id,
		        		$participant_to->id,
		        		$player->price * 1.10
		        	);

		        	// generate post (new)
		        	$transfer = Transfer::orderBy('id', 'desc')->first();
					$this->generate_new(
						'transfer',
						$transfer->id,
						NULL
		        	);

		        	// clauses counter for participants
		        	$participant_from->clauses_received += 1;
		        	$participant_from->save();
		        	$participant_to->paid_clauses += 1;
		        	$participant_to->save();

		        	// reset player market data
		        	$player->participant_id = $participant_to->id;
		        	$player->allow_clause_pay = 0;
		        	$player->market_phrase = null;
		        	$player->untransferable = 0;
		        	$player->player_on_loan = 0;
		        	$player->transferable = 0;
		        	$player->sale_price = 0;
		        	$player->sale_auto_accept = 0;
		        	$player->price = $player->price + 10;
		        	$player->salary = $player->salary + 1;
		        	$player->save();
		        	if ($player->save()) {
		        		$this->manage_player_showcase($player);
		            	return back()->with('success', 'Has pagado la claúsula del jugador ' . $player->player->name . ' y se ha incorporado por tu equipo.');
		        	} else {
		        		return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
		        	}
	        	} else {
	        		return back()->with('error', 'Acción cancelada. No eres participante.');
	        	}
	        }
	        return back();
    	}
    }

    public function signNowPlayer($id)
    {
    	if (auth()->guest()) {
    		return back()->with('info', 'La página ha expirado debido a la inactividad.');
    	} else {
	        $player = SeasonPlayer::find($id);
	        if ($player) {
	        	if (user_is_participant(auth()->user()->id)) {
	        		$participant_from = $player->participant;
	        		$participant_to = participant_of_user();

	        		// validations
	        		if (!$player->sale_auto_accept) {
	        			return back()->with('error', 'Compra cancelada. El jugador ya no está en venta directa.');
	        		}
	        		if ($participant_to->id == $player->participant_id) {
	        			return back()->with('error', 'No puedes comprar a un jugador de tu equipo.');
	        		}
	        		if ($participant_to->max_players_limit()) {
	        			return back()->with('error', 'No puedes fichar al jugador. Actualmente tienes el máximo de jugadores en tu plantilla.');
	        		}
	        		if ($participant_to->budget() < $player->sale_price) {
	        			return back()->with('error', 'No puedes comprar al jugador. No dispones de los ' . $player->sale_price . ' mill. que cuesta el jugador en tu presupuesto.');
	        		}
	        		// END::validations

	        		// generate cash movements
		        	$this->add_cash_history(
		        		$participant_to->id,
		        		'Compra directa del jugador ' . $player->player->name,
		        		$player->sale_price,
		        		'S'
		        	);
		        	$this->add_cash_history(
		        		$participant_from->id,
		        		'Venta directa del jugador ' . $player->player->name,
		        		$player->sale_price,
		        		'E'
		        	);
		        	// END::generate cash movements

		        	// save transfer
		        	$this->add_transfer(
		        		'negotiation',
		        		$player->id,
		        		$participant_from->id,
		        		$participant_to->id,
		        		$player->sale_price
		        	);

		        	// generate post (new)
		        	$transfer = Transfer::orderBy('id', 'desc')->first();
					$this->generate_new(
						'transfer',
						$transfer->id,
						NULL
		        	);

		        	// reset player market data
		        	$player->participant_id = $participant_to->id;
		        	$player->market_phrase = null;
		        	$player->untransferable = 0;
		        	$player->player_on_loan = 0;
		        	$player->transferable = 0;
		        	$player->sale_price = 0;
		        	$player->sale_auto_accept = 0;
		        	$player->price = $player->price;
		        	$player->salary = $player->salary;
		        	$player->save();
		        	if ($player->save()) {
		        		$this->manage_player_showcase($player);
		            	return back()->with('success', 'Has realizado una compra directa por el jugador ' . $player->player->name . ' y se ha incorporado por tu equipo.');
		        	} else {
		        		return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
		        	}
	        	} else {
	        		return back()->with('error', 'Acción cancelada. No eres participante.');
	        	}
	        }
	        return back();
    	}
    }

    public function onSale()
    {
    	$order = request()->order;
        if (!$order) {
            $order = 'date_desc';
        }
        $order_ext = $this->saleGetOrder($order);
        $filterSeason = active_season()->id;
        if (request()->filterParticipant == NULL) { request()->filterParticipant = 0; }
        $filterParticipant = request()->filterParticipant;
        $filterPosition = request()->filterPosition;
    	if (request()->overall_range) {
	    	$overall_rating = (explode( ';', request()->overall_range));
	    	$filterOverallRangeFrom = $overall_rating[0];
	    	$filterOverallRangeTo = $overall_rating[1];
    	} else {
    		$filterOverallRangeFrom = 70;
	    	$filterOverallRangeTo = 99;
    	}
    	if (request()->sale_price_range) {
	    	$sale_price_range = (explode( ';', request()->sale_price_range));
	    	$filterSalePriceRangeFrom = $sale_price_range[0];
	    	$filterSalePriceRangeTo = $sale_price_range[1];
    	} else {
    		$filterSalePriceRangeFrom = 0;
	    	$filterSalePriceRangeTo = 500;
    	}
    	$filterState = 'all';
		if (request()->filterState) {
			$filterState = request()->filterState;
		}

    	$players = Showcase::select('showcase.*')
	        ->join('season_players', 'season_players.id', '=', 'showcase.player_id')
	        ->join('players', 'players.id', '=', 'season_players.player_id');
        $players = $players->where('season_players.season_id', '=', $filterSeason);
        if ($filterParticipant > 0) {
            $players = $players->where('season_players.participant_id', '=', $filterParticipant);
        }
        if ($filterPosition != NULL) {
            $players = $players->where('players.position', '=', $filterPosition);
        }
        // $players->where('active', '=', 1);
        $players = $players->where('players.overall_rating', '>=', $filterOverallRangeFrom);
        $players = $players->where('players.overall_rating', '<=', $filterOverallRangeTo);
        $players = $players->where('season_players.sale_price', '>=', $filterSalePriceRangeFrom);
        $players = $players->where('season_players.sale_price', '<=', $filterSalePriceRangeTo);
        if ($filterState != 'all') {
        	switch ($filterState) {
        		case 'transferable':
        			$players = $players->where('season_players.transferable', '=', 1);
        			break;
        		case 'onloan':
        			$players = $players->where('season_players.player_on_loan', '=', 1);
        			break;
        		case 'saleprice':
        			$players = $players->where('season_players.sale_price', '>', 0);
        			break;
        		case 'buynow':
        			$players = $players->where('season_players.sale_auto_accept', '=', 1);
        			break;
        	}
        }
		$players = $players->orderBy($order_ext['sortField'], $order_ext['sortDirection'])
			->orderBy('showcase.created_at', 'desc')
	        ->get();

        if (Season::find($filterSeason)->participant_has_team) {
            $participants = SeasonParticipant::
            leftJoin('teams', 'teams.id', '=', 'season_participants.team_id')
            ->select('season_participants.*', 'teams.name as team_name')
            ->seasonId($filterSeason)->orderBy('team_name', 'asc')->get();
        } else {
            $participants = SeasonParticipant::
            leftJoin('users', 'users.id', '=', 'season_participants.user_id')
            ->select('season_participants.*', 'users.name as user_name')
            ->seasonId($filterSeason)->orderBy('user_name', 'asc')->get();
        }
        $positions = Player::select('position')->distinct()->where('players_db_id', '=', Season::find($filterSeason)->players_db_id)->orderBy('position', 'asc')->get();

        return view('market.sale', compact('players', 'participants', 'positions', 'filterParticipant', 'filterPosition', 'filterOverallRangeFrom', 'filterOverallRangeTo', 'filterTransferable', 'filterOnLoan', 'filterBuyNow', 'filterState', 'filterSalePriceRangeFrom', 'filterSalePriceRangeTo', 'order'));
    }

    public function onSalePlayer($id)
    {
        $player = SeasonPlayer::find($id);
        if ($player) {
            return view('market.sale.view', compact('player'))->render();
        } else {
            return view('market.sale.view-empty')->render();
        }
    }

    public function search()
    {
    	//data of user->participant
    	if (!auth()->guest() && user_is_participant(auth()->user()->id)) {
    		$participant_of_user = participant_of_user();
    	}
    	//filter variables
    	$order = request()->order;
        if (!$order) {
            $order = 'overall_desc';
        }

        $pagination = request()->pagination;
        if (!$pagination == null) {
            $perPage = $pagination;
        } else {
            $perPage = 12;
        }

        $page = request()->page;

        $order_ext = $this->searchGetOrder($order);

        $filterSeason = active_season()->id;

        $filterName = null;
        if (!is_null(request()->filterName)) {
        	$filterName = request()->filterName;
        }

        $filterParticipant = request()->filterParticipant;
        if ($filterParticipant == NULL) {
        	$filterParticipant = -1;
        }

        $filterPosition = request()->filterPosition;

        $filterNation = request()->filterNation;

        $filterOriginalTeam = request()->filterOriginalTeam;

        $filterOriginalLeague = request()->filterOriginalLeague;

		if (request()->filterHideFree == "on") {
			$filterHideFree = true;
		} else {
			$filterHideFree = false;
		}

		if (request()->filterHideClausePaid == "on") {
			$filterHideClausePaid = true;
		} else {
			$filterHideClausePaid = false;
		}

		if (request()->filterHideParticipantClauseLimit == "on") {
			$filterHideParticipantClauseLimit = true;
		} else {
			$filterHideParticipantClauseLimit = false;
		}

		if (!auth()->guest() && user_is_participant(auth()->user()->id)) {
			if (request()->filterShowClausesCanPay == "on") {
				$filterShowClausesCanPay = true;
			} else {
				$filterShowClausesCanPay = false;
			}
		} else {
			$filterShowClausesCanPay = false;
		}

    	if (request()->overall_range) {
	    	$overall_rating = (explode( ';', request()->overall_range));
	    	$filterOverallRangeFrom = $overall_rating[0];
	    	$filterOverallRangeTo = $overall_rating[1];
    	} else {
    		$filterOverallRangeFrom = 70;
	    	$filterOverallRangeTo = 99;
    	}

    	if (request()->clause_range) {
	    	$clause_range = (explode( ';', request()->clause_range));
	    	$filterClauseRangeFrom = $clause_range[0];
	    	$filterClauseRangeTo = $clause_range[1];
    	} else {
    		$filterClauseRangeFrom = 0;
	    	$filterClauseRangeTo = 500;
    	}

    	if (request()->age_range) {
	    	$age_range = (explode( ';', request()->age_range));
	    	$filterAgeRangeFrom = $age_range[0];
	    	$filterAgeRangeTo = $age_range[1];
    	} else {
    		$filterAgeRangeFrom = 15;
	    	$filterAgeRangeTo = 45;
    	}

    	if (request()->height_range) {
	    	$height_range = (explode( ';', request()->height_range));
	    	$filterHeightRangeFrom = $height_range[0];
	    	$filterHeightRangeTo = $height_range[1];
    	} else {
    		$filterHeightRangeFrom = 150;
	    	$filterHeightRangeTo = 210;
    	}

    	//list of players
        $players = SeasonPlayer::select('season_players.*', 'season_participants.clauses_received')
        	->leftjoin('players', 'players.id', '=', 'season_players.player_id')
        	->leftjoin('season_participants', 'season_participants.id', '=', 'season_players.participant_id');
		$players->where('season_players.season_id', "=", $filterSeason);
        $players->where('active', '=', 1);
        if (!is_null($filterName)) {
        	$players->where('players.name', "LIKE", "%$filterName%");
        }
        if ($filterParticipant >= 0) {
            $players = $players->where('season_players.participant_id', '=', $filterParticipant);
        }
        if ($filterShowClausesCanPay) {
        	if (!$participant_of_user->clauses_paid_limit()) {
	        	$players = $players->where(function($q) use ($participant_of_user) {
	          		$q->where('season_players.participant_id', '!=', 0)
	            	  ->where('season_players.participant_id', '!=', $participant_of_user->id);
	      		});
	        	$players = $players->where('season_players.allow_clause_pay', '=', 1);
	        	$players = $players->where('season_participants.clauses_received', '<', active_season()->max_clauses_received);
	        	$players = $players->where(\DB::raw('season_players.price * 1.10'), '<', $participant_of_user->budget());
        	} else {
        		$players = $players->where('season_players.id', '=', -1);
        	}
        } else {
	        if ($filterHideFree) {
	        	$players = $players->where('season_players.participant_id', '!=', 0);
	        }
	        if ($filterHideClausePaid) {
	        	$players = $players->where('season_players.allow_clause_pay', '=', 1);
	        }
	        if ($filterHideParticipantClauseLimit) {
	        	$players = $players->where('season_participants.clauses_received', '<', active_season()->max_clauses_received);
	        }
        }
        if ($filterPosition != NULL) {
            $players = $players->where('players.position', '=', $filterPosition);
        }
        if ($filterNation != NULL) {
            $players = $players->where('players.nation_name', '=', $filterNation);
        }
        if ($filterOriginalTeam != NULL) {
            $players = $players->where('players.team_name', '=', $filterOriginalTeam);
        }
        if ($filterOriginalLeague != NULL) {
            $players = $players->where('players.league_name', '=', $filterOriginalLeague);
        }
        $players = $players->where('players.overall_rating', '>=', $filterOverallRangeFrom);
        $players = $players->where('players.overall_rating', '<=', $filterOverallRangeTo);
        $players = $players->where('season_players.price', '>=', $filterClauseRangeFrom);
        $players = $players->where('season_players.price', '<=', $filterClauseRangeTo);
        $players = $players->where('players.age', '>=', $filterAgeRangeFrom);
        $players = $players->where('players.age', '<=', $filterAgeRangeTo);
        $players = $players->where('players.height', '>=', $filterHeightRangeFrom);
        $players = $players->where('players.height', '<=', $filterHeightRangeTo);
		$players = $players->orderBy($order_ext['sortField'], $order_ext['sortDirection']);
		if ($order_ext['sortField'] == 'players.overall_rating') {
			$players = $players->orderBy('players.name', 'asc');
		} else {
			$players = $players->orderBy('players.overall_rating', 'desc');
		}
        $players = $players->paginate($perPage, ['*'], 'page', $page);

	    //list of participants
        if (Season::find($filterSeason)->participant_has_team) {
            $participants = SeasonParticipant::
            leftJoin('teams', 'teams.id', '=', 'season_participants.team_id')
            ->select('season_participants.*', 'teams.name as team_name')
            ->seasonId($filterSeason)->orderBy('team_name', 'asc')->get();
        } else {
            $participants = SeasonParticipant::
            leftJoin('users', 'users.id', '=', 'season_participants.user_id')
            ->select('season_participants.*', 'users.name as user_name')
            ->seasonId($filterSeason)->orderBy('user_name', 'asc')->get();
        }
        //list of positions
        $positions = Player::select('position')->distinct()->where('players_db_id', '=', Season::find($filterSeason)->players_db_id)->orderBy('position', 'asc')->get();
        //list of nations
		$nations = Player::select('nation_name')->distinct()->where('players_db_id', '=', Season::find($filterSeason)->players_db_id)->orderBy('nation_name', 'asc')->get();
        //list of original_teams
		$original_teams = Player::select('team_name')->distinct()->where('players_db_id', '=', Season::find($filterSeason)->players_db_id)->orderBy('team_name', 'asc')->get();
		//list of original_league
		$original_leagues = Player::select('league_name')->distinct()->where('players_db_id', '=', Season::find($filterSeason)->players_db_id)->orderBy('league_name', 'asc')->get();

		//return view
        return view('market.search', compact('players', 'participants', 'positions', 'nations', 'original_teams', 'original_leagues', 'filterName', 'filterParticipant', 'filterPosition', 'filterNation', 'filterOriginalTeam', 'filterOriginalLeague', 'filterOverallRangeFrom', 'filterOverallRangeTo', 'filterTransferable', 'filterOnLoan', 'filterBuyNow', 'filterClauseRangeFrom', 'filterClauseRangeTo', 'filterAgeRangeFrom', 'filterAgeRangeTo', 'filterHeightRangeFrom', 'filterHeightRangeTo', 'filterHideFree', 'filterHideClausePaid', 'filterHideParticipantClauseLimit', 'filterShowClausesCanPay', 'order', 'pagination', 'page'));
    }

    public function teams()
    {
        $participants = $this->get_participants();
        return view('market.teams', compact('participants'));
    }

    public function team($slug)
    {
        $participants = $this->get_participants();
        $participant = $this->get_participant($slug);

		$players = SeasonPlayer::select('season_players.*')
	        ->join('players', 'players.id', '=', 'season_players.player_id')
	        ->seasonId(active_season()->id);
        $players = $players->participantId($participant->id);
        $players = $players->orderBy('players.overall_rating', 'desc')
	        ->orderBy('players.name', 'asc')
	        ->get();

        return view('market.team', compact('participants', 'participant', 'players'));
    }

    public function favorites()
    {
    	//data of user->participant
    	if (auth()->guest()) {
    		return redirect()->route('market')->with('info', 'La página ha expirado debido a la inactividad.');
    	} else {
			if (user_is_participant(auth()->user()->id)) {
				$participant_of_user = participant_of_user();

		    	//filter variables
		    	$order = request()->order;
		        if (!$order) {
		            $order = 'overall_desc';
		        }

		        $pagination = request()->pagination;
		        if (!$pagination == null) {
		            $perPage = $pagination;
		        } else {
		            $perPage = 12;
		        }

		        $page = request()->page;

		        $order_ext = $this->searchGetOrder($order);

		        $filterSeason = active_season()->id;

		        $filterName = null;
		        if (!is_null(request()->filterName)) {
		        	$filterName = request()->filterName;
		        }

		        $filterParticipant = request()->filterParticipant;
		        if ($filterParticipant == NULL) {
		        	$filterParticipant = -1;
		        }

		        $filterPosition = request()->filterPosition;

		        $filterNation = request()->filterNation;

		        $filterOriginalTeam = request()->filterOriginalTeam;

		        $filterOriginalLeague = request()->filterOriginalLeague;

				if (request()->filterHideFree == "on") {
					$filterHideFree = true;
				} else {
					$filterHideFree = false;
				}

				if (request()->filterHideClausePaid == "on") {
					$filterHideClausePaid = true;
				} else {
					$filterHideClausePaid = false;
				}

				if (request()->filterHideParticipantClauseLimit == "on") {
					$filterHideParticipantClauseLimit = true;
				} else {
					$filterHideParticipantClauseLimit = false;
				}

				if (!auth()->guest() && user_is_participant(auth()->user()->id)) {
					if (request()->filterShowClausesCanPay == "on") {
						$filterShowClausesCanPay = true;
					} else {
						$filterShowClausesCanPay = false;
					}
				} else {
					$filterShowClausesCanPay = false;
				}

		    	if (request()->overall_range) {
			    	$overall_rating = (explode( ';', request()->overall_range));
			    	$filterOverallRangeFrom = $overall_rating[0];
			    	$filterOverallRangeTo = $overall_rating[1];
		    	} else {
		    		$filterOverallRangeFrom = 70;
			    	$filterOverallRangeTo = 99;
		    	}

		    	if (request()->clause_range) {
			    	$clause_range = (explode( ';', request()->clause_range));
			    	$filterClauseRangeFrom = $clause_range[0];
			    	$filterClauseRangeTo = $clause_range[1];
		    	} else {
		    		$filterClauseRangeFrom = 0;
			    	$filterClauseRangeTo = 500;
		    	}

		    	if (request()->age_range) {
			    	$age_range = (explode( ';', request()->age_range));
			    	$filterAgeRangeFrom = $age_range[0];
			    	$filterAgeRangeTo = $age_range[1];
		    	} else {
		    		$filterAgeRangeFrom = 15;
			    	$filterAgeRangeTo = 45;
		    	}

		    	if (request()->height_range) {
			    	$height_range = (explode( ';', request()->height_range));
			    	$filterHeightRangeFrom = $height_range[0];
			    	$filterHeightRangeTo = $height_range[1];
		    	} else {
		    		$filterHeightRangeFrom = 150;
			    	$filterHeightRangeTo = 210;
		    	}

		    	//list of players
		        $players = FavoritePlayer::select('favorite_players.*', 'season_participants.clauses_received')
		        	->leftjoin('season_players', 'season_players.id', '=', 'favorite_players.player_id')
		        	->leftjoin('players', 'players.id', '=', 'season_players.player_id')
		        	->leftjoin('season_participants', 'season_participants.id', '=', 'season_players.participant_id');
		    	$players->where('favorite_players.participant_id', "=", $participant_of_user->id);
				$players->where('season_players.season_id', "=", $filterSeason);
		        $players->where('active', '=', 1);
		        if (!is_null($filterName)) {
		        	$players->where('players.name', "LIKE", "%$filterName%");
		        }
		        if ($filterParticipant >= 0) {
		            $players = $players->where('season_players.participant_id', '=', $filterParticipant);
		        }
		        if ($filterShowClausesCanPay) {
		        	$players = $players->where(function($q) use ($participant_of_user) {
		          		$q->where('season_players.participant_id', '!=', 0)
		            	  ->where('season_players.participant_id', '!=', $participant_of_user->id);
		      		});
		        	$players = $players->where('season_players.allow_clause_pay', '=', 1);
		        	$players = $players->where('season_participants.clauses_received', '<', active_season()->max_clauses_received);
		        	$players = $players->where(\DB::raw('season_players.price * 1.10'), '<', $participant_of_user->budget());
		        } else {
			        if ($filterHideFree) {
			        	$players = $players->where('season_players.participant_id', '!=', 0);
			        }
			        if ($filterHideClausePaid) {
			        	$players = $players->where('season_players.allow_clause_pay', '=', 1);
			        }
			        if ($filterHideParticipantClauseLimit) {
			        	$players = $players->where('season_participants.clauses_received', '<', active_season()->max_clauses_received);
			        }
		        }
		        if ($filterPosition != NULL) {
		            $players = $players->where('players.position', '=', $filterPosition);
		        }
		        if ($filterNation != NULL) {
		            $players = $players->where('players.nation_name', '=', $filterNation);
		        }
		        if ($filterOriginalTeam != NULL) {
		            $players = $players->where('players.team_name', '=', $filterOriginalTeam);
		        }
		        if ($filterOriginalLeague != NULL) {
		            $players = $players->where('players.league_name', '=', $filterOriginalLeague);
		        }
		        $players = $players->where('players.overall_rating', '>=', $filterOverallRangeFrom);
		        $players = $players->where('players.overall_rating', '<=', $filterOverallRangeTo);
		        $players = $players->where('season_players.price', '>=', $filterClauseRangeFrom);
		        $players = $players->where('season_players.price', '<=', $filterClauseRangeTo);
		        $players = $players->where('players.age', '>=', $filterAgeRangeFrom);
		        $players = $players->where('players.age', '<=', $filterAgeRangeTo);
		        $players = $players->where('players.height', '>=', $filterHeightRangeFrom);
		        $players = $players->where('players.height', '<=', $filterHeightRangeTo);
				$players = $players->orderBy($order_ext['sortField'], $order_ext['sortDirection']);
				if ($order_ext['sortField'] == 'players.overall_rating') {
					$players = $players->orderBy('players.name', 'asc');
				} else {
					$players = $players->orderBy('players.overall_rating', 'desc');
				}
		        $players = $players->paginate($perPage, ['*'], 'page', $page);

			    //list of participants
		        if (Season::find($filterSeason)->participant_has_team) {
		            $participants = SeasonParticipant::
		            leftJoin('teams', 'teams.id', '=', 'season_participants.team_id')
		            ->select('season_participants.*', 'teams.name as team_name')
		            ->seasonId($filterSeason)->orderBy('team_name', 'asc')->get();
		        } else {
		            $participants = SeasonParticipant::
		            leftJoin('users', 'users.id', '=', 'season_participants.user_id')
		            ->select('season_participants.*', 'users.name as user_name')
		            ->seasonId($filterSeason)->orderBy('user_name', 'asc')->get();
		        }
		        //list of positions
		        $positions = Player::select('position')->distinct()->where('players_db_id', '=', Season::find($filterSeason)->players_db_id)->orderBy('position', 'asc')->get();
		        //list of nations
				$nations = Player::select('nation_name')->distinct()->where('players_db_id', '=', Season::find($filterSeason)->players_db_id)->orderBy('nation_name', 'asc')->get();
		        //list of original_teams
				$original_teams = Player::select('team_name')->distinct()->where('players_db_id', '=', Season::find($filterSeason)->players_db_id)->orderBy('team_name', 'asc')->get();
				//list of original_league
				$original_leagues = Player::select('league_name')->distinct()->where('players_db_id', '=', Season::find($filterSeason)->players_db_id)->orderBy('league_name', 'asc')->get();

				//return view
		        return view('market.favorites', compact('players', 'participants', 'positions', 'nations', 'original_teams', 'original_leagues', 'filterName', 'filterParticipant', 'filterPosition', 'filterNation', 'filterOriginalTeam', 'filterOriginalLeague', 'filterOverallRangeFrom', 'filterOverallRangeTo', 'filterTransferable', 'filterOnLoan', 'filterBuyNow', 'filterClauseRangeFrom', 'filterClauseRangeTo', 'filterAgeRangeFrom', 'filterAgeRangeTo', 'filterHeightRangeFrom', 'filterHeightRangeTo', 'filterHideFree', 'filterHideClausePaid', 'filterHideParticipantClauseLimit', 'filterShowClausesCanPay', 'order', 'pagination', 'page'));
			}
		}

		return redirect()->route('market')->with('info', 'Debes ser participante para tener acceso.');
    }

    public function favoritesDestroy($id) {
    	$favorite = FavoritePlayer::find($id);
    	if ($favorite) {
    		$favorite->delete();
    	}
    	return back()->with('success', 'Jugador eliminado de la lista de favoritos correctamente.');
    }

    public function myTeam()
    {
    	if (auth()->guest()) {
    		return redirect()->route('market')->with('info', 'La página ha expirado debido a la inactividad.');
    	} else {
			if (user_is_participant(auth()->user()->id)) {
				$participant = SeasonParticipant::where('season_id', '=', active_season()->id)
					->where('user_id', '=', auth()->user()->id)->first();

				$players = SeasonPlayer::select('season_players.*')
			        ->join('players', 'players.id', '=', 'season_players.player_id')
			        ->seasonId(active_season()->id);
	            $players = $players->participantId($participant->id);
		        $players = $players->orderBy('players.overall_rating', 'desc')
			        ->orderBy('players.name', 'asc')
			        ->get();

				return view('market.my_team', compact('participant', 'players'));
			}
    	}

		return redirect()->route('market')->with('info', 'Debes ser participante para tener acceso.');
    }

    public function myTeamPlayerEdit($id)
    {
        $player = SeasonPlayer::find($id);
        if ($player) {
            return view('market.my_team.edit', compact('player'))->render();
        } else {
            return view('market.my_team.edit-empty')->render();
        }
    }

    public function myTeamPlayerUpdate($id)
    {
    	if (auth()->guest()) {
    		return redirect()->route('market')->with('info', 'La página ha expirado debido a la inactividad.');
    	} else {
	        $player = SeasonPlayer::find($id);
	        if ($player) {
    			if (auth()->user()->id == $player->participant->user_id) {
		        	$player->salary = request()->salary;
		        	$player->price = request()->price;

		        	if (request()->untransferable == 'on') {
		        		$player->untransferable	= 1;
		        		$player->player_on_loan = 0;
		        		$player->transferable = 0;
		        		$player->sale_price = null;
		        		$player->sale_auto_accept = 0;
		        	} else {
		        		$player->untransferable	= 0;
			        	$player->player_on_loan = request()->player_on_loan == 'on' ? 1 : 0;
			        	$player->transferable = request()->transferable == 'on' ? 1 : 0;
			        	$player->sale_price = request()->sale_price;
			        	$player->sale_auto_accept = request()->sale_auto_accept == 'on' ? 1 : 0;
		        	}
		        	$player->market_phrase = request()->market_phrase;
		        	$player->save();
		        	if ($player->save()) {
		        		$this->manage_player_showcase($player);
		            	return redirect()->route('market.my_team')->with('success', 'Jugador editado correctamente.');
		        	} else {
		        		return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
		        	}
    			} else {
    				return back()->with('error', 'Acción cancelada. Ya no eres propietario del jugador');
    			}
	        } else {
				return redirect()->route('market.my_team')->with('error', 'El jugador ya no existe en la base de datos.');
	        }
    	}
    }

    public function tagsTransferable($id)
    {
    	if (auth()->guest()) {
    		return back()->with('info', 'La página ha expirado debido a la inactividad.');
    	} else {
	        $player = SeasonPlayer::find($id);
	        if ($player) {
	        	if (auth()->user()->id == $player->participant->user_id) {
		        	$player->transferable = 1;
		        	$player->untransferable = 0;
		        	$player->save();
		        	if ($player->save()) {
		        		$this->manage_player_showcase($player);
		            	return redirect()->route('market.my_team')->with('success', $player->player->name . ' ha sido declarado transferible.');
		        	} else {
		        		return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
		        	}
	        	} else {
	        		return back()->with('error', 'Acción cancelada. Ya no eres propietario del jugador');
	        	}
	        }
	        return back();
    	}
    }

    public function tagsUntransferable($id)
    {
    	if (auth()->guest()) {
    		return back()->with('info', 'La página ha expirado debido a la inactividad.');
    	} else {
	        $player = SeasonPlayer::find($id);
	        if ($player) {
	        	if (auth()->user()->id == $player->participant->user_id) {
		        	$player->untransferable = 1;
		    		$player->player_on_loan = 0;
		    		$player->transferable = 0;
		    		$player->sale_price = null;
		    		$player->sale_auto_accept = 0;
		    		$player->market_phrase = null;
		        	$player->save();
		        	if ($player->save()) {
		        		$this->manage_player_showcase($player);
		            	return redirect()->route('market.my_team')->with('success', $player->player->name . ' ha sido declarado intransferible.');
		        	} else {
		        		return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
		        	}
	        	} else {
	        		return back()->with('error', 'Acción cancelada. Ya no eres propietario del jugador');
	        	}
	        }
	        return back();
    	}
    }

    public function tagsOnLoan($id)
    {
    	if (auth()->guest()) {
    		return back()->with('info', 'La página ha expirado debido a la inactividad.');
    	} else {
	        $player = SeasonPlayer::find($id);
	        if ($player) {
	        	if (auth()->user()->id == $player->participant->user_id) {
		        	$player->player_on_loan = 1;
		        	$player->untransferable = 0;
		        	$player->save();
		        	if ($player->save()) {
		        		$this->manage_player_showcase($player);
		            	return redirect()->route('market.my_team')->with('success', $player->player->name . ' ha sido declarado cedible.');
		        	} else {
		        		return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
		        	}
	        	} else {
	        		return back()->with('error', 'Acción cancelada. Ya no eres propietario del jugador');
	        	}
	        }
	        return back();
    	}
    }

    public function tagsDelete($id)
    {
    	if (auth()->guest()) {
    		return back()->with('info', 'La página ha expirado debido a la inactividad.');
    	} else {
	        $player = SeasonPlayer::find($id);
	        if ($player) {
	        	if (auth()->user()->id == $player->participant->user_id) {
		        	$player->untransferable = 0;
		    		$player->player_on_loan = 0;
		    		$player->transferable = 0;
		    		$player->sale_price = null;
		    		$player->sale_auto_accept = 0;
		    		$player->market_phrase = null;
		        	$player->save();
		        	if ($player->save()) {
		        		$this->manage_player_showcase($player);
		            	return redirect()->route('market.my_team')->with('success', 'Se han eliminado las etiquetas de ' . $player->player->name);
		        	} else {
		        		return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
		        	}
	        	} else {
	        		return back()->with('error', 'Acción cancelada. Ya no eres propietario del jugador');
	        	}
	        }
	        return back();
    	}
    }

    public function dismiss($id)
    {
    	if (auth()->guest()) {
    		return back()->with('info', 'La página ha expirado debido a la inactividad.');
    	} else {
	        $player = SeasonPlayer::find($id);
	        if ($player) {
	        	if (auth()->user()->id == $player->participant->user_id) {
	        		if ($player->participant->min_players_limit()) {
	        			return back()->with('error', 'No puedes despedir al jugador. Actualmente tienes el mínimo de jugadores en tu plantilla.');
	        		} else {
		        		$participant_from = $player->participant->id;
		        		$participant_to = 0;

			        	$this->add_cash_history(
			        		$player->participant_id,
			        		'Despido de ' . $player->player->name,
			        		$player->season->free_players_remuneration,
			        		'E'
			        	);

			        	$this->add_transfer(
			        		'dismiss',
			        		$player->id,
			        		$participant_from,
			        		$participant_to,
			        		$player->season->free_players_remuneration
			        	);
			        	$transfer = Transfer::orderBy('id', 'desc')->first();

						$this->generate_new(
							'transfer',
							$transfer->id,
							NULL
			        	);

			        	$player->participant_id = 0;
			        	$player->market_phrase = null;
			        	$player->untransferable = 0;
			        	$player->player_on_loan = 0;
			        	$player->transferable = 0;
			        	$player->sale_price = null;
			        	$player->sale_auto_accept = 0;
			        	$player->price = $player->season->free_players_salary * 10;
			        	$player->salary = $player->season->free_players_salary;
			        	$player->save();
			        	if ($player->save()) {
			        		$this->manage_player_showcase($player);
			            	return redirect()->route('market.my_team')->with('success', $player->player->name . ' ha sido despedido.');
			        	} else {
			        		return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
			        	}
	        		}
	        	} else {
	        		return back()->with('error', 'Acción cancelada. Ya no eres propietario del jugador');
	        	}
	        }
	        return back();
    	}
    }

    public function trades()
    {
    	if (auth()->guest()) {
    		return redirect()->route('market')->with('info', 'La página ha expirado debido a la inactividad.');
    	} else {
			if (user_is_participant(auth()->user()->id)) {
				$participant = SeasonParticipant::where('season_id', '=', active_season()->id)
					->where('user_id', '=', auth()->user()->id)->first();

				$offers_sent = Trade::where('season_id', '=', active_season()->id)
					->where('state', '=', 'pending')
					->where('participant1_id', '=', participant_of_user()->id)
					->orderBy('created_at', 'desc')
					->get();
				$offers_received = Trade::where('season_id', '=', active_season()->id)
					->where('state', '=', 'pending')
					->where('participant2_id', '=', participant_of_user()->id)
					->orderBy('created_at', 'desc')
					->get();

				return view('market.trades', compact('participant', 'offers_sent', 'offers_received'));
			}
    	}

		return redirect()->route('market')->with('info', 'Debes ser participante para tener acceso.');
    }

    public function tradesAdd($id)
    {
    	if (auth()->guest()) {
    		return redirect()->route('market')->with('info', 'La página ha expirado debido a la inactividad.');
    	} else {
    		if (user_is_participant(auth()->user()->id)) {
		    	$participant = SeasonParticipant::find($id);
		    	return view('market.trades_add', compact('participant'));
		    }
	    }

	    return redirect()->route('market')->with('info', 'Debes ser participante para tener acceso.');
    }

    public function tradesSave($id)
    {
    	if (auth()->guest()) {
    		return redirect()->route('market')->with('info', 'La página ha expirado debido a la inactividad.');
    	} else {
    		if (user_is_participant(auth()->user()->id)) {
		    	$participant = SeasonParticipant::find($id);
		    	if ($participant) {

		    		if (!request()->p1_players && !request()->p2_players) {
		    			return redirect()->route('market.trades')->with('error', 'No se permiten ofertas sin jugadores. La oferta no ha sido enviada');
		    		} else {
			    		$trade = new Trade;
			    		$trade->season_id = $participant->season_id;
			    		$trade->participant1_id = participant_of_user()->id;
			    		$trade->participant2_id = $participant->id;
			    		$trade->cash1 = request()->p1_cash;
			    		$trade->cash2 = request()->p2_cash;
			    		$trade->state = 'pending';
			    		$trade->read = 0;
			    		$trade->save();

			    		if (request()->p1_players) {
					    	for ($i=0; $i < count(request()->p1_players); $i++) {
					    		$trade_detail = new TradeDetail;
					    		$trade_detail->trade_id = $trade->id;
					    		$trade_detail->player1_id = request()->p1_players[$i];
					    		$trade_detail->save();
					    	}
			    		}
						if (request()->p2_players) {
					    	for ($i=0; $i < count(request()->p2_players); $i++) {
					    		$trade_detail = new TradeDetail;
					    		$trade_detail->trade_id = $trade->id;
					    		$trade_detail->player2_id = request()->p2_players[$i];
					    		$trade_detail->save();
					    	}
						}
				    	return redirect()->route('market.trades')->with('success', 'Oferta enviada correctamente.');
		    		}
		    	} else {
		    		return redirect()->route('market.trades')->with('error', 'El participante no existe.');
		    	}
		    }
	    }

	    return redirect()->route('market')->with('info', 'Debes ser participante para tener acceso.');
    }





    /*
     * HELPERS FUNCTIONS
     *
     */
	protected function add_cash_history($participant_id, $description, $amount, $movement) {
	    $cash = new Cash;
	    $cash->participant_id = $participant_id;
	    $cash->description = $description;
	    $cash->amount = $amount;
	    $cash->movement = $movement;
	    $cash->save();

	    if ($cash->save()) {
	    	$participant = SeasonParticipant::find($participant_id);
	    	if ($movement == 'E') {
	    		$action = 'ingresa';
	    	} else {
	    		$action = 'desembolsa';
	    	}
	    	$text = "\xF0\x9F\x92\xB2" . $participant->team->name . " (" . $participant->user->name . ") <b>" . $action . "</b> " . number_format($amount, 2, ",", ".") . " mill. por " . "'<i>" . $description . "'</i>\n" . "Presupuesto " . $participant->team->name . ": " . number_format($participant->budget(), 2, ",", ".") . " mill.";
			// Telegram::sendMessage([
			//     'chat_id' => '-1001241759649',
			//     'parse_mode' => 'HTML',
			//     'text' => $text
			// ]);
	    }

	}

	protected function add_transfer($type, $player_id, $participant_from, $participant_to, $price) {
	    $transfer = new Transfer;
	    $transfer->type = $type;
	    $transfer->player_id = $player_id;
	    $transfer->participant_from = $participant_from;
	    $transfer->participant_to = $participant_to;
	    $transfer->price = $price;
	    $transfer->save();

	    if ($transfer->save()) {
			switch ($type) {
				case 'free':
					$participant_to = SeasonParticipant::find($participant_to);
					$player = SeasonPlayer::find($player_id);
					$player_name = $player->player->name;
					$pTo_team_name = $participant_to->team->name;
					$pTo_user_name = $participant_to->user->name;
					$office_pTo_link = 'http://lpx.es/mercado/equipos/' . $participant_to->team->slug;
					$bottom_link = 'http://lpx.es/mercado';
					$title = "\xF0\x9F\x86\x93Agente libre fichado\xE2\x9D\x97";

					$text = "$title<a href='" . pesmaster_player_info_path($player->player->game_id) . "'>$player_name</a>\n\n";
					$text .= "    <b>\xF0\x9F\x91\x89 $pTo_team_name ($pTo_user_name) su nuevo destino tras desembolsar \xF0\x9F\x92\xB6 $price mill.</b>\n\n";
					$text .= "        " . $player->player->name . " (" . $player->player->position . " - Media " . $player->player->overall_rating . ")\n";
					$text .= "        " . $player->player->nation_name . ", " . $player->player->age . " años\n\n";
					$text .= "    Presupuesto $pTo_team_name: " . number_format($participant_to->budget(), 2, ",", ".") . " mill.\n\n";
					$text .= "\xF0\x9F\x8F\xA0 <a href='$office_pTo_link'>Despacho $pTo_team_name</a>\n\n";
					$text .= "\xF0\x9F\x92\xBC <a href='$bottom_link'>Sigue la evolución del mercado</a>\n\n";
					break;
				case 'clause':
					$participant_from = SeasonParticipant::find($participant_from);
					$participant_to = SeasonParticipant::find($participant_to);
					$player = SeasonPlayer::find($player_id);
					$player_name = $player->player->name;
					$pTo_team_name = $participant_to->team->name;
					$pTo_user_name = $participant_to->user->name;
					$pFrom_team_name = $participant_from->team->name;
					$pFrom_user_name = $participant_from->user->name;
					$pTo_budget = $participant_to->budget();
					$pFrom_budget = $participant_from->budget();
					$money = number_format($player->price * 1.10, 2, ",", ".") . " mill. (" . number_format($player->price, 2, ",", ".") . " + " . number_format($player->price * 0.10, 2, ",", ".") . ")";
					$office_pTo_link = 'http://lpx.es/mercado/equipos/' . $participant_to->team->slug;
					$office_pFrom_link = 'http://lpx.es/mercado/equipos/' . $participant_from->team->slug;
					$bottom_link = 'http://lpx.es/mercado';

					switch ($player->price) {
						case ($player->price <= 10):
							$title = "\xF0\x9F\x92\xA9Mierdi-clausulazo\xE2\x9D\x97";
							break;
						case (($player->price > 10) && ($player->price <= 30)):
							$title = "\xF0\x9F\x92\xB0Claúsula pagada\xE2\x9D\x97";
							break;
						case (($player->price > 30) && ($player->price <= 100)):
							$title = "\xF0\x9F\x92\xB0\xF0\x9F\x92\xB0Clausulazo\xE2\x9D\x97";
							break;
						case (($player->price > 100) && ($player->price <= 200)):
							$title = "\xF0\x9F\x98\xB1\xF0\x9F\x92\xB0\xF0\x9F\x92\xB0\xF0\x9F\x98\xB1Clausulazo\xE2\x9D\x97";
							break;
						case ($player->price > 200):
							$title = "\xF0\x9F\x94\x9D\xF0\x9F\x92\xB0\xF0\x9F\x92\xB0\xF0\x9F\x94\x9DClausulazo TOP\xE2\x9D\x97";
							break;
					}
					$text = "$title<a href='" . pesmaster_player_info_path($player->player->game_id) . "'>$player_name</a>\n\n";
					$text .= "    <b>\xF0\x9F\x91\x89 $pTo_team_name ($pTo_user_name)</b>\n";
					$text .= "    \xF0\x9F\x92\xB6 $money\n\n";
					$text .= "        " . $player->player->name . " (" . $player->player->position . " - Media " . $player->player->overall_rating . ")\n";
					$text .= "        " . $player->player->nation_name . ", " . $player->player->age . " años\n\n";
					$text .= "    \xF0\x9F\x91\x88 $pFrom_team_name ($pFrom_user_name)\n\n";
					$text .= "    Presupuesto $pTo_team_name: " . number_format($pTo_budget, 2, ",", ".") . " mill.\n";
					$text .= "    Presupuesto $pFrom_team_name: " . number_format($pFrom_budget, 2, ",", ".") . " mill.\n\n";
					$text .= "\xF0\x9F\x8F\xA0 <a href='$office_pTo_link'>Despacho $pTo_team_name</a>\n";
					$text .= "\xF0\x9F\x8F\xA0 <a href='$office_pFrom_link'>Despacho $pFrom_team_name</a>\n\n";
					$text .= "\xF0\x9F\x92\xBC <a href='$bottom_link'>Sigue la evolución del mercado</a>\n\n";
					break;
				case 'negotiation':
					// $participant_from = SeasonParticipant::find($participant_from);
					// $participant_to = SeasonParticipant::find($participant_to);
					// $player = SeasonPlayer::find($player_id);
					// $player_name = $player->player->name;
					// $pTo_team_name = $participant_to->team->name;
					// $pTo_user_name = $participant_to->user->name;
					// $office_pTo_link = 'http://lpx.es/mercado/equipos/' . $participant_to->team->slug;
					// $bottom_link = 'http://lpx.es/mercado';
					// $title = "\xF0\x9F\x98\x89Acuerdo entre \xE2\x9D\x97";

					// $text = "$title<a href='" . pesmaster_player_info_path($player->player->game_id) . "'>$player_name</a>\n\n";
					// $text .= "    <b>\xF0\x9F\x91\x89 $pTo_team_name ($pTo_user_name) su nuevo destino tras desembolsar \xF0\x9F\x92\xB6 $price mill.</b>\n\n";
					// $text .= "        " . $player->player->name . " (" . $player->player->position . " - Media " . $player->player->overall_rating . ")\n";
					// $text .= "        " . $player->player->nation_name . ", " . $player->player->age . " años\n\n";
					// $text .= "    Presupuesto $pTo_team_name: " . number_format($participant_to->budget(), 2, ",", ".") . " mill.\n\n";
					// $text .= "\xF0\x9F\x8F\xA0 <a href='$office_pTo_link'>Despacho $pTo_team_name</a>\n\n";
					// $text .= "\xF0\x9F\x92\xBC <a href='$bottom_link'>Sigue la evolución del mercado</a>\n\n";
					break;
				case 'cession':
					# code...
					break;
				case 'dismiss':
					$participant_from = SeasonParticipant::find($participant_from);
					$player = SeasonPlayer::find($player_id);
					$player_name = $player->player->name;
					$pFrom_team_name = $participant_from->team->name;
					$pFrom_user_name = $participant_from->user->name;
					$office_pFrom_link = 'http://lpx.es/mercado/equipos/' . $participant_from->team->slug;
					$bottom_link = 'http://lpx.es/mercado';
					$title = "\xF0\x9F\x9A\xAA\xF0\x9F\x91\x88 Jugador despedido\xE2\x9D\x97";

					$text = "$title<a href='" . pesmaster_player_info_path($player->player->game_id) . "'>$player_name</a>\n\n";
					$text .= "    <b>$pFrom_team_name ($pFrom_user_name) prescinde de sus servicios y recibe \xF0\x9F\x92\xB6 $price mill.</b>\n\n";
					$text .= "        " . $player->player->name . " (" . $player->player->position . " - Media " . $player->player->overall_rating . ")\n";
					$text .= "        " . $player->player->nation_name . ", " . $player->player->age . " años\n\n";
					$text .= "    Presupuesto $pFrom_team_name: " . number_format($participant_from->budget(), 2, ",", ".") . " mill.\n\n";
					$text .= "\xF0\x9F\x8F\xA0 <a href='$office_pFrom_link'>Despacho $pFrom_team_name</a>\n\n";
					$text .= "\xF0\x9F\x92\xBC <a href='$bottom_link'>Sigue la evolución del mercado</a>\n\n";
					break;
			}
			// Telegram::sendMessage([
			//     'chat_id' => '-1001241759649',
			//     'parse_mode' => 'HTML',
			//     'text' => $text
			// ]);
	    }
	}

	protected function generate_new($type, $transfer_id, $match_id) {

		if ($transfer_id) {
			$transfer = Transfer::find($transfer_id);
		    if ($transfer) {
				switch ($transfer->type) {
					case 'free':
						$category = "Fichajes - " . $transfer->participantTo->team->name;
						$title = $transfer->season_player->player->name . " firma por " . $transfer->participantTo->team->name;
						$description = "Se incorpora como agente libre para ponerse a las órdenes de " . $transfer->participantTo->user->name;
						$img = $transfer->season_player->player->getImgFormatted();
						break;
					case 'clause':
						$category = "Fichajes - " . $transfer->participantTo->team->name;
						$title = "Clausulazo de " . $transfer->participantTo->team->name . " por " . $transfer->season_player->player->name;
						$description = $transfer->participantTo->team->name . " deposita los " . $transfer->season_player->price . " mill. de su claúsula en las oficinas de " . $transfer->participantFrom->team->name . " para incorporar al jugador";
						$img = $transfer->season_player->player->getImgFormatted();
						break;
					case 'negotiation':
						$category = "Fichajes - " . $transfer->participantTo->team->name;
						$title = $transfer->season_player->player->name . " firma por " . $transfer->participantTo->team->name;
						$description = "Tras pagar los " . $transfer->season_player->sale_price . " millones por los que el " . $transfer->participantFrom->team->name . " lo puso en el mercado de venta directa";
						$img = $transfer->season_player->player->getImgFormatted();
						break;
					case 'cession':
						# code...
						break;
					case 'dismiss':
						$category = "Fichajes - " . $transfer->participantFrom->team->name;
						$title = $transfer->participantFrom->team->name . " despide a " . $transfer->season_player->player->name;
						$description = "El jugador se incorpora a la bolsa de agentes libres";
						$img = $transfer->season_player->player->getImgFormatted();
						break;
				}
		    }
		}

        $post = Post::create([
		    'type' => $type,
		    'transfer_id' => $transfer_id,
		    'match_id' => $match_id,
		    'category' => $category,
		    'title' => $title,
		    'description' => $description,
		    'img' => $img,
        ]);
	}


	protected function manage_player_showcase($player) {
		$player_showcase = $this->check_player_showcase($player->id);
		if ($player_showcase) {
			if (!$player->transferable && !$player->player_on_loan) {
				$player_showcase = Showcase::where('player_id', '=', $player->id)->first();
				$player_showcase->delete();
			}
		} else {
			if ($player->transferable || $player->player_on_loan) {
				$player_showcase = new Showcase;
				$player_showcase->player_id = $player->id;
				$player_showcase->save();
			}
		}
	}

	protected function check_player_showcase($id) {
		$showcase = Showcase::where('player_id', '=', $id)->first();
		if ($showcase) {
			return true;
		}
		return false;
	}

    protected function saleGetOrder($order) {
        $order_ext = [
            'date' => [
                'sortField'     => 'showcase.created_at',
                'sortDirection' => 'asc'
            ],
            'date_desc' => [
                'sortField'     => 'showcase.created_at',
                'sortDirection' => 'desc'
            ],
            'name' => [
                'sortField'     => 'players.name',
                'sortDirection' => 'asc'
            ],
            'name_desc' => [
                'sortField'     => 'players.name',
                'sortDirection' => 'desc'
            ],
            'overall' => [
                'sortField'     => 'players.overall_rating',
                'sortDirection' => 'asc'
            ],
            'overall_desc' => [
                'sortField'     => 'players.overall_rating',
                'sortDirection' => 'desc'
            ],
            'saleprice' => [
                'sortField'     => 'season_players.sale_price',
                'sortDirection' => 'asc'
            ],
            'saleprice_desc' => [
                'sortField'     => 'season_players.sale_price',
                'sortDirection' => 'desc'
            ]
        ];
        return $order_ext[$order];
    }

    protected function searchGetOrder($order) {
        $order_ext = [
            'name' => [
                'sortField'     => 'players.name',
                'sortDirection' => 'asc'
            ],
            'name_desc' => [
                'sortField'     => 'players.name',
                'sortDirection' => 'desc'
            ],
            'overall' => [
                'sortField'     => 'players.overall_rating',
                'sortDirection' => 'asc'
            ],
            'overall_desc' => [
                'sortField'     => 'players.overall_rating',
                'sortDirection' => 'desc'
            ],
            'clause' => [
                'sortField'     => 'season_players.price',
                'sortDirection' => 'asc'
            ],
            'clause_desc' => [
                'sortField'     => 'season_players.price',
                'sortDirection' => 'desc'
            ],
            'age' => [
                'sortField'     => 'players.age',
                'sortDirection' => 'asc'
            ],
            'age_desc' => [
                'sortField'     => 'players.age',
                'sortDirection' => 'desc'
            ],
            'height' => [
                'sortField'     => 'players.height',
                'sortDirection' => 'asc'
            ],
            'height_desc' => [
                'sortField'     => 'players.height',
                'sortDirection' => 'desc'
            ]
        ];
        return $order_ext[$order];
    }

    protected function get_participants()
    {
        return SeasonParticipant::
            leftJoin('teams', 'teams.id', '=', 'season_participants.team_id')
            ->leftJoin('users', 'users.id', '=', 'season_participants.user_id')
            ->select('season_participants.*', 'teams.name as team_name', 'users.name as user_name')
            ->seasonId(active_season()->id)->orderBy('teams.name', 'asc')->get();
    }

    protected function get_participant($slug)
    {
        return SeasonParticipant::
            leftJoin('teams', 'teams.id', '=', 'season_participants.team_id')
            ->leftJoin('users', 'users.id', '=', 'season_participants.user_id')
            ->select('season_participants.*', 'teams.name as team_name', 'users.name as user_name')
            ->seasonId(active_season()->id)
            ->where('teams.slug', '=', $slug)
            ->first();
    }
}
