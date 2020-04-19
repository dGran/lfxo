<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SeasonParticipant;
use App\SeasonParticipantCashHistory;
use App\User;
use App\Season;
use App\AdminFilter;

use App\Events\TableWasSaved;
use App\Events\TableWasDeleted;
use App\Events\TableWasUpdated;
use App\Events\TableWasImported;

class SeasonParticipantCashHistoryController extends Controller
{
    public function index()
    {
    	if (request()->filterParticipant == NULL) { request()->filterParticipant = 0; }

        $admin = AdminFilter::where('user_id', '=', \Auth::user()->id)->first();
        if ($admin) {
            if (request()->filterSeason == null) {
                if (active_season()) {
                    $filterSeason = active_season()->id;
                } else {
                    $seasons = Season::all();
                    if ($seasons->isNotEmpty()) {
                        $filterSeason = $seasons->last()->id;
                    } else {
                        $filterSeason = '';
                    }
                }
            } else {
                $filterSeason = request()->filterSeason;
            }
            $filterParticipant = request()->filterParticipant;
            $order = request()->order;
            $pagination = request()->pagination;
            $page = request()->page;
            if (!$page) {
                if ($admin->seasonCashHistory_page) {
                    $page = $admin->seasonCashHistory_page;
                }
            }

            if (!request()->filtering) { // filtering determine when you use the form and exclude the first access
                if ($admin->seasonCashHistory_filterSeason) {
                    if (Season::find($admin->seasonCashHistory_filterSeason)) {
                        $filterSeason = $admin->seasonCashHistory_filterSeason;
                    }
                }
                if ($admin->seasonPlayers_filterParticipant) {
                    $filterParticipant = $admin->seasonCashHistory_filterParticipant;
                }
                if ($admin->seasonCashHistory_order) {
                    $order = $admin->seasonCashHistory_order;
                }
                if ($admin->seasonCashHistory_pagination) {
                    $pagination = $admin->seasonCashHistory_pagination;
                }
            }
        } else {
            $admin = AdminFilter::create([
                'user_id' => \Auth::user()->id,
            ]);
            if (request()->filterSeason == null) {
                if (active_season()) {
                    $filterSeason = active_season()->id;
                } else {
                    $seasons = Season::all();
                    if ($seasons->isNotEmpty()) {
                        $filterSeason = $seasons->last()->id;
                    } else {
                        $filterSeason = '';
                    }
                }
            } else {
                $filterSeason = request()->filterSeason;
            }
            $filterParticipant = request()->filterParticipant;
            $order = request()->order;
            $pagination = request()->pagination;
            $page = request()->page;
        }


        $adminFilter = AdminFilter::find($admin->id);
        $adminFilter->seasonCashHistory_filterSeason = $filterSeason;
        $adminFilter->seasonCashHistory_filterParticipant = $filterParticipant;
        $adminFilter->seasonCashHistory_order = $order;
        $adminFilter->seasonCashHistory_pagination = $pagination;
        $adminFilter->seasonCashHistory_page = $page;
        if ($adminFilter->isDirty() && !$adminFilter->isDirty('seasonCashHistory_page')) {
            $page = 1;
            $adminFilter->seasonCashHistory_page = $page;
        }
        $adminFilter->save();

        $active_season = Season::find($filterSeason);

        if (!$pagination == null) {
            $perPage = $pagination;
        } else {
            $perPage = 12;
        }

        if (!$order) {
            $order = 'default';
        }
        $order_ext = $this->getOrder($order);

        if ($filterParticipant >= 0) {
            $cash_histories = SeasonParticipantCashHistory::participantId($filterParticipant)->orderBy($order_ext['sortField'], $order_ext['sortDirection'])->paginate($perPage, ['*'], 'page', $page);
        } else {
        	$cash_histories = SeasonParticipantCashHistory::orderBy($order_ext['sortField'], $order_ext['sortDirection'])->paginate($perPage, ['*'], 'page', $page);
        }

        $seasons = Season::orderBy('name', 'asc')->get();
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

        if ($seasons->count() == 0) {
            if ($page-1 > 0) {
                $page = $page-1;
            } else {
                $page = 1;
            }
	        if ($filterParticipant >= 0) {
	            $cash_histories = SeasonParticipantCashHistory::$cash_histories->participantId($filterParticipant)->orderBy($order_ext['sortField'], $order_ext['sortDirection'])->paginate($perPage, ['*'], 'page', $page);
	        } else {
	        	$cash_histories = SeasonParticipantCashHistory::orderBy($order_ext['sortField'], $order_ext['sortDirection'])->paginate($perPage, ['*'], 'page', $page);
	        }

            $adminFilter->seasonCashHistory_page = $page;
            $adminFilter->save();
        }

        return view('admin.cash_history.index', compact('cash_histories', 'seasons', 'participants', 'filterSeason', 'filterParticipant', 'active_season', 'order', 'pagination', 'page'));
    }

    public function add($season_id)
    {
        $season = Season::find($season_id);
        if ($season->participant_has_team) {
            $participants = SeasonParticipant::
            leftJoin('teams', 'teams.id', '=', 'season_participants.team_id')
            ->select('season_participants.*', 'teams.name as team_name')
            ->seasonId($season->id)->orderBy('team_name', 'asc')->get();
        } else {
            $participants = SeasonParticipant::
            leftJoin('users', 'users.id', '=', 'season_participants.user_id')
            ->select('season_participants.*', 'users.name as user_name')
            ->seasonId($season->id)->orderBy('user_name', 'asc')->get();
        }
    	return view('admin.cash_history.add', compact('season', 'participants'));
    }

    public function save()
    {
        $data = request()->all();
        if ($data['participant_id'] == 0) {
            $participants = SeasonParticipant::seasonId($data['season_id'])->get();
            foreach ($participants as $participant) {
                $data['participant_id'] = $participant->id;
                $cash = SeasonParticipantCashHistory::create($data);
                event(new TableWasSaved($cash, $cash->description));
            }
            if (request()->telegram) {
                if ($data['movement'] == 'E') {
                    $action = 'ingresan';
                } else {
                    $action = 'desembolsan';
                }
                $text = "\xF0\x9F\x92\xB2" . "Todos los equipos <b>" . $action . "</b> " . number_format($data['amount'], 2, ",", ".") . " mill.\n" . "     Concepto: <i>'" . $data['description'] . "'</i>\n\n";
                $this->telegram_notification_channel($text);
            }
            if (request()->no_close) {
                return back()->with('success', 'Nuevos registros registrados correctamente');
            }
            return redirect()->route('admin.season_cash_history')->with('success', 'Nuevos registros registrados correctamente');
        } else {
	        $cash = SeasonParticipantCashHistory::create($data);
            $participant = SeasonParticipant::find($data['participant_id']);

	        if ($cash->save()) {
	            event(new TableWasSaved($cash, $cash->description));
				if (request()->telegram) {
			    	if ($data['movement'] == 'E') {
			    		$action = 'ingresa';
			    	} else {
			    		$action = 'desembolsa';
			    	}
                    $text = "\xF0\x9F\x92\xB2" . $participant->team->name . " (" . $participant->user->name . ") <b>" . $action . "</b> " . number_format($data['amount'], 2, ",", ".") . " mill.\n" . "     Concepto: <i>'" . $data['description'] . "'</i>\n" . "     Presupuesto " . $participant->team->name . ": " . number_format($participant->budget(), 2, ",", ".") . " mill.\n\n";
                    $this->telegram_notification_channel($text);
				}
	            if (request()->no_close) {
	                return back()->with('success', 'Nuevo registro registrado correctamente');
	            }
	            return redirect()->route('admin.season_cash_history')->with('success', 'Nuevo registro registrado correctamente');
	        } else {
	            return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
	        }
        }
    }

    public function edit($id)
    {
        $cash = SeasonParticipantCashHistory::find($id);
        $season = $cash->participant->season;

        if ($season->participant_has_team) {
            $participants = SeasonParticipant::
            leftJoin('teams', 'teams.id', '=', 'season_participants.team_id')
            ->select('season_participants.*', 'teams.name as team_name')
            ->seasonId($season->id)->orderBy('team_name', 'asc')->get();
        } else {
            $participants = SeasonParticipant::
            leftJoin('users', 'users.id', '=', 'season_participants.user_id')
            ->select('season_participants.*', 'users.name as user_name')
            ->seasonId($season->id)->orderBy('user_name', 'asc')->get();
        }
        if ($cash) {
            return view('admin.cash_history.edit', compact('season', 'cash', 'participants'));
        } else {
            return back()->with('warning', 'Acción cancelada. El registro que querías editar ya no existe. Se ha actualizado la lista');
        }
    }

   public function update($id)
    {
        $cash = SeasonParticipantCashHistory::find($id);

        if ($cash) {
            $data = request()->all();

            $cash->fill($data);
            if ($cash->isDirty()) {
                $cash->update($data);
                if ($cash->update()) {
                    event(new TableWasUpdated($cash, $cash->description));
                    return redirect()->route('admin.season_cash_history')->with('success', 'Cambios guardados en el registro "' . $cash->description . '" correctamente.');
                } else {
                    return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
                }
            }

            return redirect()->route('admin.season_cash_history')->with('info', 'No se han detectado cambios en el registro "' . $cash->description . '".');

        } else {
            return redirect()->route('admin.season_cash_history')->with('warning', 'Acción cancelada. El registro que estabas editando ya no existe. Se ha actualizado la lista');
        }

    }

  public function destroy($id)
    {
        $cash = SeasonParticipantCashHistory::find($id);

        if ($cash) {
            $message = 'Se ha eliminado el registro "' . $cash->description . '" correctamente.';
            event(new TableWasDeleted($cash, $cash->description));
            $cash->delete();

            return redirect()->route('admin.season_cash_history')->with('success', 'Se ha eliminado el registro "' . $cash->description . '" correctamente.');
        } else {
            return back()->with('warning', 'Acción cancelada. El registro que querías eliminar ya no existe. Se ha actualizado la lista');
        }
    }

    public function destroyMany($ids)
    {
        $ids=explode(",",$ids);
        $counter_deleted = 0;

        for ($i=0; $i < count($ids); $i++)
        {
            $cash = SeasonParticipantCashHistory::find($ids[$i]);
            if ($cash) {
                $counter_deleted = $counter_deleted +1;
                event(new TableWasDeleted($cash, $cash->description));
                $cash->delete();
            }
        }
        if ($counter_deleted > 0) {
            return redirect()->route('admin.season_cash_history')->with('success', 'Se han eliminado los registros seleccionados correctamente.');
        } else {
            return back()->with('warning', 'Acción cancelada. Los registros que querías eliminar ya no existen.');
        }
    }

    public function exportFile($filename, $type, $filterParticipant, $order, $ids = null)
    {
        if (!$order) {
            $order = 'default';
        }
        $order_ext = $this->getOrder($order);

        if ($filterParticipant == "null") { $filterParticipant =""; }

        if ($ids) {
            $ids = explode(",",$ids);
            $cashs = SeasonParticipantCashHistory::whereIn('id', $ids)
                ->participantId($filterParticipant)
                ->orderBy($order_ext['sortField'], $order_ext['sortDirection'])
                ->get()->toArray();
        } else {
            $cashs = SeasonParticipantCashHistory::participantId($filterParticipant)
                ->orderBy($order_ext['sortField'], $order_ext['sortDirection'])
                ->get()->toArray();
        }

        if (!$filename) {
            $filename = 'historial_de_economia_export' . time();
        } else {
            $filename = str_slug($filename);
        }
        return \Excel::create($filename, function($excel) use ($cashs) {
            $excel->sheet('historial_de_economia', function($sheet) use ($cashs)
            {
                $sheet->fromArray($cashs);
            });
        })->download($type);
    }

   public function importFile(Request $request)
    {
        if ($request->hasFile('import_file')) {
            $path = $request->file('import_file')->getRealPath();
            $data = \Excel::load($path)->get();

            if ($data->count()) {
                foreach ($data as $key => $value) {
                    try {
                        $cash = new SeasonParticipantCashHistory;
                        $cash->participant_id = $value->participant_id;
                        $cash->description = $value->description;
                        $cash->amount = $value->amount;
                        $cash->movement = $value->movement;

                        if ($cash) {
                            $cash->save();
                            if ($cash->save()) {
                                event(new TableWasImported($cash, $cash->description));
                            }
                        }
                    }
                    catch (\Exception $e) {
                        return back()->with('error', 'Fallo al importar los datos, el archivo es inválido o no tiene el formato necesario.');
                    }
                }
                return back()->with('success', 'Datos importados correctamente.');
            } else {
                return back()->with('error', 'Fallo al importar los datos, el archivo no contiene datos.');
            }
        }
        return back()->with('error', 'No has cargado ningún archivo.');
    }

    public function pay_salaries($season_id)
    {
        $season = Season::find($season_id);
        if (!$season->salaries_paid) {
            $participants = SeasonParticipant::seasonId($season_id)->get();
            foreach ($participants as $participant) {
                $cash = new SeasonParticipantCashHistory;
                $cash->participant_id = $participant->id;
                $cash->description = "Pago de salarios '" . $season->name . "'";
                $cash->amount = $participant->salaries();
                $cash->movement = "S";
                $cash->save();
                event(new TableWasSaved($cash, $cash->description));
            }

            $text = "\xF0\x9F\x92\xB2" . "Todos los equipos han <b>pagado</b> los salarios de los jugadores.\n";
            $this->telegram_notification_channel($text);

            $season->salaries_paid = 1;
            $season->save();
            if ($season->save()) {
                event(new TableWasSaved($season, $season->name));
            }
            return back()->with('success', 'Pago de salarios realizado correctamente');

        } else {
            return back()->with('error', 'El pago de salarios de esta temporada ya se ha realizado');
        }
    }


    /*
     * HELPERS FUNCTIONS
     *
     */
    protected function getOrder($order) {
        $order_ext = [
            'default' => [
                'sortField'     => 'id',
                'sortDirection' => 'desc'
            ],
            'date' => [
                'sortField'     => 'created_at',
                'sortDirection' => 'asc'
            ],
            'date_desc' => [
                'sortField'     => 'created_at',
                'sortDirection' => 'desc'
            ]
        ];
        return $order_ext[$order];
    }
}
