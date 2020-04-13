<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Role;
use App\Profile;
use App\VerifyUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use App\Notifications\SendActivationEmail;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $role_user = Role::where('name', 'user')->first();
        $user->roles()->attach($role_user);

        $verifyUser = VerifyUser::create([
            'user_id' => $user->id,
            'token' => str_random(40)
        ]);

        $user->notify(new SendActivationEmail($verifyUser->token));

        return $user;
    }

    public function verifyUser($token)
    {
        $verifyUser = VerifyUser::where('token', $token)->first();

        if (isset($verifyUser)) {
            $user = $verifyUser->user;
            if (!$user->verified) {
                $verifyUser->user->verified = 1;
                $verifyUser->user->save();
                $status = "Tu cuenta está activada. Ahora puedes iniciar sesión.";
            } else {
                $status = "Tu cuenta ya está activada. Ahora puedes iniciar sesión.";
            }
            $profile = new Profile;
            $profile->user_id = $user->id;
            $avatar = rand(1, 40);
            $profile->avatar = 'img/avatars/gallery/' . $avatar . '.png';
            $profile->save();
        } else {
            return redirect('/login')->with('warning', "Lo siento, tu correo electrónico no puede ser identificado.");
        }

        $text = 'Nuevo usuario registrado con cuenta verificada - ' . $user->name . ' ('  . $user->email . ')';
        $this->telegram_notification_admin($text);

        return redirect('/login')->with('status', $status);
    }

    protected function registered(Request $request, $user)
    {
        $this->guard()->logout();
        return redirect('/login')->with('status', 'Revisa tu correo electrónico y haz clic en el enlace para activar tu cuenta.');
    }
}
