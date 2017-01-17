<?php
require "vendor/autoload.php";

use Gallerie\Application\ApplicationException;
use Gallerie\Application\Art\ArtControllerProvider;
use Gallerie\Application\User\UserControllerProvider;
use Gallerie\Authorization\Authorization;
use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
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
$app['authorization'] = function() {
  return new Authorization;
};

// Create the controllers
$app->register(new ServiceControllerServiceProvider);
$app->mount('/',new UserControllerProvider);
$app->mount('/',new ArtControllerProvider);

// Run the application
$app->run();