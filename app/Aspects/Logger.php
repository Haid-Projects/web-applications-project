<?php

namespace App\Aspects;

use AhmadVoid\SimpleAOP\Aspect;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Logger implements Aspect
{
    use GeneralTrait;
    public $level3=[
        'FileController',
        'GroupController' ,
        'JoinRequestController',
    ];
    public $level2=[
        'FileController',
        'GroupController'
    ];
    public $level1=[
        'FileController'
    ];
    // The constructor can accept parameters for the attribute
    public function __construct()
    {

    }

    public function executeBefore($request, $controller, $method)
    {
        $level=config("custom_config.LOGGING_LEVEL");
        $name= Auth::guard('user')->user()->name;
        $modelName = $this->getModelName(class_basename($controller));
        $message = null;
        $httpMethod = $request->getMethod();
        if($level==1){
            $file = fopen('storage/logs/requests_level_1.log', "a");
            if(in_array(class_basename($controller), $this->level1)){
                $message = $name . ' ' .$httpMethod.' '. $modelName . ' ' . $method;
            }

        }
        elseif($level==2){
            $file = fopen('storage/logs/requests_level_2.log', "a");
            if(in_array(class_basename($controller), $this->level2)){
                $message = $name . ' ' .$httpMethod.' '. $modelName . ' ' . $method;
            }}
        else{
            $file = fopen('storage/logs/requests_level_3.log', "a");
            if(in_array(class_basename($controller), $this->level3)){
                $message = $name . ' ' .$httpMethod.' '. $modelName . ' ' . $method;

            }
        }
        $logEntry = "[" . Carbon::now()->toDateTimeString() . "] " . $message . PHP_EOL;
        fwrite($file, $logEntry);
        fclose($file);

    }

    public function executeAfter($request, $controller, $method, $response)
    {
        $level=config("custom_config.LOGGING_LEVEL");
        $name= Auth::guard('user')->user()->name;
        $modelName = $this->getModelName(class_basename($controller));
        $httpMethod = $request->getMethod();
        $httpLines = explode(PHP_EOL, $response);
        $status = $httpLines[0];
        $message = null;
        if($level==1){
            $file = fopen('storage/logs/responses_level_1.log', "a");
            if(in_array(class_basename($controller), $this->level1)){
                $message = $name . ' ' .$httpMethod.' '. $modelName . ' ' . $method.' ' .$status;
            }

        }
        elseif($level==2){
            $file = fopen('storage/logs/responses_level_2.log', "a");
            if(in_array(class_basename($controller), $this->level2)){
                $message = $name . ' ' .$httpMethod.' '. $modelName . ' ' . $method.' ' .$status;
            }}
        else{
            $file = fopen('storage/logs/responses_level_3.log', "a");
            if(in_array(class_basename($controller), $this->level3)){
                $message = $name . ' ' .$httpMethod.' ' .$status;

            }
        }

        $logEntry = "[" . Carbon::now()->toDateTimeString() . "] " . $message . PHP_EOL;
        fwrite($file, $logEntry);
        fclose($file);
    }

    public function executeException($request, $controller, $method, $exception)
    {
        echo "dd";
        return $this->returnError("dddd",404);
    }

    protected function getModelName($controllerName)
    {
        // Extract the word before "Controller.php"
        return $modelName = preg_replace('/Controller\.php$/', '', $controllerName);
    }
}


