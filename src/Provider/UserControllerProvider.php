<?php
namespace Devi\Provider;

use DateTime;
use Devi\Model\User\User;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserControllerProvider implements ControllerProviderInterface
{
  // Validate the current user
  public function validateCurrent(User $user, Request $request)
  {
    // Check if the user is the owner of the image
    if ($request->request->get('user') === null || $user->getName() !== $request->request->get('user')->getName())
      throw new AccessDeniedHttpException('The specified user cannot be changed by this user');
  }
  
  // Check the current user
  public function checkCurrent(User $user, Request $request)
  {
    try
    {
      $this->validateCurrent($user,$request);
      return true;
    } 
    catch (AccessDeniedHttpException $ex) 
    {
      return false;
    }
  }
  
  // Get all extisting users
  public function getAll(Application $app)
  {
    $users = $app['users']->findAll();
    
    // Return all users
    $json = $app['json_serializer']->serialize($users,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Create a new user
  public function post(Application $app, Request $request)
  {
    // Validate the parameters
    if (!$request->request->has('name'))
      throw new BadRequestHttpException('The request did not contain a name');
    if (!$request->request->has('email'))
      throw new BadRequestHttpException('The request did not contain an email address');
    if (!$request->request->has('password'))
      throw new BadRequestHttpException('The request did not contain a password');
  
    // Create the user
    $user = User::create(
      $request->request->get('name'),
      $request->request->get('email'),
      password_hash($request->request->get('password'),PASSWORD_BCRYPT)
    );
  
    // Put the user in the database
    $app['users']->create($user);
    
    // Return the created user
    $json = $app['json_serializer']->serialize($user,'json');
    return JsonResponse::fromJsonString($json,201);
  }
  
  // Get an existing user
  public function get(Application $app, User $user)
  {
    // Return the user
    $json = $app['json_serializer']->serialize($user,'json');
    return JsonResponse::fromJsonString($json);
  }

  // Patch an existing user
  public function patch(Application $app, Request $request, User $user)
  {
    // Validate the user
    $this->validateCurrent($user,$request);

    // Replace the fields
    if ($request->request->has('name'))
      $user->setFileName($request->request->get('name'));
    if ($request->request->has('email'))
      $user->setEmail($request->request->get('email'));
    if ($request->request->has('password'))
      $user->setPassword($request->request->get('password'));
    if ($request->request->has('public'))
      $user->setPublic($request->request->get('public'));

    // Patch the updated user in the database
    $app['users']->update($user->setModifiedAt(new DateTime));

    // Return the user
    $json = $app['json_serializer']->serialize($user,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Delete an existing user
  public function delete(Application $app, Request $request, User $user)
  {
    // Validate the user
    $this->validateCurrent($user,$request);
  
    // Delete the user
    $app['users']->delete($user);
  
    // Return the user
    $json = $app['json_serializer']->serialize($user,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Connect to the application
  public function connect(Application $app)
  {
    // Create controllers
    $controllers = $app['controllers_factory'];
    
    // Create user collection routes
    $controllers->get('/users/',[$this,'getAll'])
      ->before('authorization:optional')
      ->bind('route.users.collection.get');

    // Create user routes
    $controllers->post('/users/',[$this,'post'])
      ->bind('user.collection.post');
    $controllers->get('/users/{user}',[$this,'get'])
      ->convert('user','users:findByName')
      ->before('authorization:optional')
      ->bind('route.users.get');
    $controllers->patch('/users/{user}',[$this,'patch'])
      ->convert('user','users:findByName')
      ->before('authorization:authorize')
      ->bind('route.users.patch');
    $controllers->delete('/users/{user}',[$this,'delete'])
      ->convert('user','users:findByName')
      ->before('authorization:authorize')
      ->bind('route.users.delete');
    
    // Return the controllers
    return $controllers;
  }
}
