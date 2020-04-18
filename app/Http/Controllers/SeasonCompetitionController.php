<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AdminFilter;
use App\Season;
use App\SeasonCompetition;

use App\Events\TableWasSaved;
use App\Events\TableWasDeleted;
use App\Events\TableWasUpdated;
use App\Events\TableWasImported;

class SeasonCompetitionController extends Controller
{
    public function index()
    {
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
            $order = request()->order;
            $pagination = request()->pagination;
            $page = request()->page;
            if (!$page) {
                if ($admin->seasonCompetitions_page) {
                    $page = $admin->seasonCompetitions_page;
                }
            }

            if (!request()->filtering) { // filtering determine when you use the form and exclude the first access
                if ($admin->seasonCompetitions_filterSeason) {
                    if (Season::find($admin->seasonCompetitions_filterSeason)) {
                        $filterSeason = $admin->seasonCompetitions_filterSeason;
                    }
                }
                if ($admin->seasonCompetitions_order) {
                    $order = $admin->seasonCompetitions_order;
                }
                if ($admin->seasonCompetitions_pagination) {
                    $pagination = $admin->seasonCompetitions_pagination;
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
            $order = request()->order;
            $pagination = request()->pagination;
            $page = request()->page;
        }


        $adminFilter = AdminFilter::find($admin->id);
        $adminFilter->seasonCompetitions_filterSeason = $filterSeason;
        $adminFilter->seasonCompetitions_order = $order;
        $adminFilter->seasonCompetitions_pagination = $pagination;
        $adminFilter->seasonCompetitions_page = $page;
        if ($adminFilter->isDirty() && !$adminFilter->isDirty('seasonCompetitions_page')) {
            $page = 1;
            $adminFilter->seasonCompetitions_page = $page;
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


        $competitions = SeasonCompetition::seasonId($filterSeason)->orderBy($order_ext['sortField'], $order_ext['sortDirection'])->paginate($perPage, ['*'], 'page', $page);
        $seasons = Season::orderBy('name', 'asc')->get();
        if ($seasons->count() == 0) {
            if ($page-1 > 0) {
                $page = $page-1;
            } else {
                $page = 1;
            }
            $competitions = SeasonCompetition::seasonId($filterSeason)->orderBy($order_ext['sortField'], $order_ext['sortDirection'])->paginate($perPage, ['*'], 'page', $page);
            $adminFilter->seasonCompetitions_page = $page;
            $adminFilter->save();
        }

        return view('admin.seasons_competitions.index', compact('competitions', 'seasons', 'filterSeason', 'active_season', 'order', 'pagination', 'page'));
    }

    public function add($season_id)
    {
        $season = Season::find($season_id);
        return view('admin.seasons_competitions.add', compact('season'));
    }

    public function save()
    {
        $data = request()->validate([
            'name' => 'required',
            'img' => [
                'image',
                'dimensions:max_width=256,max_height=256,ratio=1/1,min_width=48,min_height=48',
            ],
        ],
        [
            'name.required' => 'El nombre de la competición es obligatorio',
            'img.image' => 'El archivo debe contener una imagen',
            'img.dimensions' => 'Las dimensiones de la imagen no son válidas'
        ]);


        $data = request()->all();
        $data['season_id'] = request()->season_id;
        $data['slug'] = str_slug(request()->name);
        $data['stats_mvp'] = request()->stats_mvp ? 1 : 0;
        $data['stats_goals'] = request()->stats_goals ? 1 : 0;
        $data['stats_assists'] = request()->stats_assists ? 1 : 0;
        $data['stats_yellow_cards'] = request()->stats_yellow_cards ? 1 : 0;
        $data['stats_red_cards'] = request()->stats_red_cards ? 1 : 0;

        if (request()->url_img) {
            $data['img'] = request()->img_link;
        } else {
            if (request()->hasFile('img')) {
                $image = request()->file('img');
                $name = date('mdYHis') . uniqid() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/img/competitions');
                $imagePath = $destinationPath. "/".  $name;
                $image->move($destinationPath, $name);
                $data['img'] = 'img/competitions/' . $name;
            } else {
                $data['img'] = null;
            }
        }

        $competition = SeasonCompetition::create($data);

        $slug_text = str_slug($competition->name) . '_' . $competition->id;
        $competition->slug = $slug_text;
        $competition->save();

        if ($competition->save()) {
            event(new TableWasSaved($competition, $competition->name));
            if (request()->no_close) {
                return back()->with('success', 'Nueva competición registrada correctamente');
            }
            return redirect()->route('admin.season_competitions')->with('success', 'Nueva competición registrada correctamente');
        } else {
            return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
        }
    }

    public function edit($id)
    {
        $competition = SeasonCompetition::find($id);
        if ($competition) {
            return view('admin.seasons_competitions.edit', compact('competition'));
        } else {
            return back()->with('warning', 'Acción cancelada. La competición que querías editar ya no existe. Se ha actualizado la lista');
        }
    }

    public function update($id)
    {
        $competition = SeasonCompetition::find($id);

        if ($competition) {
            $data = request()->validate([
                'name' => 'required',
                'img' => [
                    'image',
                    'dimensions:max_width=256,max_height=256,ratio=1/1,min_width=48,min_height=48',
                ],
            ],
            [
                'name.required' => 'El nombre de la competición es obligatorio',
                'img.image' => 'El archivo debe contener una imagen',
                'img.dimensions' => 'Las dimensiones de la imagen no son válidas'
            ]);

            $data = request()->all();
            $slug_text = str_slug(request()->name) . '_' . $competition->id;
            $data['slug'] = $slug_text;
            $data['stats_mvp'] = request()->stats_mvp ? 1 : 0;
            $data['stats_goals'] = request()->stats_goals ? 1 : 0;
            $data['stats_assists'] = request()->stats_assists ? 1 : 0;
            $data['stats_yellow_cards'] = request()->stats_yellow_cards ? 1 : 0;
            $data['stats_red_cards'] = request()->stats_red_cards ? 1 : 0;

            if (request()->url_img) {
                $data['img'] = request()->img_link;
                if ($competition->img) {
                    if (\File::exists(public_path($competition->img))) {
                       \File::delete(public_path($competition->img));
                    }
                }
            } else {
                if (request()->hasFile('img')) {
                    $image = request()->file('img');
                    $name = date('mdYHis') . uniqid() . '.' . $image->getClientOriginalExtension();
                    $destinationPath = public_path('/img/competitions');
                    $imagePath = $destinationPath. "/".  $name;
                    if (\File::exists(public_path($competition->img))) {
                       \File::delete(public_path($competition->img));
                    }
                    $image->move($destinationPath, $name);
                    $data['img'] = 'img/competitions/' . $name;
                } else {
                    if (!request()->old_img) {
                        if ($competition->img) {
                            if (\File::exists(public_path($competition->img))) {
                               \File::delete(public_path($competition->img));
                            }
                            $data['img'] = '';
                        }
                    }
                }
            }

            $competition->fill($data);
            if ($competition->isDirty()) {
                $competition->update($data);

                if ($competition->update()) {
                    event(new TableWasUpdated($competition, $competition->name));
                    return redirect()->route('admin.season_competitions')->with('success', 'Cambios guardados en la competición "' . $competition->name . '" correctamente.');
                } else {
                    return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
                }
            }

            return redirect()->route('admin.season_competitions')->with('info', 'No se han detectado cambios en la competición "' . $competition->name . '".');

        } else {
            return redirect()->route('admin.season_competitions')->with('warning', 'Acción cancelada. La competición que estabas editando ya no existe. Se ha actualizado la lista');
        }
    }

    public function destroy($id)
    {
        $competition = SeasonCompetition::find($id);

        if ($competition) {
            $message = 'Se ha eliminado la competición "' . $competition->name . '" correctamente.';
            $this->destroy_competition($competition);
            return redirect()->route('admin.season_competitions')->with('success', $message);
        } else {
            $message = 'Acción cancelada. La competición que querías eliminar ya no existe. Se ha actualizado la lista';
            return back()->with('warning', $message);
        }
    }

    public function destroyMany($ids)
    {
        $ids=explode(",",$ids);
        $counter = 0;
        for ($i=0; $i < count($ids); $i++)
        {
            $competition = SeasonCompetition::find($ids[$i]);
            if ($competition) {
                $counter = $counter +1;
                $this->destroy_competition($competition);
            }
        }
        if ($counter > 0) {
            return redirect()->route('admin.season_competitions')->with('success', 'Se han eliminado las competiciones seleccionados correctamente.');
        } else {
            return back()->with('warning', 'Acción cancelada. Las competiciones que querías eliminar ya no existen.');
        }
    }

    public function duplicate($id)
    {
        $competition = SeasonCompetition::find($id);

        if ($competition) {
            $NewCompetition = $competition->replicate();
            $NewCompetition->name .= " (copia)";
            $NewCompetition->slug = str_slug($NewCompetition->name);

            if ($NewCompetition->img) {
                if ($NewCompetition->isLocalImg()) {
                    $extension = strstr($NewCompetition->img, '.');
                    $img = 'img/competitions/' . date('mdYHis') . uniqid() . $extension;
                    $sourceFilePath = public_path() . '/' . $NewCompetition->img;
                    $destinationPath = public_path() .'/' . $img;
                    if (\File::exists($sourceFilePath)) {
                        \File::copy($sourceFilePath,$destinationPath);
                    }

                    $NewCompetition->img = $img;
                }
            }

            $NewCompetition->save();

            if ($NewCompetition->save()) {
                event(new TableWasSaved($NewCompetition, $NewCompetition->name));
            }

            return redirect()->route('admin.season_competitions')->with('success', 'Se ha duplicado la competición "' . $NewCompetition->name . '" correctamente.');
        } else {
            return back()->with('warning', 'Acción cancelada. La competición que querías duplicar ya no existe. Se ha actualizado la lista.');
        }
    }

    public function duplicateMany($ids)
    {
        $ids=explode(",",$ids);
        $counter = 0;
        for ($i=0; $i < count($ids); $i++)
        {
            $original = SeasonCompetition::find($ids[$i]);
            if ($original) {
                $counter = $counter +1;
                $competition = $original->replicate();
                $competition->name .= " (copia)";
                $competition->slug = str_slug($competition->name);

                if ($competition->img) {
                    if ($competition->isLocalImg()) {
                        $extension = strstr($competition->img, '.');
                        $img = 'img/competitions/' . date('mdYHis') . uniqid() . $extension;
                        $sourceFilePath = public_path() . '/' . $competition->img;
                        $destinationPath = public_path() .'/' . $img;
                        if (\File::exists($sourceFilePath)) {
                            \File::copy($sourceFilePath,$destinationPath);
                        }

                        $competition->img = $img;
                    }
                }
                $competition->save();

                if ($competition->save()) {
                    event(new TableWasSaved($competition, $competition->name));
                }
            }
        }
        if ($counter > 0) {
            return redirect()->route('admin.season_competitions')->with('success', 'Se han duplicado las competiciones seleccionados correctamente.');
        } else {
            return back()->with('warning', 'Acción cancelada. Las competiciones que querías duplicar ya no existen. Se ha actualizado la lista.');
        }
    }

    public function exportFile($filename, $type, $filterSeason, $order, $ids = null)
    {
        if (!$order) {
            $order = 'default';
        }
        $order_ext = $this->getOrder($order);

        if ($filterSeason == "null") { $filterSeason =""; }

        if ($ids) {
            $ids = explode(",",$ids);
            $competitions = SeasonCompetition::whereIn('id', $ids)
                ->where('season_id', '=', $filterSeason)
                ->orderBy($order_ext['sortField'], $order_ext['sortDirection'])
                ->get()->toArray();
        } else {
            $competitions = SeasonCompetition::where('season_id', '=', $filterSeason)
                ->orderBy($order_ext['sortField'], $order_ext['sortDirection'])
                ->get()->toArray();
        }

        if (!$filename) {
            $filename = 'season_competitions_export' . time();
        } else {
            $filename = str_slug($filename);
        }
        return \Excel::create($filename, function($excel) use ($competitions) {
            $excel->sheet('competiciones', function($sheet) use ($competitions)
            {
                $sheet->fromArray($competitions);
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
                        $competition = new SeasonCompetition;
                        $competition->season_id = $value->season_id;
                        $competition->name = $value->name;
                        $competition->img = $value->img;
                        $competition->slug = str_slug($value->name);

                        if ($competition) {
                            $competition->save();
                            if ($competition->save()) {
                                event(new TableWasImported($competition, $competition->name));
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
    protected function getOrder($order) {
        $order_ext = [
            'default' => [
                'sortField'     => 'id',
                'sortDirection' => 'desc'
            ],
            'date' => [
                'sortField'     => 'id',
                'sortDirection' => 'asc'
            ],
            'date_desc' => [
                'sortField'     => 'id',
                'sortDirection' => 'desc'
            ],
            'name' => [
                'sortField'     => 'name',
                'sortDirection' => 'asc'
            ],
            'name_desc' => [
                'sortField'     => 'name',
                'sortDirection' => 'desc'
            ],
        ];
        return $order_ext[$order];
    }
}
