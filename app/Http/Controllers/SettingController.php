<?php

namespace App\Http\Controllers;


use App\Traits\GeneralTrait;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use GeneralTrait;
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $filePath = public_path('conf.json');
        $existingJson = file_get_contents($filePath);
        $existingData = json_decode($existingJson, true);
        $existingData['DB_ENGINE'] = $request->input('DB_ENGINE') ?? $existingData['DB_ENGINE'];
        $existingData['DB_HOST'] = $request->input('DB_HOST')?? $existingData['DB_HOST'];
        $existingData['DB_PORT'] = $request->input('DB_PORT')?? $existingData['DB_PORT'];
        $existingData['DB_USERNAME'] = $request->input('DB_USERNAME')?? $existingData['DB_USERNAME'];
        $existingData['DB_PASSWORD'] = $request->input('DB_PASSWORD')?? $existingData['DB_PASSWORD'];
        $existingData['LOGGING_LEVEL'] = $request->input('LOGGING_LEVEL')?? $existingData['LOGGING_LEVEL'];
        $existingData['EXCLUSIVE_FILES_PER_USER'] = $request->input('EXCLUSIVE_FILES_PER_USER')?? $existingData['EXCLUSIVE_FILES_PER_USER'];
        $existingData['DB_DATABASE'] = $request->input('DB_DATABASE')?? $existingData['DB_DATABASE'];
        $existingData['FILE_SIZE_LIMIT'] = $request->input('FILE_SIZE_LIMIT')?? $existingData['FILE_SIZE_LIMIT'];
        $updatedJson = json_encode($existingData, JSON_PRETTY_PRINT);
        file_put_contents($filePath, $updatedJson);
        return $this->returnSuccessData("", 'setting updated successfully', 200);
    }
}
