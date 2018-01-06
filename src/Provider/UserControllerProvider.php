<?php
namespace Devi\Provider;

use DateTime;
use Devi\Model\User\User;
use Devi\Model\User\UserRepositoryInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Serializer;

class UserControllerProvider implements ControllerProviderInterface
{
  // Variables
  private $repository;
  private $serializer;
  
  // Constructor
  public function __construct(UserRepositoryInterface $repository, Serializer $serializer)
  {
    $this->repository = $repository;
    $this->serializer = $serializer;
  }
  
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
  public function getAll()
  {
    // Return all users
    $json = $this->serializer->serialize($this->repository->findAll(),'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Create a new user
  public function post(Request $request)
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
    $this->repository->create($user);
    
    // Return the created user
    $json = $this->serializer->serialize($user,'json');
    return JsonResponse::fromJsonString($json,201);
  }
  
  // Get an existing user
  public function get(User $user)
  {
    // Return the user
    $json = $this->serializer->serialize($user,'json');
    return JsonResponse::fromJsonString($json);
  }

  // Patch an existing user
  public function patch(User $user, Request $request)
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
    $this->repository->update($user->setModifiedAt(new DateTime));

    // Return the user
    $json = $this->serializer->serialize($user,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Delete an existing user
  public function delete(User $user, Request $request)
  {
    // Validate the user
    $this->validateCurrent($user,$request);
  
    // Delete the user
    $this->repository->delete($user);
  
    // Return the user
    $json = $this->serializer->serialize($user,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get all images of a user
  public function getAllImages(User $user, Request $request)
  {
    global $app;
    
    // Return the images
    if ($this->checkCurrent($user,$request))
    {
      // Return all images
      $json = $this->serializer->serialize($app['images.repository']->findAllByUser($user),'json');
      return JsonResponse::fromJsonString($json);
    }
    else
    {
      // Return only public images
      $json = $this->serializer->serialize($app['images.repository']->findAllPublicByUser($user),'json');
      return JsonResponse::fromJsonString($json);
    }
  }
  
  // Connect to the application
  public function connect(Application $app)
  {
    // Get the authorization
    $authorization = $app['authorization'];
    
    // Create controllers
    $controllers = $app['controllers_factory'];
    
    // Create user collection routes
    $controllers
      ->get('/',[$this,'getAll'])
      ->before([$authorization,'optional'])
      ->bind('user.collection.get');

    // Create user routes
    $controllers
      ->post('/',[$this,'post'])
      ->bind('user.collection.post');
    $controllers
      ->get('/{user}',[$this,'get'])
      ->convert('user',[$this->repository,'findByName'])
      ->before([$authorization,'optional'])
      ->bind('user.get');
    $controllers
      ->patch('/{user}',[$this,'patch'])
      ->convert('user',[$this->repository,'findByName'])
      ->before([$authorization,'authorize'])
      ->bind('user.patch');
    $controllers
      ->delete('/{user}',[$this,'delete'])
      ->convert('user',[$this->repository,'findByName'])
      ->before([$authorization,'authorize'])
      ->bind('user.delete');
    
    // Create user images routes
    $controllers
      ->get('/{user}/images/',[$this,'getAllImages'])
      ->convert('user',[$this->repository,'findByName'])
      ->before([$authorization,'optional']);
    
    // Return the controllers
    return $controllers;
  }
}
