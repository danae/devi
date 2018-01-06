<?php
namespace Devi\Provider;

use DateTime;
use Devi\Model\Album\Album;
use Devi\Model\Album\AlbumRepositoryInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Serializer\Serializer;

class AlbumControllerProvider implements ControllerProviderInterface
{
  // Variables
  private $repository;
  private $serializer;
  
  // Constructor
  public function __construct(AlbumRepositoryInterface $repository, Serializer $serializer)
  {
    $this->repository = $repository;
    $this->serializer = $serializer;
  }
  
  // Validate the owner of an album
  public function validateOwner(Album $album, $authorized)
  {
    // Check if the user is the owner of the album
    if ($authorized === null)
      throw new AccessDeniedHttpException('The specified album cannot be changed');
    if ($album->getUserId() !== $authorized->getId())
      throw new AccessDeniedHttpException('The specified album cannot be changed by this user');
  }
  
  // Get all extisting albums
  public function getAll()
  {
    // Return all users
    $json = $this->serializer->serialize($this->repository->findAll(),'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Create a new album
  public function post(Request $request)
  {
    // Create the album
    $album = Album::create($request->request->get('user'));
  
    // Put the album in the database
    $this->repository->create($album);
    
    // Return the created user
    $json = $this->serializer->serialize($album,'json');
    return JsonResponse::fromJsonString($json,201);
  }
  
  // Get an existing album
  public function get($album)
  {
    // Return the user
    $json = $this->serializer->serialize($album,'json');
    return JsonResponse::fromJsonString($json);
  }

  // Patch an existing album
  public function patch($album, Request $request)
  {
    // Validate the album
    $this->validateOwner($album,$request->request->get('user'));
  
    // Replace the fields
    if ($request->request->has('name'))
      $album->setName($request->request->get('name'));
    if ($request->request->has('public'))
      $album->setPublic((boolean)$request->request->get('public'));
  
    // Update the updated album in the database
    $this->repository->update($album->setModifiedAt(new DateTime));
  
    // Return the image
    $json = $this->serializer->serialize($album,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Delete an existing album
  public function delete($album, Request $request)
  {
    // Validate the user
    $this->validateOwner($album,$request->request->get('user'));
  
    // Delete the user
    $this->repository->delete($album);
  
    // Return the user
    $json = $this->serializer->serialize($album,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Connect to the application
  public function connect(Application $app)
  {
    // Get the authorization
    $authorization = $app['authorization'];
    
    // Create controllers
    $controllers = $app['controllers_factory'];
    
    // Create album collection routes
    $controllers
      ->get('/',[$this,'getAll'])
      ->before([$authorization,'optional'])
      ->bind('album.collection.get');

    // Create album routes
    $controllers
      ->post('/',[$this,'post'])
      ->before([$authorization,'authorize'])
      ->bind('album.collection.post');
    $controllers
      ->get('/{album}',[$this,'get'])
      ->convert('album',[$this->repository,'find'])
      ->before([$authorization,'optional'])
      ->bind('album.get');
    $controllers
      ->patch('/{album}',[$this,'patch'])
      ->convert('album',[$this->repository,'find'])
      ->before([$authorization,'authorize'])
      ->bind('album.patch');
    $controllers
      ->delete('/{album}',[$this,'delete'])
      ->convert('album',[$this->repository,'find'])
      ->before([$authorization,'authorize'])
      ->bind('album.delete');
    
    // Return the controllers
    return $controllers;
  }
}
