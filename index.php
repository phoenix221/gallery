<?php

require __DIR__ . '/vendor/autoload.php';

use FastRoute\RouteCollector;
use App\ImageController;


$dispatcher = FastRoute\simpleDispatcher(
    function (RouteCollector $r) {
        $r->addRoute('GET', '/', [ImageController::class, 'index']);
        $r->addRoute('GET', '/api/images', [ImageController::class, 'getAllImages']);
        $r->addRoute('GET', '/api/images/{id:\d+}', [ImageController::class, 'getImage']);
        $r->addRoute('POST', '/api/images', [ImageController::class, 'storeImage']);
        $r->addRoute('DELETE', '/api/images/{id:\d+}', [ImageController::class, 'deleteImage']);
    }
);

$httpMethod = $_SERVER['REQUEST_METHOD'];

$uri = parse_url(
    $_SERVER['REQUEST_URI'],
    PHP_URL_PATH
);

$routeInfo = $dispatcher->dispatch(
    $httpMethod,
    $uri
);

switch ($routeInfo[0]) {

    case FastRoute\Dispatcher::NOT_FOUND:

        http_response_code(404);

        echo json_encode([
            'error' => 'Not found'
        ]);

        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:

        http_response_code(405);

        echo json_encode([
            'error' => 'Method not allowed'
        ]);

        break;

    case FastRoute\Dispatcher::FOUND:

        [$class, $method] = $routeInfo[1];
        $vars = $routeInfo[2];

        $controller = new $class();

        call_user_func_array(
            [$controller, $method],
            $vars
        );

        break;
}