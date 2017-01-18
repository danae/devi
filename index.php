<?php
require "vendor/autoload.php";

use Gallerie\Application\ApplicationException;
use Gallerie\Application\ArtControllerProvider;
use Gallerie\Application\UserControllerProvider;
use Gallerie\Authorization\Authorization;
use Gallerie\Implementations\MeekroDB\ArtRepository;
use Gallerie\Implementations\MeekroDB\UserRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

// Create the application
$app = new Application;

// Add JSON request parsing
$app->before(function(Request $request) {
  if (strpos($request->headers->get('Content-Type'),'application/json') === 0) {
    $data = json_decode($request->getContent(),true);
    $request->request->replace(is_array($data) ? $data : []);
  }
});

// Add application exception handling
$app->error(function(ApplicationException $ex) {
  return new JsonResponse(['error' => $ex->getMessage()],$ex->getCode());
});

// Add other error handling
$app->error(function(Exception $ex) {
  return new JsonResponse(['error' => $ex->getMessage()],500);
});

// Create the database
$app['database'] = new MeekroDB('het.is','gallerie_test','uWEvwBaXHnjSZSY4','gallerie_test');
$app['database']->throw_exception_on_error = true;
$app['database']->throw_exception_on_nonsql_error = true;

// Create authorization middleware
$app['authorization'] = new Authorization;

// Create the repositories and providers
$app['users.repository'] = new UserRepository($app['database'],'users');
$app['users.provider'] = new UserControllerProvider($app['users.repository']);

$app['arts.repository'] = new ArtRepository($app['database'],'art');
$app['arts.provider'] = new ArtControllerProvider($app['arts.repository']);

// Create the controllers
$app->mount('/',$app['users.provider']);
$app->mount('/',$app['arts.provider']);

// Run the application
$app->run();