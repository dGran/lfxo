<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Profile;
use App\Nation;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        $avatars = [];
        for ($i=1; $i < 40; $i++) {
            $avatars[$i] = 'img/avatars/gallery/' . $i . '.png';
        }
        $nations = Nation::orderBy('name', 'asc')->get();
		$profile = auth()->user()->profile()->firstOrNew([]);

        return view('users.profile.index', compact('profile', 'nations', 'avatars'));
    }

    public function update(Request $request, $id)
    {
        if (request()->user_name == null) {
            return back()->with('error', 'El nombre de usuario no puede ser nulo.');
        }
    	$profile = Profile::find($id);
    	if (!$profile) {
            $profile = new Profile;
            $profile->user_id = $id;
    	}

    	$profile->fill($request->all());
        $profile->email_notifications = $request->email_notifications ? true : false;

    	$profile->save();

        $profile->user->name = request()->user_name;
        $profile->user->save();

    	return back()->with('status', 'Perfil de usuario actualizado correctamente');
    }
}
