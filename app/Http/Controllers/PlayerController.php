<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Player;
use App\PlayerDB;
use App\AdminFilter;
use App\Team;
use App\TeamCategory;
use App\Nation;

use App\Events\TableWasSaved;
use App\Events\TableWasDeleted;
use App\Events\TableWasUpdated;
use App\Events\TableWasImported;

class PlayerController extends Controller
{
    public function index()
    {
        $admin = AdminFilter::where('user_id', '=', \Auth::user()->id)->first();
        if ($admin) {
            $filterName = request()->filterName;
            $filterPlayerDb = request()->filterPlayerDb;
            $filterTeam = request()->filterTeam;
            $filterNation = request()->filterNation;
            $filterPosition = request()->filterPosition;
            $order = request()->order;
            $pagination = request()->pagination;
            $page = request()->page;
            if (!$page) {
                if ($admin->player_page) {
                    $page = $admin->player_page;
                }
            }

            if (!request()->filtering) { // filtering determine when you use the form and exclude the first access
                if ($admin->player_filterName) {
                    $filterName = $admin->player_filterName;
                }
                if ($admin->player_filterPlayerDb) {
                    $filterPlayerDb = $admin->player_filterPlayerDb;
                }
                if ($admin->player_filterTeam) {
                    $filterTeam = $admin->player_filterTeam;
                }
                if ($admin->player_filterNation) {
                    $filterNation = $admin->player_filterNation;
                }
                if ($admin->player_filterPosition) {
                    $filterPosition = $admin->player_filterPosition;
                }
                if ($admin->player_order) {
                    $order = $admin->player_order;
                }
                if ($admin->player_pagination) {
                    $pagination = $admin->player_pagination;
                }
            }
        } else {
            $admin = AdminFilter::create([
                'user_id' => \Auth::user()->id,
            ]);
            $filterName = request()->filterName;
            $filterPlayerDb = request()->filterPlayerDb;
            $filterTeam = request()->filterTeam;
            $filterNation = request()->filterNation;
            $filterPosition = request()->filterPosition;
            $order = request()->order;
            $pagination = request()->pagination;
            $page = request()->page;
        }

        $adminFilter = AdminFilter::find($admin->id);
        $adminFilter->player_filterName = $filterName;
        $adminFilter->player_filterPlayerDb = $filterPlayerDb;
        $adminFilter->player_filterTeam = $filterTeam;
        $adminFilter->player_filterNation = $filterNation;
        $adminFilter->player_filterPosition = $filterPosition;
        $adminFilter->player_order = $order;
        $adminFilter->player_pagination = $pagination;
        $adminFilter->player_page = $page;
        if ($adminFilter->isDirty() && !$adminFilter->isDirty('player_page')) {
            $page = 1;
            $adminFilter->player_page = $page;
        }
        $adminFilter->save();

        if (!$pagination == null) {
            $perPage = $pagination;
        } else {
            $perPage = 12;
        }

        if (!$order) {
            $order = 'default';
        }
        $order_ext = $this->getOrder($order);

        $players = Player::playerDbId($filterPlayerDb)->name($filterName)->teamName($filterTeam)->nationName($filterNation)->position($filterPosition)->orderBy($order_ext['sortField'], $order_ext['sortDirection'])->paginate($perPage, ['*'], 'page', $page);
        // for cases after delete register and the page not exists
        if ($players->count() == 0) {
            if ($page-1 > 0) {
                $page = $page-1;
            } else {
                $page = 1;
            }
            $players = Player::playerDbId($filterPlayerDb)->name($filterName)->teamName($filterTeam)->nationName($filterNation)->position($filterPosition)->orderBy($order_ext['sortField'], $order_ext['sortDirection'])->paginate($perPage, ['*'], 'page', $page);
            $adminFilter->player_page = $page;
            $adminFilter->save();
        }

        $players_dbs = PlayerDB::orderBy('name', 'asc')->get();
    	return view('admin.players.index', compact('players', 'players_dbs', 'filterName', 'filterPlayerDb', 'filterTeam', 'filterNation', 'filterPosition', 'order', 'pagination', 'page'));
    }

    public function add()
    {
    	$players_dbs = PlayerDB::orderBy('name', 'asc')->get();
    	return view('admin.players.add', compact('players_dbs'));
    }

    public function save()
    {
        if (request()->new_parent) {
            $data = request()->validate([
                'name' => 'required',
                'img' => [
                    'image',
                    'dimensions:max_width=256,max_height=256,ratio=1/1,min_width=48,min_height=48',
                ],
                'players_db_name' => 'required|unique:players_dbs,name',
            ],
            [
                'name.required' => 'El nombre del jugador es obligatorio',
                'players_db_name.required' => 'El nombre de la database es obligatorio',
                'players_db_name.unique' => 'El nombre de la database ya existe',
                'img.image' => 'El archivo debe contener una imagen',
                'img.dimensions' => 'Las dimensiones de la imagen no son válidas'
            ]);

            $database = new PlayerDB;
            $database->name = request()->players_db_name;
            $database->slug = str_slug($database->name);
            $database->save();
            event(new TableWasSaved($database, $database->name));

            $players_db_id = $database->id;
        } else {
            $data = request()->validate([
                'name' => 'required',
                'players_db_id' => 'required',
                'img' => [
                    'image',
                    'dimensions:max_width=256,max_height=256,ratio=1/1,min_width=48,min_height=48',
                ],
            ],
            [
                'name.required' => 'El nombre del jugador es obligatorio',
                'players_db_id.required' => 'El player database es obligatorio',
                'img.image' => 'El archivo debe contener una imagen',
                'img.dimensions' => 'Las dimensiones de la imagen no son válidas'
            ]);

            $players_db_id = request()->players_db_id;
        }

        $data = request()->all();
        $data['players_db_id'] = $players_db_id;
        $data['slug'] = str_slug(request()->name);

        if (request()->url_img) {
            $data['img'] = request()->img_link;
        } else {
            if (request()->hasFile('img')) {
                $image = request()->file('img');
                $name = request()->player_db_id . '_' . date('mdYHis') . uniqid() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/img/players');
                $imagePath = $destinationPath. "/".  $name;
                $image->move($destinationPath, $name);
                $data['img'] = 'img/players/' . $name;
            } else {
                $data['img'] = null;
            }
        }

        $player = Player::create($data);

        if ($player->save()) {
            event(new TableWasSaved($player, $player->name));
            if (request()->no_close) {
                return back()->with('success', 'Nuevo jugador registrado correctamente');
            }
            return redirect()->route('admin.players')->with('success', 'Nuevo jugador registrado correctamente');
        } else {
            return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
        }
    }

    public function edit($id)
    {
        $player = Player::find($id);
        if ($player) {
            $players_dbs = PlayerDB::orderBy('name', 'asc')->get();
            return view('admin.players.edit', compact('player', 'players_dbs'));
        } else {
            return back()->with('warning', 'Acción cancelada. El jugador que querías editar ya no existe. Se ha actualizado la lista');
        }
    }

    public function update($id)
    {
        $player = Player::find($id);

        if ($player) {
            if (request()->new_parent) {
                $data = request()->validate([
                    'name' => 'required',
                    'img' => [
                        'image',
                        'dimensions:max_width=256,max_height=256,ratio=1/1,min_width=48,min_height=48',
                    ],
                    'players_db_name' => 'required|unique:players_dbs,name',
                ],
                [
                    'name.required' => 'El nombre del jugador es obligatorio',
                    'players_db_name.required' => 'El nombre de la database es obligatorio',
                    'players_db_name.unique' => 'El nombre de la database ya existe',
                    'img.image' => 'El archivo debe contener una imagen',
                    'img.dimensions' => 'Las dimensiones de la imagen no son válidas'
                ]);

                $database = new PlayerDB;
                $database->name = request()->players_db_name;
                $database->slug = str_slug($database->name);
                $database->save();
                event(new TableWasSaved($database, $database->name));

                $players_db_id = $database->id;
            } else {
                $data = request()->validate([
                    'name' => 'required',
                    'players_db_id' => 'required',
                    'img' => [
                        'image',
                        'dimensions:max_width=256,max_height=256,ratio=1/1,min_width=48,min_height=48',
                    ],
                ],
                [
                    'name.required' => 'El nombre del jugador es obligatorio',
                    'players_db_id.required' => 'El player database es obligatorio',
                    'img.image' => 'El archivo debe contener una imagen',
                    'img.dimensions' => 'Las dimensiones de la imagen no son válidas'
                ]);

                $players_db_id = request()->players_db_id;
            }

            $data = request()->all();
            $data['players_db_id'] = $players_db_id;
            $data['slug'] = str_slug(request()->name);

            if (request()->url_img) {
                $data['img'] = request()->img_link;
                if ($player->img) {
                    if (\File::exists(public_path($player->img))) {
                       \File::delete(public_path($player->img));
                    }
                }
            } else {
                if (request()->hasFile('img')) {
                    $image = request()->file('img');
                    $name = request()->players_db_id . '_' . date('mdYHis') . uniqid() . '.' . $image->getClientOriginalExtension();
                    $destinationPath = public_path('/img/players');
                    $imagePath = $destinationPath. "/".  $name;
                    if (\File::exists(public_path($player->img))) {
                       \File::delete(public_path($player->img));
                    }
                    $image->move($destinationPath, $name);
                    $data['img'] = 'img/players/' . $name;
                } else {
                    if (!request()->old_img) {
                        if ($player->img) {
                            if (\File::exists(public_path($player->img))) {
                               \File::delete(public_path($player->img));
                            }
                            $data['img'] = '';
                        }
                    }
                }
            }

            $player->fill($data);
            if ($player->isDirty()) {
                $player->update($data);

                if ($player->update()) {
                    event(new TableWasUpdated($player, $player->name));
                    return redirect()->route('admin.players')->with('success', 'Cambios guardados en jugador "' . $player->name . '" correctamente.');
                } else {
                    return back()->with('error', 'No se han guardado los datos, se ha producido un error en el servidor.');
                }
            }

            return redirect()->route('admin.players')->with('info', 'No se han detectado cambios en el jugador "' . $player->name . '".');

        } else {
            return redirect()->route('admin.players')->with('warning', 'Acción cancelada. El jugador que estabas editando ya no existe. Se ha actualizado la lista');
        }

    }

    public function destroy($id)
    {
        $player = Player::find($id);

        if ($player) {
            $message = 'Se ha eliminado el jugador "' . $player->name . '" correctamente.';

            if ($player->isLocalImg()) {
                if (\File::exists(public_path($player->img))) {
                    \File::delete(public_path($player->img));
                }
            }
            event(new TableWasDeleted($player, $player->name));
            $player->delete();

            return redirect()->route('admin.players')->with('success', $message);
        } else {
            $message = 'Acción cancelada. El jugador que querías eliminar ya no existe. Se ha actualizado la lista';

            return back()->with('warning', $message);
        }
    }

    public function destroyMany($ids)
    {
        $ids=explode(",",$ids);
        $counter = 0;
        for ($i=0; $i < count($ids); $i++)
        {
            $player = Player::find($ids[$i]);
            if ($player) {
                $counter = $counter +1;
                if ($player->isLocalImg()) {
                    if (\File::exists(public_path($player->img))) {
                        \File::delete(public_path($player->img));
                    }
                }
                event(new TableWasDeleted($player, $player->name));
                $player->delete();
            }
        }
        if ($counter > 0) {
            return redirect()->route('admin.players')->with('success', 'Se han eliminado los jugadores seleccionados correctamente.');
        } else {
            return back()->with('warning', 'Acción cancelada. Los jugadores que querías eliminar ya no existen.');
        }
    }

    public function view($id) {
        $player = Player::find($id);
        if ($player) {
            return view('admin.players.index.view', compact('player'))->render();
        } else {
            return view('admin.players.index.view-empty')->render();
        }
    }

    public function duplicate($id)
    {
        $player = Player::find($id);

    	if ($player) {
	    	$newPlayer = $player->replicate();
	    	$newPlayer->name .= " (copia)";
            $newPlayer->slug = str_slug($newPlayer->name);

            if ($newPlayer->img) {
                if ($newPlayer->isLocalImg()) {
                    $extension = strstr($newPlayer->img, '.');
                    $img = 'img/players/' . $newPlayer->players_db_id . '_' . date('mdYHis') . uniqid() . $extension;
                    $sourceFilePath = public_path() . '/' . $newPlayer->img;
                    $destinationPath = public_path() .'/' . $img;
                    if (\File::exists($sourceFilePath)) {
                        \File::copy($sourceFilePath,$destinationPath);
                    }

                    $newPlayer->img = $img;
                }
            }

	    	$newPlayer->save();

            if ($newPlayer->save()) {
                event(new TableWasSaved($newPlayer, $newPlayer->name));
            }

	    	return redirect()->route('admin.players')->with('success', 'Se ha duplicado el jugador "' . $newPlayer->name . '" correctamente.');
	    } else {
	    	return back()->with('warning', 'Acción cancelada. El jugador que querías duplicar ya no existe. Se ha actualizado la lista.');
	    }
    }

    public function duplicateMany($ids)
    {
    	$ids=explode(",",$ids);
    	$counter = 0;
    	for ($i=0; $i < count($ids); $i++)
    	{
	    	$original = Player::find($ids[$i]);
	    	if ($original) {
	    		$counter = $counter +1;
		    	$player = $original->replicate();
		    	$player->name .= " (copia)";
                $player->slug = str_slug($player->name);

                if ($player->img) {
                    if ($player->isLocalImg()) {
                        $extension = strstr($player->img, '.');
                        $img = 'img/players/' . $player->player_category_id . '_' . date('mdYHis') . uniqid() . $extension;
                        $sourceFilePath = public_path() . '/' . $player->img;
                        $destinationPath = public_path() .'/' . $img;
                        if (\File::exists($sourceFilePath)) {
                            \File::copy($sourceFilePath,$destinationPath);
                        }

                        $player->img = $img;
                    }
                }

		    	$player->save();

                if ($player->save()) {
                    event(new TableWasSaved($player, $player->name));
                }
		    }
    	}
    	if ($counter > 0) {
    		return redirect()->route('admin.players')->with('success', 'Se han duplicado los jugadores seleccionados correctamente.');
    	} else {
    		return back()->with('warning', 'Acción cancelada. Los jugadores que querías duplicar ya no existen. Se ha actualizado la lista.');
    	}
    }

    public function exportFile($filename, $type, $filterName, $filterPlayerDb, $filterTeam, $filterNation, $filterPosition, $order, $ids = null)
    {
        if (!$order) {
            $order = 'default';
        }
        $order_ext = $this->getOrder($order);

        if ($filterName == "null") { $filterName =""; }
        if ($filterPlayerDb == "null") { $filterPlayerDb =""; }
        if ($filterTeam == "null") { $filterTeam =""; }
        if ($filterNation == "null") { $filterNation =""; }
        if ($filterPosition == "null") { $filterPosition =""; }

        if ($ids) {
            $ids = explode(",",$ids);
            $players = Player::whereIn('id', $ids)
                ->playerDbId($filterPlayerDb)
                ->name($filterName)
                ->teamName($filterTeam)
                ->nationName($filterNation)
                ->position($filterPosition)
                ->orderBy($order_ext['sortField'], $order_ext['sortDirection'])
                ->get()->toArray();
        } else {
            $players = Player::playerDbId($filterPlayerDb)
                ->name($filterName)
                ->teamName($filterTeam)
                ->nationName($filterNation)
                ->position($filterPosition)
                ->orderBy($order_ext['sortField'], $order_ext['sortDirection'])
                ->get()->toArray();
        }

        if (!$filename) {
            $filename = 'jugadores_export' . time();
        } else {
            $filename = str_slug($filename);
        }
        return \Excel::create($filename, function($excel) use ($players) {
            $excel->sheet('jugadores', function($sheet) use ($players)
            {
                $sheet->fromArray($players);
            });
        })->download($type);
    }

    public function importData()
    {
        $players_dbs = PlayerDB::orderBy('name', 'asc')->get();
        return view('admin.players.import', compact('players_dbs'));
    }

    public function importDataSave(Request $request)
    {
        if ($request->hasFile('import_file')) {
            $path = $request->file('import_file')->getRealPath();
            $data = \Excel::load($path)->get();

            if (request()->new_parent) {
                $database = new PlayerDB;
                $database->name = request()->players_db_name;
                $database->slug = str_slug($database->name);
                $database->save();
                event(new TableWasSaved($database, $database->name));

                $players_db_id = $database->id;
            } else {
                $players_db_id = request()->players_db_id;
            }

            if ($data->count()) {
                foreach ($data as $key => $value) {
                    try {
                        // update nations table
                        if ($value->nation_name) {
                            $nation = Nation::where('name', '=', $value->nation_name)->first();
                            if (!$nation) {
                                $nation = Nation::create([
                                    'name' => $value->nation_name,
                                    'flag' => 'img/flags/' . str_slug($value->nation_name) . '.png'
                                ]);
                                event(new TableWasSaved($nation, $nation->name));
                            }
                        }
                        //////
                        if (request()->add_categories) {
                            if ($value->league_name) {
                                $category = TeamCategory::where('name', '=', $value->league_name)->first();
                                if (!$category) {
                                    $category = TeamCategory::create([
                                        'name' => $value->league_name,
                                        'slug' => str_slug($value->league_name)
                                    ]);
                                    event(new TableWasSaved($category, $category->name));
                                }
                            }
                        }
                        if (request()->add_teams) {
                            if ($value->team_name) {
                                $team = Team::where('name', '=', $value->team_name)->first();
                                if (!$team) {
                                    $team = Team::create([
                                        'team_category_id' => $category->id,
                                        'name' => $value->team_name,
                                        'logo' => '',
                                        'slug' => str_slug($value->team_name)
                                    ]);
                                    event(new TableWasSaved($team, $team->name));
                                }
                            }
                        }

                        $player = new Player;
                        $player->players_db_id = $players_db_id;
                        $player->game_id = $value->game_id;
                        $player->name = $value->name;
                        $player->img = $value->img;
                        $player->nation_name = $value->nation_name;
                        $player->league_name = $value->league_name;
                        $player->team_name = $value->team_name;
                        $player->position = $value->position;
                        $player->height = $value->height;
                        $player->age = $value->age;
                        $player->foot = $value->foot;
                        $player->overall_rating = $value->overall_rating;
                        $player->pack_id = $value->pack_id;
                        $player->slug = str_slug($value->name);

                        if ($player) {
                            $player->save();
                            if ($player->save()) {
                                event(new TableWasImported($player, $player->name));
                            }
                        }
                    }
                    catch (\Exception $e) {
                        dd($e);
                        return redirect()->route('admin.players')->with('error', 'Fallo al importar los datos, el archivo es inválido o no tiene el formato necesario.');
                    }
                }
                return redirect()->route('admin.players')->with('success', 'Datos importados correctamente.');
            } else {
                return redirect()->route('admin.players')->with('error', 'Fallo al importar los datos, el archivo no contiene datos.');
            }
        }
        return redirect()->route('admin.players')->with('error', 'No has cargado ningún archivo.');
    }

    public function linkWebImage($id, $www)
    {
        if ($www == 'pesdb' || $www == 'pesmaster') {
            $player = Player::find($id);
            if (!$player->isLocalImg() && $player->game_id) {
                if ($www == 'pesdb') {
                    $new_image = pesdb_player_img_path($player->game_id);
                } else {
                    $new_image = pesmaster_player_img_path($player->game_id);
                }
                $player->img = $new_image;
                $player->save();
                event(new TableWasUpdated($player, $player->name));
                return back()->with('success', 'Imágen enlazada correctamente del jugador "' . $player->name . '".');
            } else {
                return back()->with('error', 'No se ha enlazado la imágen del jugador, para enlazar es necesario rellenar el campo Game ID.');
            }
        } else {
            return back()->with('error', 'No se ha especificado el servidor de imágenes.');
        }
    }

    public function linkWebImageMany($ids, $www)
    {
        $ids=explode(",",$ids);
        $counter = 0;
        for ($i=0; $i < count($ids); $i++)
        {
            if ($www == 'pesdb' || $www == 'pesmaster') {
                $player = Player::find($ids[$i]);
                if (!$player->isLocalImg() && $player->game_id) {
                    if ($www == 'pesdb') {
                        $new_image = pesdb_player_img_path($player->game_id);
                    } else {
                        $new_image = pesmaster_player_img_path($player->game_id);
                    }
                    $player->img = $new_image;
                    $player->save();
                    event(new TableWasUpdated($player, $player->name));

                    $counter = $counter +1;
                }
            }
        }
        if ($counter > 0) {
            return redirect()->route('admin.players')->with('success', 'Se han enlazado las imágenes de los jugadores (con Game ID) seleccionados correctamente.');
        } else {
            return back()->with('warning', 'Acción cancelada. Los jugadores a los que querías enlazar las imágenes ya no existen. Se ha actualizado la lista.');
        }
    }

    public function unlinkWebImage($id)
    {
        $player = Player::find($id);
        if (!$player->isLocalImg()) {
            $player->img = '';
            $player->save();
            event(new TableWasUpdated($player, $player->name));
        }

        return back()->with('success', 'Imágen desenlazada correctamente del jugador "' . $player->name . '".');
    }

    public function unlinkWebImageMany($ids)
    {
        $ids=explode(",",$ids);
        $counter = 0;
        for ($i=0; $i < count($ids); $i++)
        {
            $player = Player::find($ids[$i]);
            if (!$player->isLocalImg()) {
                $player->img = '';
                $player->save();
                event(new TableWasUpdated($player, $player->name));

                $counter = $counter +1;
            }
        }
        if ($counter > 0) {
            return redirect()->route('admin.players')->with('success', 'Se han desenlazado las imágenes de los jugadores seleccionados correctamente.');
        } else {
            return back()->with('warning', 'Acción cancelada. Los jugadores a los que querías desenlazar las imágenes ya no existen. Se ha actualizado la lista.');
        }
    }

    public function linkWebImages()
    {
        $players = Player::where('game_id', '>', 0)->get();
        foreach ($players as $player) {

            if (!$player->isLocalImg() && $player->game_id) {

                $new_image = fifaindex_player_img_path($player->game_id);

                if (@GetImageSize($new_image)) {
                    \Image::make($new_image)->save('img/players/player_' . $player->id . '.png');
                    $player->img = 'img/players/player_' . $player->id . '.png';
                    $player->save();
                }

                event(new TableWasUpdated($player, $player->name));
            }
        }

        return back()->with('success', 'Imágenes enlazadas correctamente.');
    }

    public function unlinkWebImages()
    {
        $players = Player::where('game_id', '>', 0)->get();
        foreach ($players as $player) {
            if (!$player->isLocalImg()) {
                $player->img = '';
                $player->save();
                event(new TableWasUpdated($player, $player->name));
            }
        }

        return back()->with('success', 'Imágenes desenlazadas correctamente.');
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
            'overall' => [
                'sortField'     => 'overall_rating',
                'sortDirection' => 'asc'
            ],
            'overall_desc' => [
                'sortField'     => 'overall_rating',
                'sortDirection' => 'desc'
            ],
            'age' => [
                'sortField'     => 'age',
                'sortDirection' => 'asc'
            ],
            'age_desc' => [
                'sortField'     => 'age',
                'sortDirection' => 'desc'
            ],
            'height' => [
                'sortField'     => 'height',
                'sortDirection' => 'asc'
            ],
            'height_desc' => [
                'sortField'     => 'height',
                'sortDirection' => 'desc'
            ]
        ];
        return $order_ext[$order];
    }
}
