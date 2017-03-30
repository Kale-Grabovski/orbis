<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Components\DB;
use App\Controllers\Responses\JsonResponse;

// Parse the dotenv file
$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

// Initialize database connection
try {
    DB::init(getenv('DB_DRIVER'), getenv('DB_HOST'), getenv('DB_USERNAME'),
        getenv('DB_PASSWORD'), getenv('DB_DATABASE'));
} catch (PDOException $e) {
    echo "Couldn't connect to database: " . $e->getMessage() . "\n";
    exit;
}

// Routes list
$routes = [
    '/api/addImpression'   => 'App\Controllers\ApiController@addImpression',
    '/api/addConversion'   => 'App\Controllers\ApiController@addConversion',
    '/api/getByUserId'     => 'App\Controllers\ApiController@getByUserId',
    '/api/getCurrentMonth' => 'App\Controllers\ApiController@getCurrentMonth',
    '/api/getPastYear'     => 'App\Controllers\ApiController@getPastYear',
];

// Chosen response
$response = new JsonResponse();

// Iterate through routes trying to find a match
foreach ($routes as $route => $handler) {
    if (strpos($_SERVER['REQUEST_URI'], $route) === 0) {
        list($controller, $action) = explode('@', $handler);

        try {
            echo (new $controller($response))->$action();
        } catch (Exception $e) {
            http_response_code($e->getCode());
            echo $e->getMessage();
        }

        exit;
    }
}

header("HTTP/1.0 404 Not Found");
echo 'Not found';
