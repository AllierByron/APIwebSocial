<?php

namespace App\Http\Controllers;

use App\Models\forum;
use App\Http\Requests\StoreforumRequest;
use App\Http\Requests\UpdateforumRequest;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->input('id')) {
            case 1:
                $forums = forum::select('id','nombre','descripcion')->where('estado','Activo')->get();
                // dd($forums);
                // return view('Exteriores/explorar')->with('foros', $forums);
                return response()->json(['foros'=>$forums]);
                break;
            case 2:
                $forums = forum::select('id','nombre','descripcion')->where('user_id',auth()->id())->where('estado','Activo')->get();
                // dd($forums);
                $temp = json_decode($forums);
                foreach ($temp as $t) {
                    $opc = 0;
                    if($t->nombre == "Memes"){
                        $opc = 1;
                    }else{
                        $opc = 2;
                    }

                    $t->url = asset('/forum/'.$opc.'/'.$t->id);
                }

                $forums = json_encode($temp);

                // return $forums;
                return response()->json(['foros'=>$forums]);

                break;
            default:
                return "";
                # code...
                break;
        }

        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $foro = forum::where('nombre', $request->input('forumName'))->first();

        if(!$foro){
            forum::create([
                'nombre'=> $request->input('forumName'),
                'descripcion'=>$request->input('descripcion'),
                'estado'=>'Activo',
                'user_id'=>auth()->id()
            ]);

            $foro_id = forum::select('id')->where('nombre', $request->input('forumName'))->first();

            $AdminController = new AdminController();
            $UserForumController = new UserForumController();
            
            $AdminController->create(auth()->id(),$foro_id->id);
            $UserForumController->create(auth()->id(),$foro_id->id);

            return redirect()->route('crForo')->with('msj1','Comunidad creada correctamente');
        }else{
            return redirect()->route('crForo')->with('msj2','Comunidad existente');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreforumRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreforumRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\forum  $forum
     * @return \Illuminate\Http\Response
     */
    public static function show(/*forum $forum*/)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\forum  $forum
     * @return \Illuminate\Http\Response
     */
    public function edit(forum $forum)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateforumRequest  $request
     * @param  \App\Models\forum  $forum
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateforumRequest $request, forum $forum)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\forum  $forum
     * @return \Illuminate\Http\Response
     */
    public function destroy(forum $forum)
    {
        //
    }
}
