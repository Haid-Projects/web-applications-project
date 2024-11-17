<?php
$jsonFilePath = public_path('conf.json');

if (file_exists($jsonFilePath)) {
    $configData = json_decode(file_get_contents($jsonFilePath), true);
    return $configData;
}
return [];
