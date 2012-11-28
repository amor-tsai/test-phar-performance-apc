<?php
require '../vendor/autoload.php';
require __DIR__ . '/../vendor/Zend_Db-2.1.0beta1.autoload.php';

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
    echo 'Interface loaded from file system: ';
    var_dump(interface_exists('Zend\Db\Adapter\Platform\PlatformInterface', true));
    $app->render('index.html');
});


// Run app
$app->run();

