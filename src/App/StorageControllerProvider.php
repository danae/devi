<?php

namespace Devi\App;

use Devi\Model\Image\ImageRepositoryInterface;
use Devi\Storage\StorageInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class StorageControllerProvider implements ControllerProviderInterface
{
  // Variables
  private $repository;
  private $storage;
  
  // Constructor
  public function __construct(ImageRepositoryInterface $repository, StorageInterface $storage)
  {
    $this->repository = $repository;
    $this->storage = $storage;
  }
  
  // Validate image
  public function validate(Request $request)
  {
    $image = $request->attributes->get('image');
    $image = $this->repository->find($image);
    
    // Check if the image exists
    if ($image === null)
      throw new ApplicationException('The specified image was not found',404);
    
    // Set the attribute
    $request->attributes->set('image',$image);
  }
  
  // Raw image
  public function raw($format, Request $request, Application $app)
  {
    $image = $request->attributes->get('image');
    
    // Check if the mime type is supported
    if (!in_array($image->getContentType(),$app['mimetypes']))
      $app->abort(415,'The content type of the image is not supported');
            
    // Check if the mimetype equals the requested format
    //if ($app['mimetypes'] === $format)
    //  return $image->respond($this->storage);
    
    // Return the raw data
    return $image->respond($this->storage);
  }
    
  // Connect
  public function connect(Application $app)
  {
    // Create controllers
    $controllers = $app['controllers_factory']
      ->before([$this,'validate']);
    
    // Raw image
    $controllers->get('/raw.{format}',[$this,'raw']);
    
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
