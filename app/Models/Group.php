<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'user_id',
        'isPrivate'
    ];
    protected $hidden = ['pivot'];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
        public function join_requests_user(){
        return $this->belongsToMany(User::class,'join_requests','group_id');
    }
    public function group_users(){
        return $this->belongsToMany(User::class,'group_users','group_id')->select("users.id","users.name");
    }
}
