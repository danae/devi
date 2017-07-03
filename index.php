<?php
require "vendor/autoload.php";

use Devi\App\ImageControllerProvider;
use Devi\App\UserControllerProvider;
use Devi\Authorization\Authorization;
use Devi\Model\Image\ImageDatabaseNormalizer;
use Devi\Model\Image\ImageOutputNormalizer;
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
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
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
$app['storage'] = function($app) {
  $filesystem = new Filesystem($app['storage.backend']);
  
  return new GzipWrapper(
    new Flysystem($filesystem,"image-%s.gz"));
};

// Create serializer callbacks
$app['callbacks.date'] = function($app) {
  return function($dateTime) {
    return $dateTime instanceof DateTime ? $dateTime->format(DateTime::ISO8601) : '';
  };
};
$app['callbacks.user'] = function($app) {
  return function($userId) use($app) {
    return $app['users.repository']->find($userId);
  };
};

// Create the user serializers
$app['images.serializer.database'] = function() {
  return new Serializer([new UserDatabaseNormalizer,new DateTimeNormalizer],[new JsonEncoder]);
};
$app['images.serializer.api'] = function() {
  return new Serializer([new UserApiNormalizer,new DateTimeNormalizer],[new JsonEncoder]);
};

$app['users.serializer'] = function($app) {  
  $objectNormalizer = new ObjectNormalizer(null,new CamelCaseToSnakeCaseNameConverter,null,new ReflectionExtractor);
  $objectNormalizer->setIgnoredAttributes(['id','password','public_key','private_key']);
  $objectNormalizer->setCallbacks(['dateCreated' => $app['callbacks.date'],'dateModified' => $app['callbacks.date']]);
  
  $jsonEncoder = new JsonEncoder;
  
  return new Serializer([$objectNormalizer],[$jsonEncoder]);
};

// Create the image serializers
$app['images.serializer.database'] = function() {
  return new Serializer([new ImageDatabaseNormalizer,new DateTimeNormalizer],[new JsonEncoder]);
};
$app['images.serializer.output'] = function() {
  return new Serializer([new ImageOutputNormalizer,new DateTimeNormalizer],[new JsonEncoder]);
};

// Create the repositories and providers
$app['users.repository'] = function($app) {
  return new UserRepository($app['database'],'users',$app['users.serializer']);
};
$app['users.provider'] = function($app) {
  return new UserControllerProvider($app['users.repository'],$app['users.serializer']);
};
$app['images.repository'] = function($app) {
  return new ImageRepository($app['database'],'images',$app['images.serializer.database']);
};
$app['images.provider'] = function($app) {
  return new ImageControllerProvider($app['images.repository'],$app['images.serializer.output'],$app['storage']);
};

// Create the controllers
$app->mount('/users',$app['users.provider']);
$app->mount('/images',$app['images.provider']);

// Run the application
$app->run();