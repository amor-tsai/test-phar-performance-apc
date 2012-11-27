<?php
require '../vendor/autoload.php';

// Prepare app
$app = new \Slim\Slim(array(
    'templates.path' => '../templates',
    'log.level' => 4,
    'log.enabled' => false,
    'log.writer' => new \Slim\Extras\Log\DateTimeFileWriter(array(
        'path' => '../logs',
        'name_format' => 'y-m-d'
    ))
));

// Prepare view
\Slim\Extras\Views\Twig::$twigOptions = array(
    'charset' => 'utf-8',
    'cache' => realpath('../templates/cache'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);
$app->view(new \Slim\Extras\Views\Twig());

// Define routes
$app->get('/', function () use ($app) {
    $app->render('index.html');
});

// make sure we visit a class located in the Zend_Db library
var_dump(interface_exists('Zend\Db\Adapter\Platform\PlatformInterface', true));

// Run app
$app->run();

echo '<pre>';
print_r(apc_cache_info());