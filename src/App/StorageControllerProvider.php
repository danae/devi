<?php

namespace Devi\App;

use Devi\Model\Storage\StorageInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class StorageControllerProvider implements ControllerProviderInterface
{
  // Variables
  private $storage;
  
  // Constructor
  public function __construct(StorageInterface $storage)
  {
    $this->storage = $storage;
  }
  
  
  
  // Connect
  public function connect(Application $app)
  {
    // Create controllers
    $controllers = $app['controllers_factory'];
    
    $controllers->get('/thumbnail/{width}x{height}.png',function($width, $height, Request $request)
    {
      $image = $request->attributes->get('image');
      return new JsonResponse([
        'image' => $image,
        'width' => $width,
        'height' => $height
      ]);
    });
    
    // Return the controllers
    return $controllers;
  }
}
