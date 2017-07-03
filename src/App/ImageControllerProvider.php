<?php
namespace Devi\App;

use DateTime;
use Devi\Authorization\AuthorizationInterface;
use Devi\Model\Image\Image;
use Devi\Model\Image\ImageRepositoryInterface;
use Devi\Model\Storage\StorageInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;

class ImageControllerProvider implements ControllerProviderInterface
{
  // Variables
  private $authorization;
  private $repository;
  private $serializer;
  private $storage;
  
  // Constructor
  public function __construct(AuthorizationInterface $authorization, ImageRepositoryInterface $repository, Serializer $serializer, StorageInterface $storage)
  {
    $this->authorization = $authorization;
    $this->repository = $repository;
    $this->serializer = $serializer;
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
    if (!array_key_exists($file->getMimeType(),$app['mimetypes']))
      throw new ApplicationException('The type of the specified file is not supported',415);
    if ($file->getClientSize() > $file->getMaxFilesize())
      throw new ApplicationException('The specified file was too large; maximum size is ' . $file->getMaxFilesize(),413);
  }
  
  // Get all extisting images
  public function getAll()
  {
    // Return all images
    $json = $this->serializer->serialize($this->repository->findAll(),'json');
    return JsonResponse::fromJsonString($json);
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
    $this->repository->create($image);
    
    // Return the created image
    $json = $this->serializer->serialize($image,'json');
    return JsonResponse::fromJsonString($json,201);
  }
  
  // Replace an existing image
  public function replace($image, Request $request)
  {
    // Validate the image
    $this->validate($image);
    $this->validateOwner($image,$request->request->get('user'));
  
    // Validate the file
    $file = $request->files->get('file');
    $this->validateFile($file);

    // Replace the image
    $image->upload($this->storage,$file);
    $this->repository->update($image->setModifiedAt(new DateTime));
    
    // Return the image
    $json = $this->serializer->serialize($image,'json');
    return JsonResponse::fromJsonString($json);
  }

  // Get an existing image
  public function get($image)
  {
    // Validate the image
    $this->validate($image);
    
    // Return the image
    $json = $this->serializer->serialize($image,'json');
    return JsonResponse::fromJsonString($json);
  }

  // Update an existing image
  public function patch($image, Request $request)
  {
    // Validate the image
    $this->validate($image);
    $this->validateOwner($image,$request->request->get('user'));
  
    // Replace the fields
    if ($request->request->has('file_name'))
      $image->setFileName($request->request->get('file_name'));
    if ($request->request->has('public'))
      $image->setPublic((boolean)$request->request->get('public'));
  
    // Patch the updated image in the database
    $this->repository->update($image->setModifiedAt(new DateTime));
  
    // Return the image
    $json = $this->serializer->serialize($image,'json');
    return JsonResponse::fromJsonString($json);
  }

  // Delete an existing image
  public function delete($image, Request $request)
  {
    // Validate the image
    $this->validate($image);
    $this->validateOwner($image,$request->request->get('user'));
  
    // Delete the image
    $this->repository->delete($image);
    
    // Delete the image from the storage
    $this->storage->delete($image->getId());
  
    // Return the image
    $json = $this->serializer->serialize($image,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get the raw data of an existing image
  public function getRaw($image)
  {
    // Validate the image
    $this->validate($image);
    
    // Return the raw data
    return $image->respond($this->storage);
  }
  
  // Connect to the application
  public function connect(Application $app)
  {    
    // Create controllers
    $controllers = $app['controllers_factory'];
    
    // Create image collection routes
    $controllers
      ->get('/',[$this,'getAll'])
      ->before([$this->authorization,'optional']);

    // Create image routes
    $controllers
      ->post('/',[$this,'post'])
      ->before([$this->authorization,'authorize']);
    $controllers
      ->post('/{image}',[$this,'replace'])
      ->convert('image',[$this->repository,'find'])
      ->before([$this->authorization,'authorize']);
    $controllers
      ->get('/{image}',[$this,'get'])
      ->convert('image',[$this->repository,'find'])
      ->before([$this->authorization,'optional']);
    $controllers
      ->patch('/{image}',[$this,'patch'])
      ->convert('image',[$this->repository,'find'])
      ->before([$this->authorization,'authorize']);
    $controllers
      ->delete('/{image}',[$this,'delete'])
      ->convert('image',[$this->repository,'find'])
      ->before([$this->authorization,'authorize']);
    
    
    // Create raw image routes
    $controllers
      ->get('/{image}/raw',[$this,'getRaw'])
      ->convert('image',[$this->repository,'find'])
      ->before([$this->authorization,'optional']);
    
    // Return the controllers
    return $controllers;
  }
}
