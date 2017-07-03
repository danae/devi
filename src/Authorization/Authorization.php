<?php
namespace Devi\Authorization;

use Devi\Model\User\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

class Authorization implements AuthorizationInterface
{
  // Variables
  private $repository;
  
  // Constructor
  public function __construct(UserRepositoryInterface $repository)
  {
    $this->repository = $repository;
  }
  
  // Authorizes a request or throws an exception if failed
  public function authorize(Request $request): void
  {
    // Get the Authorization and Date headers
    $auth_user = $request->getUser();
    $auth_password = $request->getPassword();
    
    // Check if the headers are valid
    if ($auth_user == null || $auth_password == null)
      throw new AuthorizationException('The request did not contain a valid Authorization header');

    // Check if the user is valid
    $user = $this->repository->find($auth_user);
    if ($user == null)
      throw new AuthorizationException('The specified user does not exist');
    
    // Check if the password is valid
    if (!password_verify($auth_password,$user->getPassword()))
      throw new AuthorizationException('The specified password does not match');

    // All checks passed, so return the user
    $request->request->set('user',$user);
  }
  
  // Optionally authorizes a request
  public function optional(Request $request): void
  {
    try
    {
      $this->authorize($request);
    } 
    catch (AuthorizationException $ex) 
    {
      return;
    }
  }
}
