<?php
require "vendor/autoload.php";

use Devi\App\ApplicationException;
use Devi\App\ImageControllerProvider;
use Devi\App\UserControllerProvider;
use Devi\Authorization\Authorization;
use Devi\Model\ImageRepository;
use Devi\Model\Storage\Flysystem;
use Devi\Model\Storage\GzipWrapper;
use Devi\Model\UserRepository;
use Devi\Utils\Database;
use League\Flysystem\Filesystem;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Create the application with the settings
$app = new Application(require('settings.php'));

// Before execution
$app->before(function(Request $request) {
  // Parse the request body if JSON
  if (strpos($request->headers->get('Content-Type'),'application/json') === 0) 
  {
    $data = json_decode($request->getContent(),true);
    $request->request->replace(is_array($data) ? $data : []);
  }
});

// After execution
$app->after(function(Request $request, Response $response) {
  // Set CORS request responses
  $response->headers->set('Access-Control-Allow-Origin','*');
  $response->headers->set('Access-Control-Allow-Headers','Origin, Content-Type, Accept, Authorization, X-Requested-With');
  $response->headers->set('Access-Control-Allow-Methods','GET, POST, PUT, DELETE');
  
  // Pretty print the JSON response
  if ($response instanceof JsonResponse)
    $response->setEncodingOptions(JSON_PRETTY_PRINT);
  
  return $response;
});

// Add application exception handling
$app->error(function(Exception $ex) {
  return new Response($ex,500);
  //return new JsonResponse(['error' => $ex->getMessage()],500);
});

// Add support for CORS requests
$app->options("{anything}", function () {
  return new JsonResponse(null,204);
});

// Create the database service
$app['database'] = function($app) {
  return new Database("mysql:host=" . $app['db.server'] . ";dbname=" . $app['db.database'],$app['db.user'],$app['db.password']);
};

// Create authorization middleware
$app['authorization'] = function() {
  return new Authorization;
};

// Create the file system
$app['images.storage'] = function($app) {
  $filesystem = new Filesystem($app['storage']);
  
  return new GzipWrapper(
    new Flysystem($filesystem,"image-%s.gz"));
};

// Create the repositories and providers
$app['users.repository'] = function($app) {
  return new UserRepository($app['database'],'users');
};
$app['users.provider'] = function($app) {
  return new UserControllerProvider($app['users.repository']);
};
$app['images.repository'] = function($app) {
  return new ImageRepository($app['database'],'images');
};
$app['images.provider'] = function($app) {
  return new ImageControllerProvider($app['images.repository'],$app['images.storage']);
};

// Create the controllers
$app->mount('/users',$app['users.provider']);
$app->mount('/images',$app['images.provider']);

// Run the application
$app->run();