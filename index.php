<?php
require "vendor/autoload.php";

use Devi\App\ApplicationException;
use Devi\App\ImageControllerProvider;
use Devi\App\StorageControllerProvider;
use Devi\App\UserControllerProvider;
use Devi\Authorization\Authorization;
use Devi\Model\Image\ImageRepository;
use Devi\Model\Storage\Flysystem;
use Devi\Model\Storage\GzipWrapper;
use Devi\Model\User\UserRepository;
use Devi\Utils\Database;
use League\Flysystem\Filesystem;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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

// Add exception handling
$app->error(function(Exception $ex) {
  return new JsonResponse([
    'error' => $ex->getMessage()
  ],$ex instanceof ApplicationException ? $ex->getCode() : 500);
});

// Add support for CORS requests
$app->options("{anything}", function () {
  return new JsonResponse(null,204);
});

// Create the database service
$app['database'] = function($app) {
  return new Database("mysql:host=" . $app['db.server'] . ";dbname=" . $app['db.database'],$app['db.user'],$app['db.password']);
};

// Create the file system
$app['storage'] = function($app) {
  $filesystem = new Filesystem($app['storage.backend']);
  
  return new GzipWrapper(
    new Flysystem($filesystem,"image-%s.gz"));
};

// Create the serializer
$app['serializer'] = function() {
  return new Serializer([new CustomNormalizer,new DateTimeNormalizer('Y-m-d H:i:s'),new ObjectNormalizer],[new JsonEncoder]);
};

// Create authorization
$app['authorization'] = function($app) {
  return new Authorization($app['users.repository']);
};

// Create the repositories and providers
$app['users.repository'] = function($app) {
  return new UserRepository($app['database'],'users',$app['serializer']);
};
$app['users.provider'] = function($app) {
  return new UserControllerProvider($app['authorization'],$app['users.repository'],$app['serializer']);
};
$app['images.repository'] = function($app) {
  return new ImageRepository($app['database'],'images',$app['serializer']);
};
$app['images.provider'] = function($app) {
  return new ImageControllerProvider($app['authorization'],$app['images.repository'],$app['serializer'],$app['storage']);
};
$app['storage.provider'] = function($app) {
  return new StorageControllerProvider($app['storage']);
};

// Create the controllers
$app->mount('/users',$app['users.provider']);
$app->mount('/images',$app['images.provider']);
//$app->mount('/images/{image}',$app['storage.provider']);

// Run the application
$app->run();