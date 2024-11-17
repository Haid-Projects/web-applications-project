<?php

namespace App\Http\Controllers;

use App\Aspects\Logger;
use App\Aspects\Transactional;
use App\Models\File;
use App\Models\File_User;
use App\Models\Group;
use App\Models\Group_User;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File as Files;
use Illuminate\Support\Str;
use ZipArchive;

#[Transactional,Logger]
class FileController extends Controller
{
    use GeneralTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $files=File::all();
        return $this->returnSuccessData($files, 'all files', 200);
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
     * @return \Illuminate\Http\JsonResponse
     */
    //////transaction
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'label'=>"required",
            'path'=>"required|file"
        ]);

        if($validator->fails()) {
            return  $this->returnValidationError($validator->errors()->first(),400);
        }

        $user = Auth::guard('user')->user();

        $path=$request->file('path');
        $nameofpath=time().'.'.$path->getClientOriginalExtension();
        $destinationpath=public_path('storage/file');
        $path->move($destinationpath,$nameofpath);


        $file = File::create([
            'label' => $request->label,
            'user_id' => $user->id,
            'path' =>$nameofpath,

        ]);
        return $this->returnSuccessData($file, 'file created successfully', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\JsonResponse
     */
    public function show_own_file(Request $request)
    {
        $user_id=Auth::guard('user')->user()->id;
        $fileQuery=File::query();

        if($user_id)
        {$fileQuery->where('user_id',"=",$user_id)
            ->where('group_id',"=",null);}

        $files=$fileQuery->get();

        if(isset($files)) {
            return $this->returnSuccessData($files, "Success", 200);
        }
        return $this->returnError("Error not found",404);
    }


    public function show_files(Request $request)
    {

        $user_id=Auth::guard('user')->user()->id;
        $group_id=$request->query('group_id');
        $group_user=Group_User::query()->where('group_id',"=",$group_id)
        ->where('user_id',"=",$user_id)->get()->first();
        if(!isset($group_user)){
            return $this->returnError("not allowed",404);
        }
        $fileQuery=File::query()->with('user');
        if($group_id)
        {$fileQuery->where('group_id',"=",$group_id);}
        $files=$fileQuery->get();

        if(isset($files)) {
            return $this->returnSuccessData($files, "Success", 200);
        }
        return $this->returnError("Error not found",404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $file_id)
    {
        $file = File::find($file_id);
        $user = Auth::guard('user')->user();
        if(isset($file)) {
            if ($file->user_id === $user->id) {
                $file->update([
                    'label' => $request->label ?? $file->label,
                ]);

                return $this->returnSuccessData($file, 'file updated successfully', 200);
            }

            return $this->returnError('you can not update this group', 400);
        }
        return $this->returnError("Error not found",404);
    }

    public function add_to_group(Request $request,)
    {
        $validator = Validator::make($request->all(), [
            'label'=>"required",
            'path'=>"required|file",
            'group_id'=>"required"
        ]);

        if($validator->fails()) {
            return  $this->returnValidationError($validator->errors()->first(),400);
        }

        $user = Auth::guard('user')->user();
        $path=$request->file('path');
        $nameofpath=time().'.'.$path->getClientOriginalExtension();
        $destinationpath=public_path('storage/file');
        $path->move($destinationpath,$nameofpath);

        $group = Group::find($request->group_id);
        $file = File::create([
            'label' => $request->label,
            'user_id' => $user->id,
            'path' =>$nameofpath,
            'group_id'=>$group->id

        ]);
        return $this->returnSuccessData($file, 'file created successfully', 200);
    }

    /**
     * @throws \Exception
     */

    public function check_in_single(Request $request)
    {
        $user = Auth::guard('user')->user();
        $file_id = $request->file_id;
        $version=$request->version;
        $file=File::find($file_id);
        if(isset($file)){
                    if ($file->isAvailable == 1 &&$file->version==$version) {
                        $file->update([
                            'isAvailable' => false,
                            'version'=>$file->version+1,
                            'reservation_holder'=>$user->id
                        ]);
                        $report=File_User::create([
                            'check_in'=>Carbon::now(),
                            'check_out'=>null,
                            'user_id'=>$user->id,
                            'file_id'=>$file->id,

                        ]);
                    } else {
                        throw new \Exception("The file has been requested ");
                    }

            return $this->returnSuccessData('', 'successfully', 200);
        }
        return $this->returnError("Error not found",404);
    }


    /**
     * @throws \Exception
     */
    public function check_in(Request $request)
    {
        try {
        $user = Auth::guard('user')->user();
        $files = $request->input('files');
        $zipFileName = Str::random(10).'.zip';
        $zipFilePath = '/storage/zip/' . $zipFileName;
        $zip = new ZipArchive();
        $zip->open(public_path($zipFilePath), ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if(isset($files)){
            foreach ($files as $reserved_file) {
                $file = File::find($reserved_file["id"]);
                if(isset($file)){
                    if ($file->isAvailable == 1&&$file->version==$reserved_file["version"]) {
                        $file_path = 'storage/file/'.$file->path;
                        $extension = pathinfo($file_path, PATHINFO_EXTENSION);
                        $zip->addFile($file_path, $file->label.'.'.$extension);
                        $file->update([
                            'isAvailable' => false,
                            'version'=>$file->version+1,
                            'reservation_holder'=>$user->id
                        ]);

                  $report=File_User::create([
                      'check_in'=>Carbon::now(),
                      'check_out'=>null,
                      'user_id'=>$user->id,
                      'file_id'=>$file->id,

                  ]);
                    } else {
                        throw new \Exception("The file has been requested ");

                    }
            } else {
                    throw new \Exception("The file not found ");
                }
            }

            $zip->close();
            $zipFilePath2 = public_path("storage/zip/".$zipFileName);

            if (file_exists($zipFilePath2)) {
                return Response::download($zipFilePath2,Auth::guard('user')->user()->name.'.zip', array('Content-Type: application/zip','Content-Length: '. filesize($zipFilePath2)));
            } else {
                return $this->returnError("zip file does not exist",404);
            }
            }
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), 400);
        }
        return $this->returnError("Error not found",404);
    }
    public function check_inn(Request $request)
    {
        $user = Auth::guard('user')->user();
        $files = $request->input('files');
        $filePaths = [];
        $fileNames = [];
        if(isset($files)){
            foreach ($files as $reserved_file) {
                $file = File::find($reserved_file["id"]);
                if(isset($file)){
                    if ($file->isAvailable == 1&&$file->version==$reserved_file["version"]) {
                        $file_path = 'storage/file/1704805527.png';
                        $filePaths[] = $file_path;
                        $fileNames[] =$file->label;
                        $type = Files::mimeType($file_path);

                        $headers = [ 'Content-Type' => 'image/png' ];

                        return response()->file(public_path($file_path), $headers);
//                        $file->update([
//                            'isAvailable' => false,
//                            'version'=>$file->version+1,
//                            'reservation_holder'=>$user->id
//                        ]);

                        $report=File_User::create([
                            'check_in'=>Carbon::now(),
                            'check_out'=>null,
                            'user_id'=>$user->id,
                            'file_id'=>$file->id,

                        ]);
                    } else {
                        throw new \Exception("The file has been requested ");
                    }
                } else {
                    throw new \Exception("The file not found ");
                }
            }
            $responses = [];
            foreach ($filePaths as $key => $filePath) {
                $fileName = $fileNames[$key];

                return  response()->download($filePath, $fileName);
            }
            $mergedResponse = collect($responses)->reduce(function ($carry, $response) {
                return $carry->merge($response);
            }, collect());

            return $mergedResponse;
        }
        return $this->returnError("Error not found",404);
    }




    public function check_out(Request $request)
    {
        try{
        $user = Auth::guard('user')->user();
        $files = $request->input('files');
        if(isset($files)) {
            foreach ($files as $check_out_files) {
                $file = File::find($check_out_files);
                if (isset($file)) {
                    if ($file->isAvailable === 0) {
                        if ($file->reservation_holder == $user->id) {
                            $file->update([
                                'isAvailable' => 1,
                                'reservation_holder' => null,
                            ]);
                            $file_user = File_User::query()->where('file_id', '=', $file->id)
                                ->where('user_id', '=', $user->id)
                                ->whereNull('check_out')->get()->first();
                            $file_user->update([
                                'check_out' => Carbon::now()
                            ]);
                        } else {
                            throw new \Exception("error in the process check out");
                        }
                    } else {
                        throw new \Exception("error in the process check out");
                    }
                } else {
                    throw new \Exception("error file not found");
                }
            }
            return $this->returnSuccessData("", 'check out successfully', 200);
        }
    } catch (\Exception $e) {
return $this->returnError($e->getMessage(), 400);
}
        return $this->returnError("Error not found",404);}


    public function upload_with_file(Request $request)
    {
        $file = File::find($request->file_id);
        $user = Auth::guard('user')->user();
        if(isset($file)) {
           if($file->isAvailable == 0 && $file->reservation_holder==$user->id){
               $file_path = 'storage/file/'.$file->path;
               if(Files::exists($file_path)) {

                  $old_type=explode('/', Files::mimeType($file_path))[0];
                  $new_type=explode('/',Files::mimeType($request->file('file')))[0];
                  if($old_type!=$new_type){
                      return $this->returnError("type doesnt match",404);
                  }

                if($new_type=="text") {
                    $content_old = Files::get($file_path);
                    $content_new = Files::get($request->file('file'));
//                   $differ = new HtmlDiffer();
//                   $diff = $differ->render($content_old, $content_new);
                    Files::put($file_path, ' ');
                    Files::put($file_path, (string)$content_new);
//                   $file->update([
//                       'diff' => $diff,
//
//                   ]);
                }
                else{
                    $path=$request->file('file');
                    $nameofpath=time().'.'.$path->getClientOriginalExtension();
                    $destinationpath=public_path('storage/file');
                    $path->move($destinationpath,$nameofpath);
                    Files::delete($file_path);
                    $file->update([
                        'path'=> $nameofpath
                    ]);

                }
                   $file->update([
                       'isAvailable' => 1,
                       'reservation_holder' => null,
                   ]);
                   $file_user = File_User::query()->where('file_id', '=', $file->id)
                       ->where('user_id', '=', $user->id)
                       ->whereNull('check_out')->get()->first();
                   $file_user->update([
                       'check_out' => Carbon::now()
                   ]);
                   return $this->returnSuccessData("", 'updated successfully', 200);

               }
               else {return $this->returnError("the sever can not find this file",404);}
           }

           else { return $this->returnError("your are not allowed to update this file right now",404);}

        }
        return $this->returnError("Error not found",404);

    }
    public function upload_with_txt(Request $request)
    {
        $file = File::find($request->file_id);
        $user = Auth::guard('user')->user();

        if(isset($file)) {
            if($file->isAvailable == 0 && $file->reservation_holder==$user->id){

                $file_path = 'storage/file/'.$file->path;
                if(Files::exists($file_path)) {
                    $content_old =Files::get($file_path);
                    $content_new = $request->txt;

//                   $differ = new HtmlDiffer();
//                   $diff = $differ->render($content_old, $content_new);
                    Files::put($file_path, ' ');
                    Files::put($file_path,(string) $content_new);
//                   $file->update([
//                       'diff' => $diff,
//
//                   ]);
                    $file->update([
                        'isAvailable' => 1,
                        'reservation_holder' => null,
                    ]);
                    $file_user = File_User::query()->where('file_id', '=', $file->id)
                        ->where('user_id', '=', $user->id)
                        ->whereNull('check_out')->get()->first();
                    $file_user->update([
                        'check_out' => Carbon::now()
                    ]);

                    return $this->returnSuccessData("", 'updated successfully', 200);

                }
                else return $this->returnError("the sever can not find this file",404);
            }
            else return $this->returnError("your are not allowed to update this file right now",404);

        }
        return $this->returnError("Error not found",404);

    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\JsonResponse
     */
    ///////transaction
    public function destroy($file_id)
    {
        $file = File::find($file_id);
        $user = Auth::guard('user')->user();
        if(isset($file)) {
            if ($file->user_id == $user->id) {
                if($file->isAvailable==1){
                $file_paths=config('constants.constant.file_url');
                $file_path = $file_paths.'storage/file/'.$file->path;  // Value is not URL but directory file path
                if(Files::exists($file_path)) {
                    Files::delete($file_path);
                }
                $file->delete();

                return $this->returnSuccessData($file, 'file deleted successfully', 200);
            }
                return $this->returnError('file not available', 400);
            }

            return $this->returnError('you can not delete this group', 400);
        }
        return $this->returnError("Error not found",404);
    }
    public function content(Request $request){
        $file_id = $request->query('file_id');
        $file = File::find($file_id);
        $myfile = fopen("storage/file/$file->path", "r") or die("Unable to open file!");
        $content = fread($myfile,filesize("storage/file/$file->path"));
        fclose($myfile);
        return response()->json(['data' => $content]);
    }
}
