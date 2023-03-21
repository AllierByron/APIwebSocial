<?php

namespace App\Http\Controllers;

use App\Models\user_forum;
use App\Http\Requests\Storeuser_forumRequest;
use App\Http\Requests\Updateuser_forumRequest;

class UserForumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($user_id, $forum_id)
    {
        user_forum::create([
            'user_id'=> $user_id,
            'forum_id'=>$forum_id,
            'estado'=>'Activo'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Storeuser_forumRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Storeuser_forumRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\user_forum  $user_forum
     * @return \Illuminate\Http\Response
     */
    public function show(user_forum $user_forum)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\user_forum  $user_forum
     * @return \Illuminate\Http\Response
     */
    public function edit(user_forum $user_forum)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Updateuser_forumRequest  $request
     * @param  \App\Models\user_forum  $user_forum
     * @return \Illuminate\Http\Response
     */
    public function update(Updateuser_forumRequest $request, user_forum $user_forum)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\user_forum  $user_forum
     * @return \Illuminate\Http\Response
     */
    public function destroy(user_forum $user_forum)
    {
        //
    }
}
