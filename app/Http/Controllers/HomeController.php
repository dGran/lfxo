<?php

namespace App\Http\Controllers;

use Mail;
use App\Mail\Contact;
use Illuminate\Http\Request;
use App\User;
use App\Post;
use App\Testimony;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type = null)
    {
        $this->telegram_notification_admin('test');
        $posts = Post::orderBy('created_at', 'desc')->paginate();
        $results = Post::where('type', '=', 'result')->orderBy('created_at', 'desc')->take(10)->get();
        $last_users = User::has('profile')->where('verified', '=', 1)->orderBy('id', 'desc')->take(8)->get();
        $testimonies = Testimony::has('user_profile')->orderBy('created_at', 'desc')->take(5)->get();

        return view('home', compact('posts', 'results', 'last_users', 'testimonies', 'type'));
    }

    public function posts($type = null)
    {
        if ($type == null) {
            $posts = Post::orderBy('created_at', 'desc')->paginate();
        } else {
            $posts = Post::where('type', '=', $type)->orderBy('created_at', 'desc')->paginate();
        }
        $last_users = User::where('verified', '=', 1)->orderBy('id', 'desc')->take(8)->get();
        $results = Post::where('type', '=', 'result')->orderBy('created_at', 'desc')->take(10)->get();
        $testimonies = Testimony::has('user_profile')->orderBy('created_at', 'desc')->take(5)->get();

        return view('home', compact('posts', 'results', 'last_users', 'testimonies' ,'type'));
    }

    public function privacity()
    {
        return view('privacity');
    }

    public function contact()
    {
        return view('contact');
    }

    public function contactSent(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'mensaje' => 'required|string|min:20',
            'captcha' => 'required|captcha'
        ]);

        $forminput = [
            'nombre' => $request->input('nombre'),
            'email' => $request->input('email'),
            'mensaje' => $request->input('mensaje')
        ];
        Mail::to('lpx.torneos@gmail.com')->send(new Contact($forminput));
        $text = 'Nuevo mensaje recibido desde el formulario de contacto.';
        $this->telegram_notification_admin($text);

        return redirect('contacto')->with('success', '¡Mensaje enviado! Gracias por contactarnos.');
    }

    public function rules()
    {
        return view('rules.index');
    }
}
