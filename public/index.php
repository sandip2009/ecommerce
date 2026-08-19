<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Container;
use App\Core\Request;
use App\Core\Router;
use App\Interfaces\ProductRepositoryInterface;
use App\Repositories\ProductRepository;


$container = new Container();
$session = new \App\Core\Session();

$exceptionHandler = new \App\Core\ExceptionHandler(
    dirname(__DIR__) . '/storage/logs/app.log'
);
$config = require __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| PDO
|--------------------------------------------------------------------------
*/
$session->start();
$container->singleton(
    \PDO::class,
    function () use ($config) {

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['database']
        );

        return new \PDO(
            $dsn,
            $config['username'],
            $config['password'],
            [
                \PDO::ATTR_ERRMODE =>
                    \PDO::ERRMODE_EXCEPTION,

                \PDO::ATTR_DEFAULT_FETCH_MODE =>
                    \PDO::FETCH_ASSOC,

                \PDO::ATTR_EMULATE_PREPARES =>
                    false,
            ]
        );
    }
);

/*
|--------------------------------------------------------------------------
| Repository Interface Binding
|--------------------------------------------------------------------------
*/

$container->bind(
    ProductRepositoryInterface::class,
    function ($container) {
        return $container->make(
            ProductRepository::class
        );
    }
);

/*
|--------------------------------------------------------------------------
| Router
|--------------------------------------------------------------------------
*/
$exceptionHandler->register();
$router = new Router($container);

require_once __DIR__ . '/../routes/api.php';

/*
|--------------------------------------------------------------------------
| Request
|--------------------------------------------------------------------------
*/

$request = new Request();

$router->dispatch($request);