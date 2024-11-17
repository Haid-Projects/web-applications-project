<?php

namespace App\Http\Controllers;

use App\Aspects\Logger;
use App\Models\File;
use App\Models\File_User;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
#[Logger]
class FileUserController extends Controller
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
     * @param  \App\Models\File_User  $file_User
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $file_user=File::find($request->file_id);
        return $this->returnSuccessData($file_user->files_user()->get(), "Success", 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\File_User  $file_User
     * @return \Illuminate\Http\Response
     */
    public function edit(File_User $file_User)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\File_User  $file_User
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File_User $file_User)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\File_User  $file_User
     * @return \Illuminate\Http\Response
     */
    public function destroy(File_User $file_User)
    {
        //
    }
}
