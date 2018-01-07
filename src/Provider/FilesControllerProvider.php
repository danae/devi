<?php
namespace Devi\Provider;

use Devi\Model\Image\Image;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class FilesControllerProvider implements ControllerProviderInterface
{
  // Respond with an imagine image
  private function respondImage(Application $app, ImageInterface $image, string $format, string $name)
  {
    // Check if the format is supported
    if (!in_array($format,$app['mimetypes']))
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
  public function raw(Application $app, Image $image, string $format)
  {
    // Get the MIME type of the image
    $mimetype = $app['storage']->getMimeType($image->getId());
    
    // Check if the mimetype equals the requested format
    if ($app['mimetypes'][$format] === $mimetype)
      return $app['storage']->respond($image->getId(),$image->getName());
    
    // Get the contents of the stream
    $imagine = $app['imagine'];
    $img = $imagine->read($app['storage']->readStream($image->getId()));
    
    //return new JsonResponse(["mimetype" => $mimetype]);
    return $this->respondImage($app,$img,$format,$image->getName());
  }
  
  // Thumbnail
  public function thumbnail(Application $app, Image $image, int $width, int $height)
  {
    $imagine = $app['imagine'];
    $img = $imagine->read($app['storage']->readStream($image->getId()));
    $img = $img->thumbnail(new Box($width,$height),ImageInterface::THUMBNAIL_OUTBOUND);
    
    return $this->respondImage($app,$img,'png',sprintf('%s_%dx%d.png',$image->getName(),$width,$height));
  }
    
  // Connect
  public function connect(Application $app)
  {
    // Create controllers
    $controllers = $app['controllers_factory'];
    
    // Raw image
    $controllers->get('/files/{image}.{format}',[$this,'raw'])
      ->convert('image','images:find')
      ->bind('route.files.image');
    
    // Thumbnail
    $controllers->get('/files/{image}/{width}x{height}.png',[$this,'thumbnail'])
      ->convert('image','images:find')
      ->bind('route.files.thumbnail');
    
    // Return the controllers
    return $controllers;
  }
}
