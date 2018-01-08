<?php
require "vendor/autoload.php";

use Devi\Authorization\Authorization;
use Devi\DeviApplication;
use Devi\Provider\AlbumControllerProvider;
use Devi\Provider\FilesControllerProvider;
use Devi\Provider\ImageControllerProvider;
use Devi\Provider\UserControllerProvider;
use Imagine\Gd\Imagine;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;

// Register error handlers
ErrorHandler::register();

// Create the application with the settings
$app = new DeviApplication(require('settings.php'));

// Parse the request body if JSON
$app->before(function(Request $request) 
{
  if (strpos($request->headers->get('Content-Type'),'application/json') === 0) 
  {
    $data = json_decode($request->getContent(),true);
    $request->request->replace(is_array($data) ? $data : []);
  }
});

// Pretty print the JSON response
$app->after(function(Request $request, Response $response) 
{
  if ($response instanceof JsonResponse)
    $response->setEncodingOptions(JSON_PRETTY_PRINT);
  return $response;
});

// Add exception handling
$app->error(function(Exception $ex, Request $request) 
{
  // Check if the debug parameter is true
  if ($request->query->getBoolean('debug') === true)
  {
    // Print the thrown exception
    return new JsonResponse([
      'error' => $ex->getMessage(),
      'exceptionThrown' => get_class($ex),
      'trace' => $ex->getTraceAsString()
    ],$ex instanceof HttpException ? $ex->getStatusCode() : 500);
  }
  else
  {
    // Just print the error
    return new JsonResponse([
      'error' => $ex->getMessage()
    ],$ex instanceof HttpException ? $ex->getStatusCode() : 500);
  }
});

// Add support for CORS requests
$app->register(new CorsServiceProvider);
$app->after($app['cors']);

// Create the serializer for display
$app['json_serializer'] = function() {
  return new Serializer([new DateTimeNormalizer(DateTime::ISO8601),new CustomNormalizer],[new JsonEncoder]);
};

// Create authorization
$app['authorization'] = function($app) {
  return new Authorization($app['users']);
};

// Create the imagine interface
$app['imagine'] = function() {
  return new Imagine;
};

// Create the providers for the models
$app['user_controller'] = function() {
  return new UserControllerProvider();
};
$app['image_controller'] = function($app) {
  return new ImageControllerProvider();
};
$app['album_controller'] = function($app) {
  return new AlbumControllerProvider();
};
$app['files_controller'] = function($app) {
  return new FilesControllerProvider();
};

// Create the controllers
$app->mount('/',$app['user_controller']);
$app->mount('/',$app['image_controller']);
$app->mount('/',$app['album_controller']);
$app->mount('/',$app['files_controller']);

// Run the application
$app->run();