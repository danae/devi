<?php
namespace Devi\Controller;

use DateTime;
use Devi\Model\User;
use Devi\Model\UserRepositoryInterface;
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
  public function validateCurrent(User $user, Request $request)
  {
    // Check if the user is the owner of the image
    if ($user->getId() !== $request->request->get('user')->getId())
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
  
  // Get all extisting users
  public function getAll()
  {
    // Return all users
    return new JsonResponse($this->repo->getAll());
  }
  
  // Create a new user
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
      password_hash($request->request->get('password'),PASSWORD_BCRYPT)
    );
  
    // Put the user in the database
    $this->repo->put($user);
    
    // Return the created user
    return new JsonResponse($user,201);
  }
  
  // Get an existing user
  public function get($user)
  {
    // Validate the user
    $this->validate($user);
    
    // Return the user
    return new JsonResponse($user);
  }

  // Patch an existing user
  public function patch($user, Request $request)
  {
    // Validate the user
    $this->validate($user);
    $this->validateCurrent($user,$request);

    // Replace the fields
    if ($request->request->has('name'))
      $user->setName($request->request->get('name'));
    if ($request->request->has('email'))
      $user->setEmail($request->request->get('email'));
    if ($request->request->has('password'))
      $user->setPassword($request->request->get('password'));
    if ($request->request->has('public'))
      $user->setPublic($request->request->get('public'));

    // Patch the updated user in the database
    $this->repo->patch($user->setDateModified(new DateTime));

    // Return the user
    return new JsonResponse($user);
  }
  
  // Delete an existing user
  public function delete($user, Request $request)
  {
    // Validate the user
    $this->validate($user);
    $this->validateCurrent($user,$request);
  
    // Delete the user
    $this->repo->delete($user);
  
    // Return the user
    return new JsonResponse($user);
  }
  
  // Get all images of a user
  public function getAllImages($user, Request $request)
  {
    global $app;
    
    // Validate the user
    $this->validate($user);
    
    // Return the images
    if ($this->checkCurrent($user,$request->request->get('user')))
      return new JsonResponse($app['images.repository']->getAllByUser($user));
    else
      return new JsonResponse($app['images.repository']->getAllPublicByUser($user));
  }
  
  // Connect to the application
  public function connect(Application $app)
  {
    // Create controllers
    $controllers = $app['controllers_factory']
      ->convert('user',[$this->repo,'getByName']);
    
    // Create user collection routes
    $controllers
      ->get('/users',[$this,'getAll'])
      ->before('authorization:optional');

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
    
    // Create user images routes
    $controllers
      ->get('/users/{user}/images',[$this,'getAllImages'])
      ->before('authorization:optional');
    
    // Return the controllers
    return $controllers;
  }
}
