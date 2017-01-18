<?php
namespace Gallerie\Application;

use DateTime;
use Gallerie\Model\User;
use Gallerie\Model\UserRepositoryInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserControllerProvider implements ControllerProviderInterface
{
  // Variables
  private $repo;
  
  // Constructor
  public function __construct(UserRepositoryInterface $repo)
  {
    $this->repo = $repo;
  }
  
  // Validate the user
  private function validate($user)
  {
    // Check if the user exists
    if ($user === null)
      throw new ApplicationException('The specified user was not found',404);
  }
  
  // Validate the current user
  public function validateCurrent(User $user, User $authorized)
  {
    // Check if the user is the owner of the art
    if ($user->getId() !== $authorized->getId())
      throw new ApplicationException('The specified user cannot be changed by this user',403);
  }
  
  // Check the current user
  public function checkCurrent(User $user, $authorized)
  {
    try
    {
      if ($authorized === null)
        return false;
      
      $this->validateCurrent($user,$authorized);
      return true;
    } 
    catch (ApplicationException $ex) 
    {
      return false;
    }
  }
  
  // POST: Create a new user
  public function post(Request $request)
  {
    // Validate the parameters
    if (!$request->request->has('name'))
      throw new ApplicationException('The request did not contain a name',400);
    if (!$request->request->has('email'))
      throw new ApplicationException('The request did not contain an email address',400);
    if (!$request->request->has('password'))
      throw new ApplicationException('The request did not contain a password',400);
  
    // Create the user
    $user = User::create(
      $request->request->get('name'),
      $request->request->get('email'),
      $request->request->get('password')
    );
  
    // Put the user in the database
    $this->repo->put($user);
    
    // Return the created user
    return new JsonResponse($user,201);
  }
  
  // Get an existing user
  public function get($user)
  {
    $this->validate($user);
    return new JsonResponse($user);
  }

  // Patch an existing user
  public function patch($user, Request $request)
  {
    // Validate the user
    $this->validate($user);
    $this->validateCurrent($user,$request->request->get('user'));

    // Replace the fields
    if ($request->request->has('name'))
      $user->withName($request->request->get('name'));
    if ($request->request->has('email'))
      $user->withEmail($request->request->get('email'));
    if ($request->request->has('password'))
      $user->withPassword($request->request->get('password'));
    if ($request->request->has('public'))
      $user->withPublic($request->request->get('public'));

    // Patch the updated user in the database
    $this->repo->patch($user->withDateModified(new DateTime));

    // Return the user
    return new JsonResponse($user);
  }
  
  // Delete an existing user
  public function delete($user, Request $request)
  {
    // Validate the user
    $this->validate($user);
    $this->validateCurrent($user,$request->request->get('user'));
  
    // Delete the user
    $this->repo->delete($user);
  
    // Return the user
    return new JsonResponse($user);
  }
  
  // Get all art of a user
  public function getAllArts($user, Request $request)
  {
    global $app;
    
    // Validate the user
    $this->validate($user);
    
    // Return the art
    if ($this->checkCurrent($user,$request->request->get('user')))
      return new JsonResponse($app['arts.repository']->getAllByUser($user));
    else
      return new JsonResponse($app['arts.repository']->getAllPublicByUser($user));
  }
  
  // Connect to the application
  public function connect(Application $app)
  {
    // Create user controller
    $app['users.controller'] = $this;
    
    // Create controllers
    $controllers = $app['controllers_factory']
      ->convert('user',[$this->repo,'getByName']);

    // Create user routes
    $controllers
      ->post('/users',[$this,'post']);
    $controllers
      ->get('/users/{user}',[$this,'get'])
      ->before('authorization:optional');
    $controllers
      ->patch('/users/{user}',[$this,'patch'])
      ->before('authorization:authorize');
    $controllers
      ->delete('/users/{user}',[$this,'delete'])
      ->before('authorization:authorize');
    
    // Create user art routes
    $controllers
      ->get('/users/{user}/arts',[$this,'getAllArts'])
      ->before('authorization:optional');
    
    // Return the controllers
    return $controllers;
  }
}
