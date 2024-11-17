<?php

namespace App\Http\Controllers;

use App\Aspects\Logger;
use App\Models\Group;
use App\Models\Group_User;
use App\Models\Join_Request;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\FriendController;

#[Logger]
class JoinRequestController extends Controller
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

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => "required",
            'group_id' => "required"
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError($validator->errors()->first(), 400);
        }

        $user = Auth::guard('user')->user();
        $group = Group::find($request->group_id);
        $receiver=User::query()->where('email','=',$request->email)->get()->first();
        if(!isset($receiver)){
            return $this->returnError("email not found", 404);
        }
        $group_user=Group_User::query()->where('user_id','=',$receiver->id)->get()->first();
        if(!isset($group_user)){
            return $this->returnError("user already joined", 404);
        }
        $check=Join_Request::query()->where('receiver_id','=',$receiver->id)->get()->first();
        if(isset($check)){
            return $this->returnError("Join request already send", 404);
        }
        if (isset($group)) {
            if ($group->user_id === $user->id) {
             $join_request= Join_Request::create([
                  'sender_id'=>$user->id,
                  'receiver_id'=>$receiver->id,
                  'group_id'=>$request->group_id

              ]);
                return $this->returnSuccessData($join_request, 'join requested successfully', 200);
            }

            return $this->returnError('you can not add to this group', 400);
        }
        return $this->returnError("Error not found", 404);

    }
    public function accept_join_request(Request $request)
    {
        $user=Auth::guard('user')->user();
        $join_request=Join_Request::find($request->join_request);
        if(isset($join_request)){
          $group=Group_User::create([
            'user_id'=>$user->id,
              'group_id'=>$join_request->group_id
          ]);
          $join_request->delete();
           (new FriendController)->relation($user->id,$join_request->group_id);
          return $this->returnSuccessData($group, 'accepted successfully', 200);
        }
        return $this->returnError("some thing went wrong", 404);

    }
    public function reject_join_request(Request $request)
    {
        $user=Auth::guard('user')->user();
        $join_request=Join_Request::find($request->join_request);
        if(isset($join_request)){
          $join_request->delete();
          return $this->returnSuccessData(null, 'rejected successfully', 200);
        }
        return $this->returnError("some thing went wrong", 404);

    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Join_Request  $join_Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Join_Request $join_Request)
    {
        $id=Auth::guard('user')->user()->id;
        $user=User::query()->where("id","=",$id)->get()->first();
        return $this->returnSuccessData($user->join_requests()->get(), "Success", 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Join_Request  $join_Request
     * @return \Illuminate\Http\Response
     */
    public function edit(Join_Request $join_Request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Join_Request  $join_Request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Join_Request $join_Request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Join_Request  $join_Request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Join_Request $join_Request)
    {
        //
    }
}
