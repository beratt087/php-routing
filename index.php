<?php

require __DIR__ . '/vendor/autoload.php';

use \Luadex\Core\{Route, App};

$app = new \Luadex\Core\App();
$app->loadEnv();
require __DIR__ . "/Http/routes/web.php";
Route::prefix('/api');
require __DIR__ . "/Http/routes/api.php";
Route::$prefix = '';
Route::dispatch();