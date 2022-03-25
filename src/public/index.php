<?php
// print_r(apache_get_modules());
// echo "<pre>"; print_r($_SERVER); die;
// $_SERVER["REQUEST_URI"] = str_replace("/phalt/","/",$_SERVER["REQUEST_URI"]);
// $_GET["_url"] = "/";
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;
use Phalcon\Di;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Http\Response;
use Phalcon\Escaper;

$config = new Config([]);

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
    ]
);

$loader->register();

$container = new FactoryDefault();

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);


$container->set(
    'session',
    function () {
        $session = new Manager();
        $files = new Stream(
            [
                'savePath' => '/tmp',
            ]
        );

        $session
            ->setAdapter($files)
            ->setName('user')
            ->start();

        return $session;
    }
);

$container->set(
    'escaper',
    function (){
        return new Escaper();
    }
);

$container->set(
    'service',
    function () {
       
        return date('Y-m-d H:i:s');
    }
);

$application = new Application($container);

$container->set(
    'db',
    function () {
        return new Mysql(
            [
                'host'     => 'mysql-server',
                'username' => 'root',
                'password' => 'secret',
                'dbname'   => 'escaper',
            ]
        );
    }
);



try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );
    $response->send();
    // $response->redirect('https://en.wikipedia.org', true, 301);
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
    $response = new Response();

    $response->setStatusCode(404, 'Not Found');
    $response->setContent("Sorry, the page doesn't exist");
    $response->send();
}