<?php
require "vendor/autoload.php";

use Devi\Authorization\Authorization;
use Devi\Model\Album\AlbumRepository;
use Devi\Model\Image\ImageRepository;
use Devi\Model\User\UserRepository;
use Devi\Provider\AlbumControllerProvider;
use Devi\Provider\GetControllerProvider;
use Devi\Provider\ImageControllerProvider;
use Devi\Provider\UploadControllerProvider;
use Devi\Provider\UserControllerProvider;
use Devi\Storage\Flysystem;
use Devi\Storage\GzipWrapper;
use Devi\Utils\Database;
use Imagine\Gd\Imagine;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
use League\Flysystem\Filesystem;
use Silex\Application;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

// Register error handlers
ErrorHandler::register();

// Create the application with the settings
$app = new Application(require('settings.php'));

// Parse the request body if JSON
$app->before(function(Request $request) {
  if (strpos($request->headers->get('Content-Type'),'application/json') === 0) {
    $data = json_decode($request->getContent(),true);
    $request->request->replace(is_array($data) ? $data : []);
  }
});

// Pretty print the JSON response
$app->after(function(Request $request, Response $response) {
  if ($response instanceof JsonResponse)
    $response->setEncodingOptions(JSON_PRETTY_PRINT);
  return $response;
});

// Add exception handling
$app->error(function(Exception $ex) {
  return new JsonResponse([
    'error' => $ex->getMessage(),
    'exceptionThrown' => get_class($ex),
    'trace' => $ex->getTraceAsString()
  ],$ex instanceof HttpException ? $ex->getStatusCode() : 500);
});

// Add support for CORS requests
$app->register(new CorsServiceProvider);
$app->after($app['cors']);

// Create the database service
$app['database'] = function($app) {
  return new Database("mysql:host=" . $app['db.server'] . ";dbname=" . $app['db.database'],$app['db.user'],$app['db.password']);
};

// Create the file system
$app['storage'] = function($app) {
  $filesystem = new Filesystem($app['storage.backend']);
  
  return new GzipWrapper(
    new Flysystem($filesystem));
};

// Create the serializer
$app['serializer'] = function() { 
  return new Serializer([new DateTimeNormalizer('Y-m-d H:i:s'),new GetSetMethodNormalizer],[]);
};
$app['serializer.display'] = function() {
  return new Serializer([new DateTimeNormalizer(DateTime::ISO8601),new CustomNormalizer],[new JsonEncoder]);
};

// Create authorization
$app['authorization'] = function($app) {
  return new Authorization($app['users.repository']);
};

// Create the imagine interface
$app['imagine'] = function() {
  return new Imagine;
};

// Create the user provider
$app['users.repository'] = function($app) {
  return new UserRepository($app['database'],'users',$app['serializer']);
};
$app['users.provider'] = function($app) {
  return new UserControllerProvider($app['users.repository'],$app['serializer.display']);
};

// Create the image provider
$app['images.repository'] = function($app) {
  return new ImageRepository($app['database'],'images',$app['serializer']);
};
$app['images.provider'] = function($app) {
  return new ImageControllerProvider($app['images.repository'],$app['serializer.display'],$app['storage']);
};

// Create the album provider
$app['albums.repository'] = function($app) {
  return new AlbumRepository($app['database'],'albums',$app['serializer']);
};
$app['albums.provider'] = function($app) {
  return new AlbumControllerProvider($app['albums.repository'],$app['serializer.display']);
};

// Create the get provider
$app['get.provider'] = function($app) {
  return new GetControllerProvider($app['images.repository'],$app['storage']);
};

// Create the upload provider
$app['upload.provider'] = function($app) {
  return new UploadControllerProvider($app['images.repository'],$app['storage'],$app['serializer.display']);
};

// Create the controllers
$app->mount('/users',$app['users.provider']);
$app->mount('/images',$app['images.provider']);
$app->mount('/albums',$app['albums.provider']);
$app->mount('/get',$app['get.provider']);
$app->mount('/upload',$app['upload.provider']);

// Run the application
$app->run();