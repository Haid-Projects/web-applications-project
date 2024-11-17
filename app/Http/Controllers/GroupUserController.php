<?php

namespace App\Http\Controllers;

use App\Aspects\Logger;
use App\Models\File;
use App\Models\File_User;
use App\Models\Group;
use App\Models\Group_User;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

#[Logger]
class GroupUserController extends Controller
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



    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Group_User  $group_User
     * @return \Illuminate\Http\JsonResponse
     */
    public function show_group_users(Request $request)
    {
        $group=Group::query()->where("id","=",$request->group_id)->get()->first();
        $users=$group->group_users()->get();
        if(isset($group)) {
            return $this->returnSuccessData($users, "Success", 200);
        }
        return $this->returnError("Error not found",404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Group_User  $group_User
     * @return \Illuminate\Http\Response
     */
    public function edit(Group_User $group_User)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group_User  $group_User
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Group_User $group_User)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group_User  $group_User
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_group_member(Request $request)
      {
          $group = Group::find($request->group_id);
          $user = Auth::guard('user')->user();
          if (isset($group)) {
              if ($group->user_id == $user->id) {
                  $files=File::query()->where('group_id','=',$group->id)->get();
                  foreach ($files as $file){
                    if($file->reservation_holder==$request->deleted_user){
                        $file->update([
                            'isAvailable' => 1,
                            'reservation_holder' => null,
                        ]);
                        $file_user = File_User::query()->where('file_id', '=', $file->id)
                            ->where('user_id', '=', $request->deleted_user)
                            ->whereNull('check_out')->get();
                        $file_user->update([
                            'check_out' => Carbon::now()
                        ]);
                    }
                  }
                  $group_user=Group_User::query()->where('user_id','=',$request->deleted_user)
                      ->where('group_id','=',$group->id)->delete();
                  return $this->returnSuccessData('', 'user deleted successfully', 200);
              }

          return $this->returnError('you dont have authorization on this group', 400);
          }
          return $this->returnError("Error not found",404);
      }
}
