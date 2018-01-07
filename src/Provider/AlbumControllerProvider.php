<?php
namespace Devi\Provider;

use DateTime;
use Devi\Model\Album\Album;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AlbumControllerProvider implements ControllerProviderInterface
{
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
  public function getAll(Application $app)
  {
    $albums = $app['albums']->findAll();
    
    // Return all users
    $json = $app['json_serializer']->serialize($albums,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Create a new album
  public function post(Application $app, Request $request)
  {
    // Create the album
    $album = Album::create($request->request->get('user'));
  
    // Put the album in the database
    $this->repository->create($album);
    
    // Return the created user
    $json = $app['json_serializer']->serialize($album,'json');
    return JsonResponse::fromJsonString($json,201);
  }
  
  // Get an existing album
  public function get(Application $app, Album $album)
  {
    // Return the user
    $json = $app['json_serializer']->serialize($album,'json');
    return JsonResponse::fromJsonString($json);
  }

  // Patch an existing album
  public function patch(Application $app, Request $request, Album $album)
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
  
    // Return the album
    $json = $app['json_serializer']->serialize($album,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Delete an existing album
  public function delete(Application $app, Request $request, Album $album)
  {
    // Validate the user
    $this->validateOwner($album,$request->request->get('user'));
  
    // Delete the user
    $this->repository->delete($album);
  
    // Return the user
    $json = $app['json_serializer']->serialize($album,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Connect to the application
  public function connect(Application $app)
  {
    // Create controllers
    $controllers = $app['controllers_factory'];
    
    // Create album collection routes
    $controllers
      ->get('/albums/',[$this,'getAll'])
      ->before('authorization:optional')
      ->bind('route.albums.collection.get');

    // Create album routes
    $controllers->post('/albums/',[$this,'post'])
      ->before('authorization:authorize')
      ->bind('route.albums.collection.post');
    $controllers ->get('/{album}',[$this,'get'])
      ->convert('album','albums:find')
      ->before('authorization:optional')
      ->bind('route.albums.get');
    $controllers->patch('/{album}',[$this,'patch'])
      ->convert('album','albums:find')
      ->before('authorization:authorize')
      ->bind('route.albums.patch');
    $controllers->delete('/{album}',[$this,'delete'])
      ->convert('album','albums:find')
      ->before('authorization:authorize')
      ->bind('route.albums.delete');
    
    // Return the controllers
    return $controllers;
  }
}
