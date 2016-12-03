<?php

use Lokhman\Silex\Provider\ConfigServiceProvider;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

if (isset($_ENV['env']) && ($env = $_ENV['env'])) {
    $app['env'] = $env;
} else {
    $app['env'] = 'prod';
}

$app->register(new ConfigServiceProvider(__DIR__ . '/../config', [], 'app'));
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

// Define result view
$app->view(function (array $controllerResult) use ($app) {
    return $app->json([
        'response' => $controllerResult,
        'ver' => '0.1',
        'error_code' => 0,
        'error_message' => 'ok',
        'time' => date('Y-m-d H:i:s')
    ]);
});

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    return $app->json([
        'response' => new \stdClass(),
        'ver' => '0.1',
        'error_code' => $code,
        'error_message' => $e->getMessage(),
        'time' => date('Y-m-d H:i:s')
    ]);
});

// Define services and repo
$app['service.redis'] = function () use ($app) {
    return new \Predis\Client($app['config']['redis']);
};
$app['repository.locale'] = function () use ($app) {
    return new \Application\Repository\LocaleRedisRepository($app['service.redis']);
};
$app['repository.translation'] = function () use ($app) {
    return new \Application\Repository\TranslationRedisRepository($app['service.redis']);
};
$app['controller.locale'] = function () use ($app) {
    return new \Application\Controller\LocaleController($app['repository.locale']);
};
$app['controller.translation'] = function () use ($app) {
    return new \Application\Controller\TranslationController($app['repository.translation'], $app['repository.locale']);
};

$app->get('/', function () {
    return [];
});

$app->get('/locales', 'controller.locale:listAction');
$app->get('/translations/{localeName}', 'controller.translation:listAction');
$app->get('/translations/{localeName}/{words}', 'controller.translation:translateAction')->convert('words', function ($string) {
    return explode('&', $string);
});

// Return app for tests
if ($app['env'] == 'test') {
    return $app;
}

$app->run();