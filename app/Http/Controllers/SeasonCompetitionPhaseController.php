<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\SeasonCompetition;
use App\SeasonCompetitionPhase;

use App\Events\TableWasSaved;
use App\Events\TableWasDeleted;
use App\Events\TableWasUpdated;
use App\Events\TableWasImported;

class SeasonCompetitionPhaseController extends Controller
{
    public function index($slug)
    {
        $competition = SeasonCompetition::where('slug','=', $slug)->firstOrFail();
        $phases = SeasonCompetitionPhase::where('competition_id', '=', $competition->id)->orderBy('order', 'asc')->get();

        return view('admin.seasons_competitions_phases.index', compact('phases', 'competition'));
    }

    public function add($slug)
    {
        $competition = SeasonCompetition::where('slug','=', $slug)->firstOrFail();
        $max_participants = $this->calculateMaxParticipants($competition->id);
        return view('admin.seasons_competitions_phases.add', compact('competition', 'max_participants'));
    }

    public function save($slug)
    {
        $data = request()->validate([
            'name' => 'required',
        ],
        [
            'name.required' => 'El nombre de la fase es obligatorio',
        ]);

        $competition = SeasonCompetition::where('slug','=', $slug)->firstOrFail();

        $data = request()->all();
        $data['competition_id'] = $competition->id;
        $data['order'] = $this->calculateOrder($competition->id);
        $data['active'] = 0;
        $data['slug'] = str_slug(request()->name);

        $phase = SeasonCompetitionPhase::create($data);

        $slug_text = str_slug($phase->name) . '_' . $phase->id;
        $phase->slug = $slug_text;
        $phase->save();

        if ($phase->save()) {
            event(new TableWasSaved($phase, $phase->name));
            if (request()->no_close) {
                return back()->with('success', 'Nueva fase registrada correctamente en ' . $competition->name);
            }
            return redirect()->route('admin.season_competitions_phases', $slug)->with('success', 'Nueva fase registrada correctamente en ' . $competition->name);
        } else {
            return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
        }
    }

    public function edit($slug, $id)
    {
        $phase = SeasonCompetitionPhase::find($id);
        $competition = SeasonCompetition::where('slug','=', $slug)->firstOrFail();
        $max_participants = $this->calculateMaxParticipantsPhase($id, $competition->id);

        if ($phase) {
            return view('admin.seasons_competitions_phases.edit', compact('phase', 'competition', 'max_participants'));
        } else {
            return back()->with('warning', 'Acción cancelada. La fase que querías editar ya no existe. Se ha actualizado la lista');
        }
    }

    public function update($slug, $id)
    {
    	$competition = SeasonCompetition::where('slug','=', $slug)->firstOrFail();
        $phase = SeasonCompetitionPhase::find($id);

        if ($phase) {
            $data = request()->validate([
                'name' => 'required',
            ],
            [
                'name.required' => 'El nombre de la fase es obligatorio',
            ]);

            $data = request()->all();
            $data['order'] = $this->calculateOrder($competition->id);
            $slug_text = str_slug(request()->name) . '_' . $phase->id;
            $data['slug'] = $slug_text;

            $phase->fill($data);
            if ($phase->isDirty()) {
                $phase->update($data);

                if ($phase->update()) {
                    event(new TableWasUpdated($phase, $phase->name));
                    return redirect()->route('admin.season_competitions_phases', $competition->slug)->with('success', 'Cambios guardados en la fase "' . $phase->name . '" correctamente.');
                } else {
                    return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
                }
            }
            return redirect()->route('admin.season_competitions_phases', $competition->slug)->with('info', 'No se han detectado cambios en la fase "' . $phase->name . '".');

        } else {
            return redirect()->route('admin.season_competitions_phases', $competition->slug)->with('warning', 'Acción cancelada. La fase que estabas editando ya no existe. Se ha actualizado la lista');
        }
    }

    public function destroy($slug, $id)
    {
        $phase = SeasonCompetitionPhase::find($id);

        if ($phase) {
            $message = 'Se ha eliminado la fase "' . $phase->name . '" correctamente.';
            $this->destroy_phase($phase);
            return redirect()->route('admin.season_competitions_phases', $slug)->with('success', $message);
        } else {
            $message = 'Acción cancelada. La fase que querías eliminar ya no existe. Se ha actualizado la lista';
            return back()->with('warning', $message);
        }
    }

    public function destroyMany($slug, $ids)
    {
        $ids=explode(",",$ids);
        $counter = 0;
        for ($i=0; $i < count($ids); $i++)
        {
            $phase = SeasonCompetitionPhase::find($ids[$i]);
            if ($phase) {
                $counter += $counter +1;
                $this->destroy_phase($phase);
            }
        }
        if ($counter > 0) {
            return redirect()->route('admin.season_competitions_phases', $slug)->with('success', 'Se han eliminado las fases seleccionadas correctamente.');
        } else {
            return back()->with('warning', 'Acción cancelada. Las fases que querías eliminar ya no existen.');
        }
    }

    public function activate($slug, $id)
    {
        $phase = SeasonCompetitionPhase::find($id);
        if ($phase) {
            $phase->active = 1;
            $phase->save();
            if ($phase->save()) {
                event(new TableWasUpdated($phase, $phase->name, "Fase activada"));
            }
        }
        return redirect()->route('admin.season_competitions_phases', $slug)->with('success', 'Se ha activado la fase correctamente.');
    }

    public function activateMany($slug, $ids)
    {
        $ids=explode(",",$ids);
        $counter_activated = 0;
        for ($i=0; $i < count($ids); $i++)
        {
            $phase = SeasonCompetitionPhase::find($ids[$i]);
            if ($phase) {
                if ($phase->active == 0) {
                    $counter_activated = $counter_activated +1;
                    $phase->active = 1;
                    $phase->save();
                    event(new TableWasUpdated($phase, $phase->name, 'Fase activada'));
                }
            }
        }
        if ($counter_activated > 0) {
            return redirect()->route('admin.season_competitions_phases', $slug)->with('success', 'Se han activado las fases seleccionadas correctamente.');
        } else {
            return back()->with('warning', 'Acción cancelada. Todas las fases seleccionadas ya estaban activadas.');
        }
    }

    public function desactivate($slug, $id)
    {
        $phase = SeasonCompetitionPhase::find($id);
        if ($phase) {
            $phase->active = 0;
            $phase->save();
            if ($phase->save()) {
                event(new TableWasUpdated($phase, $phase->name, "Fase desactivada"));
            }
        }
        return redirect()->route('admin.season_competitions_phases', $slug)->with('success', 'Se ha desactivado la fase correctamente.');
    }

    public function desactivateMany($slug, $ids)
    {
        $ids=explode(",",$ids);
        $counter_desactivated = 0;
        for ($i=0; $i < count($ids); $i++)
        {
            $phase = SeasonCompetitionPhase::find($ids[$i]);
            if ($phase) {
                if ($phase->active == 1) {
                    $counter_desactivated = $counter_desactivated +1;
                    $phase->active = 0;
                    $phase->save();
                    event(new TableWasUpdated($phase, $phase->name, 'Fase desactivada'));
                }
            }
        }
        if ($counter_desactivated > 0) {
            return redirect()->route('admin.season_competitions_phases', $slug)->with('success', 'Se han desactivado las fases seleccionadas correctamente.');
        } else {
            return back()->with('warning', 'Acción cancelada. Todas las fases seleccionadas ya estaban desactivadas.');
        }
    }

    public function exportFile($slug, $filename, $type, $ids = null)
    {
    	$competition = SeasonCompetition::where('slug','=', $slug)->firstOrFail();
        if ($ids) {
            $ids = explode(",",$ids);
            $phases = SeasonCompetitionPhase::whereIn('id', $ids)
                ->where('competition_id', '=', $competition->id)
                ->orderBy('order', 'asc')
                ->get()->toArray();
        } else {
            $phases = SeasonCompetitionPhase::where('competition_id', '=', $competition->id)
                ->orderBy('order', 'asc')
                ->get()->toArray();
        }

        if (!$filename) {
            $filename = 'season_competitions_phases_export' . time();
        } else {
            $filename = str_slug($filename);
        }
        return \Excel::create($filename, function($excel) use ($phases) {
            $excel->sheet('Fases', function($sheet) use ($phases)
            {
                $sheet->fromArray($phases);
            });
        })->download($type);
    }

    public function importFile($slug, Request $request)
    {
        if ($request->hasFile('import_file')) {
            $path = $request->file('import_file')->getRealPath();
            $data = \Excel::load($path)->get();

            if ($data->count()) {
                foreach ($data as $key => $value) {
                    try {
                        $phase = new SeasonCompetitionPhase;
                        $phase->competition_id = $value->competition_id;
                        $phase->name = $value->name;
                        $phase->mode = $value->mode;
                        $phase->num_participants = $value->num_participants;
                        $phase->order = $value->order;
                        $phase->active = is_null($value->active) ? 0 : $value->active;
                        $phase->slug = str_slug($value->name);

                        if ($phase) {
                            $phase->save();
                            if ($phase->save()) {
                                event(new TableWasImported($phase, $phase->name));
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

    /*
     * HELPERS FUNCTIONS
     *
     */

    protected function calculateMaxParticipants($competition_id) {
        $phases = SeasonCompetitionPhase::where('competition_id', '=', $competition_id)->orderBy('order', 'desc')->get();
        if ($phases->count() > 0) {
        	return $phases->first()->num_participants;
        } else {
			$competition = SeasonCompetition::find($competition_id);
        	return $competition->season->num_participants;
        }
    }

    protected function calculateMaxParticipantsPhase($phase_id, $competition_id) {
        $phase = SeasonCompetitionPhase::find($phase_id);
        if ($phase->order == 1) {
            return $phase->competition->season->num_participants;
        } else {
            $previous_phase = SeasonCompetitionPhase::where('competition_id', '=', $competition_id)->where('order', '=', $phase->order-1)->get()->first()->num_participants;
            if ($previous_phase) {
                return $previous_phase;
            }
        }
    }


    protected function calculateOrder($competition_id) {
        $phases = SeasonCompetitionPhase::where('competition_id', '=', $competition_id)->orderBy('order', 'desc')->get();
        if ($phases->count() > 0) {
	        $last_phase = $phases->first()->order;
	        $total_phases = $phases->count();
        	if ($last_phase != $total_phases) {
        		$this->reorderPhases($competition_id);
        		$last_phase = SeasonCompetitionPhase::where('competition_id', '=', $competition_id)->orderBy('order', 'desc')->get()->first()->order;
        	}
        	return $last_phase + 1;
        } else {
        	return 1;
        }
    }

    protected function reorderPhases($competition_id) {
		$phases = SeasonCompetitionPhase::where('competition_id', '=', $competition_id)->orderBy('order', 'asc')->get();
		foreach ($phases as $key => $phase) {
			$phase->order = $key + 1;
			$phase->save();
		}

    }

}