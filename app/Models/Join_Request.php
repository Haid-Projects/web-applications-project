<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Join_Request extends Model
{
    use HasFactory;
    protected $table = "join_requests";
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'group_id',
    ];

    public function join_requests($id){
        return self::query()->select( 'groups.label', 'users.name','join_requests.created_at')
            ->join('groups', 'join_requests.group_id', '=', 'groups.id')
            ->join('users', 'join_requests.sender_id', '=', 'users.id')
            ->where("receiver_id",'=',$id)->orderBy("join_requests.created_at",'desc');
    }

}
