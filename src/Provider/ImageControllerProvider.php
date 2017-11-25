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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Serializer\Serializer;

class ImageControllerProvider implements ControllerProviderInterface
{
  // Variables
  private $repository;
  private $serializer;
  private $storage;
  
  // Constructor
  public function __construct(ImageRepositoryInterface $repository, Serializer $serializer, StorageInterface $storage)
  {
    $this->repository = $repository;
    $this->serializer = $serializer;
    $this->storage = $storage;
  }
  
  // Validate the owner of an image
  public function validateOwner(Image $image, $authorized)
  {
    // Check if the user is the owner of the image
    if ($authorized === null)
      throw new AccessDeniedHttpException('The specified image cannot be changed');
    if ($image->getUserId() !== $authorized->getId())
      throw new AccessDeniedHttpException('The specified image cannot be changed by this user');
  }
  
  // Get all existing images
  public function getAll()
  {
    // Return all images
    $json = $this->serializer->serialize($this->repository->findAll(),'json');
    return JsonResponse::fromJsonString($json);
  }

  // Get an existing image
  public function get(Image $image)
  {
    // Return the image
    $json = $this->serializer->serialize($image,'json');
    return JsonResponse::fromJsonString($json);
  }

  // Update an existing image
  public function patch(Image $image, Request $request)
  {
    // Validate the image
    $this->validateOwner($image,$request->request->get('user'));
  
    // Replace the fields
    if ($request->request->has('name'))
      $image->setName($request->request->get('name'));
    if ($request->request->has('public'))
      $image->setPublic((boolean)$request->request->get('public'));
  
    // Update the updated image in the database
    $this->repository->update($image->setModifiedAt(new DateTime));
  
    // Return the image
    $json = $this->serializer->serialize($image,'json');
    return JsonResponse::fromJsonString($json);
  }

  // Delete an existing image
  public function delete(Image $image, Request $request)
  {
    // Validate the image
    $this->validateOwner($image,$request->request->get('user'));
  
    // Delete the image
    $this->repository->delete($image);
    
    // Delete the image from the storage
    $this->storage->delete($image->getId());
  
    // Return the image
    $json = $this->serializer->serialize($image,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Connect to the application
  public function connect(Application $app)
  {    
    // Get the authorization
    $authorization = $app['authorization'];

    // Create controllers
    $controllers = $app['controllers_factory'];
    
    // Create image collection routes
    $controllers
      ->get('/',[$this,'getAll'])
      ->before([$authorization,'optional'])
      ->bind('image.collection.get');

    // Create image routes
    $controllers
      ->get('/{image}',[$this,'get'])
      ->convert('image',[$this->repository,'find'])
      ->before([$authorization,'optional'])
      ->bind('image.get');
    $controllers
      ->patch('/{image}',[$this,'patch'])
      ->convert('image',[$this->repository,'find'])
      ->before([$authorization,'authorize'])
      ->bind('image.patch');
    $controllers
      ->delete('/{image}',[$this,'delete'])
      ->convert('image',[$this->repository,'find'])
      ->before([$authorization,'authorize'])
      ->bind('image.delete');
    
    // Return the controllers
    return $controllers;
  }
}
