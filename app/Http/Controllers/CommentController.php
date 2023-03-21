<?php

namespace App\Http\Controllers;

use App\Models\comment;
use App\Http\Requests\StorecommentRequest;
use App\Http\Requests\UpdatecommentRequest;
use App\Models\publication;
use App\Models\User;
// use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Redis;
use stdClass;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($pub_id)
    {
        //
        $like = comment::select('like')
                        ->where('estado', 'Activo')
                        ->where('publication_id',$pub_id)
                        ->first();
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id, $pub_id, Request $request)
    {
        //
        switch ($id) {
            //caso no.1 es para crear likes, if para crearlo y else por si ya existe el registro y solo se necesita
            //cambiar el estado del like de false a true
            case 1:
                $comment = Comment::where('user_id', auth()->id())
                                    ->where('estado', 'Activo')
                                    ->where('publication_id',$pub_id)
                                    ->first();

                if(!$comment){
                    $like = comment::create([
                        'publication_id'=> $pub_id,
                        'user_id'=> auth()->id(),
                        'like'=>true,
                        'estado'=>'Activo'
                    ]);
                    $like = json_decode($like);
                    $ruta = asset('upComment/2/'.$like->id);
                    return $ruta;
                }else{
                   return json_encode(CommentController::update(1, json_decode($comment)));
                }
                break;
            //caso 2 es para crear comentarios
            case 2:
                $comment = Comment::where('user_id', auth()->id())
                                    ->where('estado', 'Activo')
                                    ->where('publication_id',$pub_id)
                                    ->first();
                if(!$comment){
                    $comm = comment::create([
                        'publication_id'=> $pub_id,
                        'user_id'=> auth()->id(),
                        'like'=>false,
                        'contenido'=> '@'.auth()->id().'-yml3xftx9(89)pop/v3'.$request->input('comment'),
                        'estado'=>'Activo',
                    ]);
                    return redirect()->route('pub',['id'=>$pub_id]);
                }else{
                    CommentController::update(3, json_decode($comment), $request);
                    return redirect()->route('pub',['id'=>$pub_id]);
                }
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorecommentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorecommentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show($id, $post_id)
    {
        //
        switch ($id) {
            //caso 1 para desplegar todos los comentarios de cierto post
            case 1:
                $comments = publication::find($post_id)->comments()
                                        ->select('contenido', 'user_id')
                                        ->where('estado', 'Activo')
                                        ->where('contenido', '!=',null)
                                        ->get();
                if(count($comments) != 0){
                    $comments = json_decode($comments);
                
                    foreach ($comments as $comm) {
                        $posicion = strpos($comm->contenido, "-yml3xftx9(89)pop/v3");
                        $key = substr($comm->contenido, 0, ($posicion+20));
                        $commentsArray = explode($key, $comm->contenido);
                        
                        $comm->comentarios = $commentsArray;

                        $user_name = User::select('name')->where('id', $comm->user_id)->get();
                        $user_name = json_decode($user_name);
                        $comm->nombre = $user_name[0]->name;
                    }
                }


                return $comments;
                break;
            
            default:
                # code...
                break;
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function edit(comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatecommentRequest  $request
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update($id, $com_id, Request $request = null)
    {
        //
        switch ($id) {
            //caso 1 para activar el like en de un registro. 
            //caso 2 para desactivar el like de un registro.
            case 1:
                $comment = comment::find($com_id->id);
                $comment->like = true;
                $comment->save();
                $ruta = comment::select('publication_id')->where('id',$com_id->id)->get();
                $ruta = json_decode($ruta);
                $ruta[0]->url = asset('upComment/2/'.$comment->id);
                // json_encode($ruta);
                return $ruta;

                break;
            case 2:
                $comment = comment::find($com_id);
                $comment->like = false;
                $comment->save();
                $ruta = comment::select('publication_id')->where('id',$com_id)->get();
                $ruta = json_decode($ruta);
                $ruta[0]->url = asset('crComment/1/'.$comment->publication_id);
                json_encode($ruta);
                return $ruta;
                // return "asset('crear/1/".$comment->publication_id."')";
                break;
            case 3:
                $comment = comment::find($com_id->id);
                $comment->contenido .= "@".auth()->id()."-yml3xftx9(89)pop/v3".$request->input('comment');
                $comment->save();
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(comment $comment)
    {
        //
    }
}
