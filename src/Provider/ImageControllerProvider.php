<?php
namespace Devi\Provider;

use DateTime;
use Devi\Model\Image\Image;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class ImageControllerProvider implements ControllerProviderInterface
{
  // Validate the owner of an image
  public function validateOwner(Image $image, $authorized)
  {
    // Check if the user is the owner of the image
    if ($authorized === null)
      throw new AccessDeniedHttpException('The specified image cannot be changed');
    if ($image->getUserId() !== $authorized->getId())
      throw new AccessDeniedHttpException('The specified image cannot be changed by this user');
  }
  
  // Validate an uploaded file
  public function validateUploadedFile($file)
  {
    global $app;
    
    if ($file === null)
      throw new BadRequestHttpException('The request did not contain a file to upload');
    if (!$file->isValid())
      throw new BadRequestHttpException('The specified file was not uploaded sucessfully: ' . $file->getErrorMessage());
    if (!array_key_exists($file->getMimeType(),$app['mimetypes']))
      throw new UnsupportedMediaTypeHttpException('The type of the specified file is not supported');
    if ($file->getClientSize() > $file->getMaxFilesize())
      throw new PreconditionFailedHttpException('The specified file was too large; maximum size is ' . $file->getMaxFilesize());
  }
  
  // Get all existing images
  public function getAll(Application $app)
  {
    $images = $app['images']->findAll();
    
    // Return all images
    $json = $app['json_serializer']->serialize($images,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Create a new image
  public function post(Application $app, Request $request)
  {  
    // Validate the uploaded file
    $uploadedFile = $request->files->get('file');
    $this->validateUploadedFile($uploadedFile);
  
    // Create the image
    $image = Image::create($request->request->get('user'));
    $stimage = $image->storedAt($app['storage']);
    $stimage->upload($uploadedFile);
    $app['images']->create($stimage);
    
    // Return the image
     $json = $app['json_serializer']->serialize($stimage,'json');
    return JsonResponse::fromJsonString($json,201);
  }
  
    // Replace an existing image
  public function replace(Application $app, Request $request, Image $image)
  {
    // Validate the image
    $this->validateOwner($image,$request->request->get('user'));
  
    // Validate the uploaded file
    $uploadedFile = $request->files->get('file');
    $this->validateUploadedFile($uploadedFile);

    // Replace the image
    $stimage = $image->storedAt($app['storage']);
    $stimage->upload($uploadedFile);
    $app['images']->update($stimage->setModifiedAt(new DateTime));
    
    // Return the image
    $json = $app['json_serializer']->serialize($stimage,'json');
    return JsonResponse::fromJsonString($json);
  }

  // Get an existing image
  public function get(Application $app, Image $image)
  {
    // Return the image
    $json = $app['json_serializer']->serialize($image,'json');
    return JsonResponse::fromJsonString($json);
  }

  // Update an existing image
  public function patch(Application $app, Request $request, Image $image)
  {
    // Validate the image
    $this->validateOwner($image,$request->request->get('user'));
  
    // Replace the fields
    if ($request->request->has('name'))
      $image->setName($request->request->get('name'));
    if ($request->request->has('public'))
      $image->setPublic((boolean)$request->request->get('public'));
  
    // Update the updated image in the database
    $app['images']->update($image->setModifiedAt(new DateTime));
  
    // Return the image
    $json = $app['json_serializer']->serialize($image,'json');
    return JsonResponse::fromJsonString($json);
  }

  // Delete an existing image
  public function delete(Application $app, Request $request, Image $image)
  {
    // Validate the image
    $this->validateOwner($image,$request->request->get('user'));
  
    // Delete the image
    $app['images']->delete($image);
    
    // Delete the image from the storage
    $app['storage']->delete($image->getId());
  
    // Return the image
    $json = $app['json_serializer']->serialize($image,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Connect to the application
  public function connect(Application $app)
  {    
    // Create controllers
    $controllers = $app['controllers_factory'];
    
    // Create image collection routes
    $controllers->get('/images/',[$this,'getAll'])
      ->before('authorization:optional')
      ->bind('route.images.collection.get');

    // Create image routes
    $controllers->post('/images/',[$this,'post'])
      ->before('authorization:optional')
      ->bind('route.images.collection.post');
    $controllers->post('/images/{image}',[$this,'replace'])
      ->convert('image','images:find')
      ->before('authorization:authorize')
      ->bind('route.images.post');
    $controllers->get('/images/{image}',[$this,'get'])
      ->convert('image','images:find')
      ->before('authorization:optional')
      ->bind('route.images.get');
    $controllers->patch('/images/{image}',[$this,'patch'])
      ->convert('image','images:find')
      ->before('authorization:authorize')
      ->bind('route.images.patch');
    $controllers->delete('/images/{image}',[$this,'delete'])
      ->convert('image','images:find')
      ->before('authorization:authorize')
      ->bind('route.images.delete');    
    
    // Return the controllers
    return $controllers;
  }
}
