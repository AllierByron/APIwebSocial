<?php

namespace App\Http\Controllers;

use App\Models\publication;
use App\Models\comment;
use App\Http\Requests\StorepublicationRequest;
use App\Http\Requests\UpdatepublicationRequest;
use Illuminate\Support\Facades\Http;
use App\Models\forum;
use Laravel\Sail\Console\PublishCommand;
use Illuminate\Http\Request;


class PublicationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $post = publication::where('publications.id', $request->input(('id')))
                             ->where('publications.estado', 'Activo')
                             ->join('forums','forums.id','=','publications.forum_id')
                             ->join('users','users.id','=','publications.user_id')
                             ->select('publications.*','forums.nombre','users.name','users.foto_perfil')
                             ->get();
        
        $post = json_decode($post);

        $contenido = explode('/',$post[0]->contenido);
        // echo $contenido[0];
        if($contenido[0] != 'https:' && $contenido[0] != ""){
            // echo "Entre\n".$deta->id;
            $arrayTemp = [];
            $arrayContents = explode('fId'.$post[0]->forum_id.'pId'.$post[0]->id, $post[0]->contenido);
            for ($i = 0; $i < count($arrayContents)-1; $i++) {
                    $arrayTemp[$i] =  $arrayContents[$i];
            }
            $post[0]->contenido = $arrayTemp;
        }

        $comments = new CommentController();
        $commentss = $comments->show(1,$post[0]->id);

        // session(['post'=> $post, 'comments'=> $commentss]);
        // dd(session('post'));
        // dd(session('comments'));
        // return view('layouts/publication')->with('post', $post)->with('comments', $commentss);
        return response()->json(['post'=>$post, 'comments'=>$commentss]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $user_id)
    {
        // dd(count($request->file('content')));
        dd($request->file('content'));
        $pub = publication::create([
            'titulo'=>$request->input('postName'),
            'descripcion'=>$request->input('postDesc'),
            // 'contenido'=>$request->input('content'),
            'user_id'=>$user_id,
            'forum_id'=>$request->input('forumID'),
            'estado'=>'Activo'
        ]);

        $pubInsertContent = publication::find($pub->id);

        $destino = 'img/posts/';
        for ($i=0; $i < count($request->file('content')); $i++) { 
            $nombreFoto = $request->file('content')[$i]->getClientOriginalName();
            $request->file('content')[$i]->move($destino, $nombreFoto);
            $pubInsertContent->contenido .= $nombreFoto.'fId'.$request->input('forumID').'pId'.$pub->id;
            $pubInsertContent->save();
        }

        // dd($pub);

        // return redirect()->route('expl',['id'=>1]);
        // echo "forumID: ".$request->input('forumID');
        return response()->json(['publication'=>$pub]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorepublicationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorepublicationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\publication  $publication
     * @return \Illuminate\Http\Response
     */
    public static function show($id, $forum_id = 0,$user_id = 0)
    {
        switch ($id) {
            //el primer caso es solo para la api de memes
            case 1:
                $memes = Http::get('https://meme-api.com/gimme/5');
                $forumName = forum::select('nombre')->where('id',$forum_id)->first();
                $memesRea = json_decode($memes);
                session(['memes'=>$memesRea->memes,'forumName'=> $forumName->nombre]);
                
                return redirect()->route('forumMemes');

                break;
            case 2:

                $pubs = publication::where('forum_id', $forum_id)
                                     ->where('publications.estado', 'Activo')
                                     ->join('users','publications.user_id','=','users.id')
                                     ->select('publications.*','users.name','users.foto_perfil')
                                     ->get();
                $forum = forum::where('id',$forum_id)->where('estado','Activo')->first();

                // dd(json_decode($pubs));

                if(!$pubs){ 
                    return redirect()->route('forum')->with('noForums', 'no foros');
                }else{
                    //primero decodifico la info y la pongo en la variable de sesion
                    session(['data'=>json_decode($pubs),'forum'=> $forum]);
                    //ya que esta decodificada agrego la url al comentario
                    foreach (session('data') as $deta) {
                        $like = comment::where('user_id', auth()->id())
                                        ->where('estado', 'Activo')
                                        ->where('publication_id',$deta->id)
                                        ->where('like', true)
                                        ->first();

                        //seccion para decodificar las imgs que estan en los posts
                        $contenido = explode('/',$deta->contenido);
                        // echo $contenido[0];
                        if($contenido[0] != 'https:' && $contenido[0] != ""){
                            // echo "Entre\n".$deta->id;
                            $arrayTemp = [];
                            $arrayContents = explode('fId'.$deta->forum_id.'pId'.$deta->id, $deta->contenido);
                            for ($i = 0; $i < count($arrayContents)-1; $i++) {
                                    $arrayTemp[$i] =  $arrayContents[$i];
                            }
                            $deta->contenido = $arrayTemp;
                        }

                        if(!$like){
                            $deta->url_like = asset('/crComment/1/'.$deta->id);
                        }else{
                            $deta->url_like = asset('/upComment/2/'.$like->id);
                        }
                    }

                    // dd(session('data'));
                    // return redirect()->route('forum');
                    return view('layouts/comunidad');
                }

                break;
            case 3:
                $pubs = publication::where('publications.user_id',$user_id)
                                    ->where('publications.estado', 'Activo')
                                    ->join('forums','forums.id','=','publications.forum_id')
                                    ->join('users','users.id','=','publications.user_id')
                                    ->select('publications.*','forums.nombre','users.name','users.foto_perfil')
                                    ->get();

                if($pubs){
                    $datos = json_decode($pubs);
                    
                    foreach ($datos as $deta) {
                        $contenido = explode('/',$deta->contenido);
                        // echo $contenido[0];
                        if($contenido[0] != 'https:' && $contenido[0] != ""){
                            // echo "Entre\n".$deta->id;
                            $arrayTemp = [];
                            $arrayContents = explode('fId'.$deta->forum_id.'pId'.$deta->id, $deta->contenido);
                            for ($i = 0; $i < count($arrayContents)-1; $i++) {
                                 $arrayTemp[$i] =  $arrayContents[$i];
                            }
                            $deta->contenido = $arrayTemp;
                        }
                        $deta->url = asset('/forum/2/'.$deta->forum_id);
                    }
                    return $datos;
                    // dd(session('data'));
                }else{
                    return '';
                }   
                // dd(session('data'));

                break;
            default:
                # code...
                break;
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\publication  $publication
     * @return \Illuminate\Http\Response
     */
    public function edit(publication $publication)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatepublicationRequest  $request
     * @param  \App\Models\publication  $publication
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatepublicationRequest $request, publication $publication)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\publication  $publication
     * @return \Illuminate\Http\Response
     */
    public function destroy(publication $publication)
    {
        //
    }
}
