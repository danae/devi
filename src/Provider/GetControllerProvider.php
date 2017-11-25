<?php
namespace Devi\Provider;

use Devi\Model\Image\Image;
use Devi\Model\Image\ImageRepositoryInterface;
use Devi\Storage\StorageInterface;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class GetControllerProvider implements ControllerProviderInterface
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
  
  // Respond with an imagine image
  private function respondImage(ImageInterface $image, string $format, string $name, Application $app)
  {
    // Check if the format is supported
    if (!array_key_exists($format,$app['mimetypes']))
      $app->abort(415,'The specified format is not supported');
    
    // Get the contents of the image
    $contents = $image->get($format);

    // Create a new response
    $response = new Response($contents);
    
    // Set the response headers
    $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE,$name);
    $response->headers->set('Content-Disposition',$disposition);
    $response->headers->set('Content-Type',$app['mimetypes'][$format]);
    
    // Return the response
    return $response;
  }
  
  // Raw image
  public function raw(Image $image, string $format, Application $app)
  {
    // Get the MIME type of the image
    $mimetype = $this->storage->getMimeType($image->getId());
    
    // Check if the mimetype equals the requested format
    if ($app['mimetypes'][$format] === $mimetype)
      return $this->storage->respond($image->getId(),$image->getName());
    
    // Get the contents of the stream
    $imagine = $app['imagine'];
    $img = $imagine->read($this->storage->readStream($image->getId()));
    
    //return new JsonResponse(["mimetype" => $mimetype]);
    return $this->respondImage($img,$format,$image->getName(),$app);
  }
  
  // Thumbnail
  public function thumbnail(Image $image, int $width, int $height, Application $app)
  {
    $imagine = $app['imagine'];
    $img = $imagine->read($this->storage->readStream($image->getId()));
    $img = $img->thumbnail(new Box($width,$height),ImageInterface::THUMBNAIL_OUTBOUND);
    
    return $this->respondImage($img,'png',sprintf('%s_%dx%d.png',$image->getName(),$width,$height),$app);
  }
    
  // Connect
  public function connect(Application $app)
  {
    // Create controllers
    $controllers = $app['controllers_factory'];
    
    // Raw image
    $controllers->get('/{image}.{format}',[$this,'raw'])
      ->convert('image',[$this->repository,'find'])
      ->bind('get.image');
    
    // Thumbnail
    $controllers->get('/{image}/thumbnail/{width}x{height}.png',[$this,'thumbnail'])
      ->convert('image',[$this->repository,'find'])
      ->bind('get.thumbnail');
    
    // Return the controllers
    return $controllers;
  }
}
