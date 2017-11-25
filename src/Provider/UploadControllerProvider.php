<?php
namespace Devi\Provider;

use DateTime;
use Devi\Model\Image\Image;
use Devi\Model\Image\ImageRepositoryInterface;
use Devi\Storage\StorageInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Serializer\Serializer;

class UploadControllerProvider implements ControllerProviderInterface
{
  // Variables
  private $repository;
  private $storage;
  private $serializer;
  
  // Constructor
  public function __construct(ImageRepositoryInterface $repository, StorageInterface $storage, Serializer $serializer)
  {
    $this->repository = $repository;
    $this->storage = $storage;
    $this->serializer = $serializer;
  }
  
  // Validate an uploaded file
  public function validateUploadedFile($file)
  {
    global $app;
    
    if ($file === null)
      throw new BadRequestHttpException('The request did not contain a file to upload');
    if (!$file->isValid())
      throw new BadRequestHttpException('The specified file was not uploaded sucessfully: ' . $file->getErrorMessage());
    if (!in_array($file->getMimeType(),$app['mimetypes']))
      throw new UnsupportedMediaTypeHttpException('The type of the specified file is not supported');
    if ($file->getClientSize() > $file->getMaxFilesize())
      throw new PreconditionFailedHttpException('The specified file was too large; maximum size is ' . $file->getMaxFilesize());
  }
  
  // Create a new image
  public function upload(Request $request)
  {  
    // Validate the uploaded file
    $uploadedFile = $request->files->get('file');
    $this->validateUploadedFile($uploadedFile);
  
    // Create the image
    $image = Image::create($request->request->get('user'));
    $stimage = $image->storedAt($this->storage);
    $stimage->upload($uploadedFile);
    $this->repository->create($stimage);
    
    // Return the image
    $json = $this->serializer->serialize($stimage,'json');
    return JsonResponse::fromJsonString($json,201);
  }
  
  // Replace an existing image
  public function replace(Image $image, Request $request)
  {
    // Validate the image
    $this->validateOwner($image,$request->request->get('user'));
  
    // Validate the uploaded file
    $uploadedFile = $request->files->get('file');
    $this->validateUploadedFile($uploadedFile);

    // Replace the image
    $stimage = $image->storedAt($this->storage);
    $stimage->upload($this->storage,$uploadedFile);
    $this->repository->update($stimage->setModifiedAt(new DateTime));
    
    // Return the image
    $json = $this->serializer->serialize($stimage,'json');
    return JsonResponse::fromJsonString($json);
  }
    
  // Connect
  public function connect(Application $app)
  {
    // Get the authorization
    $authorization = $app['authorization'];

    // Create controllers
    $controllers = $app['controllers_factory'];
    
    // Upload image
    $controllers
      ->post('/',[$this,'upload'])
      ->before([$authorization,'authorize'])
      ->bind('upload.new');
    
    // Replace image
    $controllers
      ->post('/{image}',[$this,'replace'])
      ->convert('image',[$this->repository,'find'])
      ->before([$authorization,'authorize'])
      ->bind('upload.replace');
    
    // Return the controllers
    return $controllers;
  }
}
