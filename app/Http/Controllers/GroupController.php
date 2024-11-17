<?php

namespace App\Http\Controllers;

use App\Aspects\Logger;
use App\Models\Group;
use App\Models\Group_User;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

#[Logger]
class GroupController extends Controller
{
    use GeneralTrait;

    public function index()
    {
       $groups=Group::all();
        return $this->returnSuccessData($groups, 'all group', 200);
    }

    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'label'=>'required',
        ]);

        if($validator->fails()) {
            return  $this->returnValidationError($validator->errors()->first(),400);
        }

        $user = Auth::guard('user')->user();

        $group = Group::create([
            'label' => $request->label,
            'user_id' => $user->id,
            'isPrivate' => $request->isPrivate ?? 0
        ]);
        if($request->isisPrivate==0){
         $users=User::query()->get('id');
         foreach ($users as $use) {
             $group_user = Group_User::create([
                 'user_id' => $use->id,
                 'group_id' => $group->id
             ]);
         }
             return $this->returnSuccessData($group, 'general group created successfully', 200);

        }
        $group_user=Group_User::create([
            'user_id'=>$user->id,
            'group_id'=>$group->id
        ]);
        return $this->returnSuccessData($group, 'group created successfully', 200);
    }

    public function getInfo(Request $request){
        $group_id = $request->query('group_id');
        $user = Auth::guard('user')->user();
        $group = Group::find($group_id);
        if($group->user_id === $user->id)
            return $this->returnSuccessData($group, "INfo", 200);

        return $this->returnError("Unauthorized",403);
    }
    public function show_my_own_group(Request $request)
    {
        $user=Auth::guard('user')->user();
        $fileQuery=Group::query();
        $groups = $user->user_groups_order_owner($user->id)->get();

        if(isset($groups)) {
            return $this->returnSuccessData($groups, "Success", 200);
        }
        return $this->returnError("Error not found",404);
    }

    public function show_my_join_group(Request $request)
    {
        $user=Auth::guard('user')->user();
        $fileQuery=Group::query();
        $groups = $user->user_groups_order_joined($user->id)->get();

        if(isset($groups)) {
            return $this->returnSuccessData($groups, "Success", 200);
        }
        return $this->returnError("Error not found",404);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function edit(Group $group)
    {
        //
    }


    public function update(Request $request, $group_id)
    {
        $group = Group::find($group_id);
        $user = Auth::guard('user')->user();
        if(isset($group)) {
        if($group->user_id === $user->id){
            $group->update([
              'label'=>$request->label ?? $group->label,
              'isPrivate'=> $request->isPrivate ?? $group->isPrivate,
            ]);

            return $this->returnSuccessData($group, 'group updated successfully', 200);
        }
            return $this->returnError('you can not update this group', 400);
        }

        return $this->returnError("Error not found",404);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($group_id)
    {
        $group = Group::find($group_id);
        $user = Auth::guard('user')->user();
        if (isset($group)) {
            if ($group->user_id === $user->id) {
                $group->delete();
                return $this->returnSuccessData($group, 'group deleted successfully', 200);
            }

            return $this->returnError('you can not delete this group', 400);
        }
        return $this->returnError("Error not found",404);
    }

}
