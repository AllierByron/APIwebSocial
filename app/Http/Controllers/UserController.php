<?php

namespace App\Http\Controllers;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use League\CommonMark\Node\Block\Document;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $usuarios = User::all();
        return response()->json(['usuarios'=>$usuarios]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $id)
    {
        //
        switch ($id) {
            case 1:

                $handle = explode("@",$request->input('correo-InS'));
        
                $correo = User::where('email',$request->input('correo-InS'))->first();
                
                //doble verificacion, la comprobacion de la existencia de $correo sirve para evitar un error de SQL
                //que avanza el auto_increment de la tabla users pero no registra ningun nuevo registro/tupla
                //el metodo updateOrCreate simplemente verifica que no este creado el mismo usuario con la misma contraseña
                if(!$correo){
                    $user = User::updateOrCreate(
                        [
                            'name' => $handle[0],
                            'estado'=> "Activo",
                            'fecha_nac'=> "0001-01-01",
                            'bool_18' => false,
                            'email'=> $request->input('correo-InS'),
                            'foto_perfil'=> 'userIconpng.png',
                            'password' => Hash::make($request->input('password-InS'))
                        ]
                    );
                    
                    // Auth::login($user);
                    
                    //esta devolucion es importante porque es la forma en la que autenticamos al usuario, e inciamos su sesion
                    $imageURL = asset('img/'.$user->foto_perfil);
                    
                    $token = $user->createToken('auth_token')->plainTextToken; 
                    $user_token = $user->getAuthIdentifier();

                    return response()->json(['insertado'=>user::where('email', $request->input('correo-InS'))->get(), 
                                             'token'=>$token,
                                             'user_token'=>$user_token,
                                             'avatar'=>$imageURL]);
                }else{
                    return response()->json(['error'=>'Ya existe una cuenta con ese correo']);
                }
                break;
            case 2:
                $userSocialite = Socialite::driver('google')->user();

                $correo = User::where('email', $userSocialite->getEmail())->first();

                if(!$correo){
                    // dd($userSocialite);
                    $user = User::updateOrCreate(   
                        [
                            'name' => $userSocialite->getName(),
                            'estado'=> "Activo",
                            'fecha_nac'=> "0001-01-01",
                            'bool_18' => false,
                            'email'=> $userSocialite->getEmail(),
                            'foto_perfil'=> $userSocialite->getAvatar()
                        ]
                    );
                    
                    Auth::login($user);
                
                    // return redirect()->route('user');
                    return redirect()->route('obtainPosts',['id'=>3]);


                }else{
                    return UserController::show($request,2);
                    //return redirect()->route('user')->with('error', 'Ya existe una cuenta con ese correo');
                }
                
                break;
            default:
                return "error";
                break;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    //Añadido de FB
    public function store(Request $request,$id)
    {
        switch ($id) {
            case 'facebook':
                $userSocialite = Socialite::driver('facebook')->user();

                // dd($userSocialite);
                if(Auth::check()){
                    $user = User::find(auth()->user()->id);
                    $user->facebook = "https://www.facebook.com/search/people/?q=".$userSocialite->getName();
                    $user->save();
                    // return redirect()->route('user');
                    return redirect()->route('obtainPosts',['id'=>3]);
                }else{
                    return redirect()->route('user')->with('error','Usuario no encontrado');
                }
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        //
        switch ($id) {
            //caso 1 para usuarios que ingresaron manualmente su correo y contraseña
            case 1:

                $user = User::where('email', $request->input('correo-InS'))
                            // ->where('password',$request->input('password-InS'))
                            ->where('estado','Activo')->first();
                $imageURL = asset('img/'.$user->foto_perfil);
                // return response()->json(['test'=>$user]);
                $enter = false;
                if(Hash::check($request->input('password-InS'), $user->password)){
                    $enter = true;
                }else if($request->input('password-InS') == $user->password){
                    $enter = true;
                }
                if($enter){
                    // Auth::login($user);
                    $user_token = $user->getAuthIdentifier();
                    // return response()->json(['prueba'=>$user_token]);
                    // return redirect()->route('obtainPosts',['id'=>3]);
                    $publications = new PublicationController();
                    // return response()->json(['prueba'=>json_decode($user)]);
                    $pubs = $publications->show(3, 0, $user->id);
                    // return response()->json(['prueba'=>$pubs]);
                    
                    $token = $user->createToken('auth_token')->plainTextToken;
                    
                    return response()->json(['publicaciones'=> $pubs, 'user_token'=>$user_token, 'avatar'=>$imageURL, 'token'=>$token]);
                    // return response()->json(['publicaciones'=> 'nose']);

                }else{
                    // echo "no existe";
                    return response()->json(['error'=>'Usuario no encontrado']);
                    // return redirect()->route('home')->with('error','Usuario no encontrado');
                }

                break;
            //caso 2 para usuarios que crearon su cuenta mediante google, no hay contraseña registrada, solo correo
            case 2:
                $userSocialite = Socialite::driver('google')->user();

                $user = User::where('email', $userSocialite->getEmail())
                            ->where('estado','Activo')->first();

                if($user){
                    // echo "si existe, ".$user;
                    Auth::login($user);

                    // return redirect()->route('user');
                    return redirect()->route('obtainPosts',['id'=>3]);

                }else{
                    // echo "no existe";
                    echo '<script> document.getElementById("apartado-InS").InnerHTML = "Error";</script>';
                    return redirect()->route('user')->with('error','Usuario no encontrado');
                }

                break;
            default:
                # code...
                break;
        }
    }

    public function logout()
    {
        //
        Auth::logout();
        return redirect()->route('home');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, $user_id)
    {
        //
        switch($id){
            //actualizar aspectos del perfil
            case 1:
                // $foto_perfil = "";
                // dd($request);
                $origen = "";

                if($foto_perfil = $request->file('avatar')){
                    $destino = 'img/';
                    $origen = $foto_perfil->getClientOriginalName();
                    $foto_perfil->move($destino, $origen); 
                }else if($request->input('img-elegida') != ""){
                    $foto_perfil = $request->input('img-elegida');
                    $origen = $foto_perfil;
                }else{
                    $user = User::find($user_id);
                    $foto_perfil = $user->foto_perfil;
                    $origen = $foto_perfil;
                }
                
                
                User::where('id',$user_id)
                      ->update(array('name'=> $request->input('name'),
                                     'facebook'=> $request->input('urlFB'),
                                     'foto_perfil'=> $origen));
                // return redirect()->route('home');
                $userUpda = User::find($user_id);

                return response()->json(['updatedUser'=> $userUpda]);


            default:
                break;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $user_id)
    {
        switch($id){
            case 1:
                $user = User::find($user_id);

                $user->estado = 'Desactivado';

                return response()->json(['usuario'=>$user]);

                break;
        }
    }
}
