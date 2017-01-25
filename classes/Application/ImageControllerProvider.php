<?php
namespace Gallerie\Application;

use DateTime;
use Gallerie\Model\Image;
use Gallerie\Model\ImageRepositoryInterface;
use Gallerie\Model\ImageStorageInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ImageControllerProvider implements ControllerProviderInterface
{
  // Variables
  private $repo;
  private $storage;
  
  // Constructor
  public function __construct(ImageRepositoryInterface $repo, ImageStorageInterface $storage)
  {
    $this->repo = $repo;
    $this->storage = $storage;
  }
  
  // Validate an image
  public function validate($image)
  {  
    // Check if the image exists
    if ($image === null)
      throw new ApplicationException('The specified image was not found',404);
  }
  
  // Validate the owner of an image
  public function validateOwner(Image $image, $authorized)
  {
    // Check if the user is the owner of the image
    if ($authorized === null)
      throw new ApplicationException('The specified image cannot be changed',403);
    if ($image->getUserId() !== $authorized->getId())
      throw new ApplicationException('The specified image cannot be changed by this user',403);
  }
  
  // Validate an uploaded file
  public function validateFile($file)
  {
    global $app;
    
    if ($file === null)
      throw new ApplicationException('The request did not contain a file to upload',400);
    if (!$file->isValid())
      throw new ApplicationException('The specified file was not uploaded sucessfully: ' . $file->getErrorMessage(),400);
    if (!array_key_exists($file->getMimeType(),$app['settings.mimetypes']))
      throw new ApplicationException('The type of the specified file is not supported',415);
    if ($file->getClientSize() > $file->getMaxFilesize())
      throw new ApplicationException('The specified file was too large; maximum size is ' . $file->getMaxFilesize(),413);
  }
  
  // Get all extisting images
  public function getAll()
  {
    // Return all images
    return new JsonResponse($this->repo->getAll());
  }
  
  // Create a new image
  public function post(Request $request)
  {  
    // Validate the file
    $file = $request->files->get('file');
    $this->validateFile($file);
  
    // Create the image
    $image = Image::create($request->request->get('user'));
    $image->upload($this->storage,$file);
    $this->repo->put($image);
    
    // Return the created image
    return new JsonResponse($image,201);
  }

  // Get an existing image
  public function get($image)
  {
    // Validate the image
    $this->validate($image);
    
    // Return the image
    return new JsonResponse($image);
  }

  // Update an existing image
  public function patch($image, Request $request)
  {
    // Validate the image
    $this->validate($image);
    $this->validateOwner($image,$request->request->get('user'));
  
    // Replace the fields
    if ($request->request->has('file_name'))
      $image->withFileName($request->request->get('file_name'));
    if ($request->request->has('public'))
      $image->withPublic((boolean)$request->request->get('public'));
  
    // Patch the updated image in the database
    $this->repo->patch($image->withDateModified(new DateTime));
  
    // Return the image
    return new JsonResponse($image);
  }

  // Delete an existing image
  public function delete($image, Request $request)
  {
    // Validate the image
    $this->validate($image);
    $this->validateOwner($image,$request->request->get('user'));
  
    // Delete the image
    $this->repo->delete($image);
    
    // Delete the image from the storage
    $this->storage->delete($image->getName());
  
    // Return the image
    return new JsonResponse($image);
  }
  
  // Replace the raw data of an existing image
  public function postRaw($image, Request $request)
  {
    // Validate the image
    $this->validate($image);
    $this->validateOwner($image,$request->request->get('user'));
  
    // Validate the file
    $file = $request->files->get('file');
    $this->validateFile($file);

    // Replace the image
    $image->upload($this->storage,$file);
    $this->repo->patch($image->withDateModified(new DateTime));
    
    // Return the image
    return new JsonResponse($image);
  }
  
  // Get the raw data of an existing image
  public function getRaw($image)
  {
    // Validate the image
    $this->validate($image);
    
    // Return the raw data
    return $image->response($this->storage);
  }
  
  // Connect to the application
  public function connect(Application $app)
  {    
    // Create controllers
    $controllers = $app['controllers_factory']
      ->convert('image',[$this->repo,'getByName']);
    
    // Create image collection routes
    $controllers
      ->get('/images',[$this,'getAll'])
      ->before('authorization:optional');

    // Create image routes
    $controllers
      ->post('/images',[$this,'post'])
      ->before('authorization:authorize');
    $controllers
      ->get('/images/{image}',[$this,'get'])
      ->before('authorization:optional');
    $controllers
      ->patch('/images/{image}',[$this,'patch'])
      ->before('authorization:authorize');
    $controllers
      ->delete('/images/{image}',[$this,'delete'])
      ->before('authorization:authorize');

    // Create raw image routes
    $controllers
      ->post('/images/{image}/raw',[$this,'postRaw'])
      ->before('authorization:authorize');
    $controllers
      ->get('/images/{image}/raw',[$this,'getRaw'])
      ->before('authorization:optional');
    
    // Return the controllers
    return $controllers;
  }
}
