<?php
require "vendor/autoload.php";

use Devi\App\ApplicationException;
use Devi\App\ImageControllerProvider;
use Devi\App\UserControllerProvider;
use Devi\Authorization\Authorization;
use Devi\Model\PDO\ImageRepository;
use Devi\Model\PDO\UserRepository;
use Devi\Model\Storage\Flysystem;
use Devi\Model\Storage\GzipWrapper;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Create the application
$app = require('settings.php');

// Add JSON request parsing
$app->before(function(Request $request) {
  if (strpos($request->headers->get('Content-Type'),'application/json') === 0) 
  {
    $data = json_decode($request->getContent(),true);
    $request->request->replace(is_array($data) ? $data : []);
  }
});

// Add JSON response pretty printing
$app->after(function(Request $request, Response $response) {
  if ($response instanceof JsonResponse)
    $response->setEncodingOptions(JSON_PRETTY_PRINT);
  return $response;
});

// Add application exception handling
$app->error(function(ApplicationException $ex) {
  return new JsonResponse(['error' => $ex->getMessage()],$ex->getCode());
});

// Add other error handling
$app->error(function(Exception $ex) {
  return new JsonResponse([
    'error' => $ex->getMessage(),
    'trace' => explode("\n",$ex->getTraceAsString())
  ]);
});

// Add support for CORS requests
$app->after(function (Request $request, Response $response) {
  $response->headers->set('Access-Control-Allow-Origin','*');
  $response->headers->set('Access-Control-Allow-Headers','Origin, Content-Type, Accept, Authorization, X-Requested-With');
  $response->headers->set('Access-Control-Allow-Methods','GET, POST, PUT, DELETE');
});
$app->options("{anything}", function () {
  return new JsonResponse(null,204);
});

// Create the database
$app['database'] = new PDO(
  "mysql:host=" . $app['settings.db.server'] . ";dbname=" . $app['settings.db.database'],
  $app['settings.db.user'],
  $app['settings.db.password']
 );
$app['database']->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

// Create authorization middleware
$app['authorization'] = function() {
  return new Authorization;
};

// Create the file system
$app['storage'] = function($app) {
  $filesystem = new Filesystem($app['settings.storage']);
  
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
  return new ImageControllerProvider($app['images.repository'],$app['storage']);
};

// Create the controllers
$app->mount('/',$app['users.provider']);
$app->mount('/',$app['images.provider']);

// Run the application
try
{
  $app->run();
}
catch (Throwable $ex)
{
  $response = new JsonResponse([
    'error' => $ex->getMessage(),
    'trace' => explode("\n",$ex->getTraceAsString())
  ]);
  $response->send();
}