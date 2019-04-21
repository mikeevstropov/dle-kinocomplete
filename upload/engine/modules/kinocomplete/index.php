<?php

@ini_set('default_charset', 'utf-8');

require __DIR__ . '/vendor/autoload.php';

use Slim\App;
use Kinocomplete\Router\Router;
use Kinocomplete\Service\ServiceFactory;
use Kinocomplete\Service\ServiceInjector;
use Kinocomplete\Controller\ControllerResolver;
use Kinocomplete\Configuration\ConfigurationProvider;
use Kinocomplete\Configuration\ConfigurationInjector;

// Slim settings.
$settings = [
  'displayErrorDetails' => true,
  'addContentLengthHeader' => false
];

// Load configuration.
$configuration = ConfigurationProvider::getDefaults(__DIR__, [
  'router' => new Router(),
  'settings' => $settings
]);

// Create instances.
$app = new App($configuration);
$container = $app->getContainer();
$router = $container['router'];
$configInjector = new ConfigurationInjector($container);
$serviceInjector = new ServiceInjector($container);

// Injections.
$router->setContainer($container);
$serviceInjector->inject();
$configInjector->inject();

// System version check.
$container->get('diagnostics')->systemVersionCheck();

// Bind routes.
$app->any('/', ControllerResolver::class);

// Display admin interface.
if (
  !array_key_exists('ajax', $_REQUEST) &&
  !array_key_exists('cron', $_REQUEST)
) echoheader(
  $container->get('module_label'),
  $container->get('module_description')
);

// Run an application.
$app->run();
