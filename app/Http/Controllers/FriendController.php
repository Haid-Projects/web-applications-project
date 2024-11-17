<?php

namespace App\Http\Controllers;

use App\Aspects\Logger;
use App\Models\Friend;
use App\Models\Group_User;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
#[Logger]
class FriendController extends Controller
{
    use GeneralTrait;
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    public function relation($user_id,$group_id)
    {
      $users=Group_User::query()->where('group_id','=',$group_id)->pluck('user_id');
      foreach ($users as $user){
         if($user_id!=$user) {
             Friend::query()->create([
                 'user_id' => $user_id,
                 'friend_id' => $user
             ]);
             Friend::query()->create([
                 'user_id' => $user,
                 'friend_id' => $user_id
             ]);
         }
      }
      return null;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Friend  $friend
     * @return \Illuminate\Http\JsonResponse
     */
    public function show_friend(Request $request)
    {
        $id=Auth::guard('user')->user()->id;
        $user=User::query()->where("id","=",$id)->get()->first();

        return $this->returnSuccessData($user->friends()->get(), "Success", 200);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Friend  $friend
     * @return \Illuminate\Http\Response
     */
    public function edit(Friend $friend)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Friend  $friend
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Friend $friend)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Friend  $friend
     * @return \Illuminate\Http\Response
     */
    public function destroy(Friend $friend)
    {
        //
    }
}
