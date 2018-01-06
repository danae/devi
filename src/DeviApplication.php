<?php
namespace Devi;

use Devi\Database\Database;
use Devi\Model\Album\AlbumRepository;
use Devi\Model\Image\ImageRepository;
use Devi\Model\User\UserRepository;
use Devi\Storage\FlysystemStorage;
use Devi\Storage\GzipStorageWrapper;
use League\Flysystem\Filesystem;
use Silex\Application;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class DeviApplication extends Application
{
  // Constructor
  public function __construct(array $values = [])
  {
    // Call the parent constructor
    parent::__construct($values);
    
    // Create the file system
    $this['storage'] = function($app) {
      $filesystem = new Filesystem($app['backend']);
  
      return new GzipStorageWrapper(
        new FlysystemStorage($filesystem));
    };

    // Create the database service
    $this['database'] = function($app) {
      return new Database("mysql:host=" . $app['db.server'] . ";dbname=" . $app['db.database'],$app['db.user'],$app['db.password']);
    };
    
    // Create the serializer for the models
    $this['serializer'] = function() { 
      return new Serializer([new DateTimeNormalizer('Y-m-d H:i:s'),new GetSetMethodNormalizer],[]);
    };

    // Create the repositories for the models
    $this['users'] = function($app) {
      return new UserRepository($app['database'],'users',$app['serializer']);
    };
    $this['images'] = function($app) {
      return new ImageRepository($app['database'],'images',$app['serializer']);
    };
    $this['albums'] = function($app) {
      return new AlbumRepository($app['database'],'albums',$app['serializer']);
    };
  }
}
