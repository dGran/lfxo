<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\SeasonCompetitionPhase;
use App\SeasonCompetitionPhaseGroup;

use App\Events\TableWasSaved;
use App\Events\TableWasDeleted;
use App\Events\TableWasUpdated;
use App\Events\TableWasImported;

class SeasonCompetitionPhaseGroupController extends Controller
{
    public function index($competition_slug, $phase_slug)
    {
        $phase = SeasonCompetitionPhase::where('slug','=', $phase_slug)->firstOrFail();
        $groups = SeasonCompetitionPhaseGroup::where('phase_id', '=', $phase->id)->orderBy('id', 'asc')->get();

        return view('admin.seasons_competitions_phases_groups.index', compact('groups', 'phase'));
    }

    public function add($competition_slug, $phase_slug)
    {
        $phase = SeasonCompetitionPhase::where('slug','=', $phase_slug)->firstOrFail();
        $max_participants = $phase->groups_available_participants();
        return view('admin.seasons_competitions_phases_groups.add', compact('phase', 'max_participants'));
    }

    public function save($competition_slug, $phase_slug)
    {
        $data = request()->validate([
            'name' => 'required',
        ],
        [
            'name.required' => 'El nombre del grupo es obligatorio',
        ]);

        $phase = SeasonCompetitionPhase::where('slug','=', $phase_slug)->firstOrFail();

        $data = request()->all();
        $data['phase_id'] = $phase->id;
        $data['slug'] = str_slug(request()->name);

        $group = SeasonCompetitionPhaseGroup::create($data);

        $slug_text = str_slug($group->name) . '_' . $group->id;
        $group->slug = $slug_text;
        $group->save();

        if ($group->save()) {
            event(new TableWasSaved($group, $group->name));
            if (request()->no_close) {
                return back()->with('success', 'Nuevo grupo registrado correctamente en la fase "' . $phase->name . '" de la competición "' . $phase->competition->name . '"');
            }
            return redirect()->route('admin.season_competitions_phases_groups', [$competition_slug, $phase_slug])->with('success', 'Nuevo grupo registrado correctamente en la fase "' . $phase->name . '" de la competición "' . $phase->competition->name . '"');
        } else {
            return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
        }
    }

    public function edit($competition_slug, $phase_slug, $id)
    {
        $group = SeasonCompetitionPhaseGroup::find($id);
        $phase = SeasonCompetitionPhase::where('slug','=', $phase_slug)->firstOrFail();
        $max_participants = $phase->groups_available_participants() + $group->num_participants;

        if ($group) {
            return view('admin.seasons_competitions_phases_groups.edit', compact('group', 'phase', 'max_participants'));
        } else {
            return back()->with('warning', 'Acción cancelada. El grupo que querías editar ya no existe. Se ha actualizado la lista');
        }
    }

    public function update($competition_slug, $phase_slug, $id)
    {
    	$phase = SeasonCompetitionPhase::where('slug','=', $phase_slug)->firstOrFail();
        $group = SeasonCompetitionPhaseGroup::find($id);

        if ($group) {
            $data = request()->validate([
                'name' => 'required',
            ],
            [
                'name.required' => 'El nombre del grupo es obligatorio',
            ]);

            $data = request()->all();
            $slug_text = str_slug(request()->name) . '_' . $group->id;
            $data['slug'] = $slug_text;

            $group->fill($data);
            if ($group->isDirty()) {
                $group->update($data);

                if ($group->update()) {
                    event(new TableWasUpdated($group, $group->name));
                    return redirect()->route('admin.season_competitions_phases_groups', [$competition_slug, $phase_slug])->with('success', 'Cambios guardados en el grupo "' . $group->name . '" correctamente.');
                } else {
                    return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
                }
            }
            return redirect()->route('admin.season_competitions_phases_groups', [$competition_slug, $phase_slug])->with('info', 'No se han detectado cambios en el grupo "' . $group->name . '".');

        } else {
            return redirect()->route('admin.season_competitions_phases_groups', [$competition_slug, $phase_slug])->with('warning', 'Acción cancelada. El grupo que estabas editando ya no existe. Se ha actualizado la lista');
        }
    }

    public function destroy($competition_slug, $phase_slug, $id)
    {
        $group = SeasonCompetitionPhaseGroup::find($id);

        if ($group) {
            $message = 'Se ha eliminado el grupo "' . $group->name . '" correctamente.';
            $this->destroy_group($group);
            return redirect()->route('admin.season_competitions_phases_groups', [$competition_slug, $phase_slug])->with('success', $message);
        } else {
            $message = 'Acción cancelada. El grupo que querías eliminar ya no existe. Se ha actualizado la lista';
            return back()->with('warning', $message);
        }
    }

    public function destroyMany($competition_slug, $phase_slug, $ids)
    {
        $ids=explode(",",$ids);
        $counter = 0;
        for ($i=0; $i < count($ids); $i++)
        {
            $group = SeasonCompetitionPhaseGroup::find($ids[$i]);
            if ($group) {
                $counter = $counter +1;
                $this->destroy_group($group);
            }
        }
        if ($counter > 0) {
            return redirect()->route('admin.season_competitions_phases_groups', [$competition_slug, $phase_slug])->with('success', 'Se han eliminado los grupos seleccionados correctamente.');
        } else {
            return back()->with('warning', 'Acción cancelada. El grupo que querías eliminar ya no existen.');
        }
    }

    public function exportFile($competition_slug, $phase_slug, $filename, $type, $ids = null)
    {
    	$phase = SeasonCompetitionPhase::where('slug','=', $phase_slug)->firstOrFail();
        if ($ids) {
            $ids = explode(",",$ids);
            $groups = SeasonCompetitionPhaseGroup::whereIn('id', $ids)
                ->where('phase_id', '=', $phase->id)
                ->orderBy('id', 'asc')
                ->get()->toArray();
        } else {
            $groups = SeasonCompetitionPhaseGroup::where('phase_id', '=', $phase->id)
                ->orderBy('id', 'asc')
                ->get()->toArray();
        }

        if (!$filename) {
            $filename = 'season_competitions_phases_groups_export' . time();
        } else {
            $filename = str_slug($filename);
        }
        return \Excel::create($filename, function($excel) use ($groups) {
            $excel->sheet('grupos', function($sheet) use ($groups)
            {
                $sheet->fromArray($groups);
            });
        })->download($type);
    }

    public function importFile($competition_slug, $phase_slug, Request $request)
    {
        if ($request->hasFile('import_file')) {
            $path = $request->file('import_file')->getRealPath();
            $data = \Excel::load($path)->get();

            if ($data->count()) {
                foreach ($data as $key => $value) {
                    try {
                        $group = new SeasonCompetitionPhaseGroup;
                        $group->phase_id = $value->phase_id;
                        $group->name = $value->name;
                        $group->num_participants = $value->num_participants;
                        $group->slug = str_slug($value->name);

                        if ($group) {
                            $group->save();
                            if ($group->save()) {
                                event(new TableWasImported($group, $group->name));
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

}
