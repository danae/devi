<?php
namespace Gallerie\Authorization;

use Symfony\Component\HttpFoundation\Request;

class BasicAuthorization
{
  // Required authorization
  public function authorize(Request $request)
  {
    global $app;
    
    // Get the Authorization and Date headers
    $auth_user = $request->getUser();
    $auth_password = $request->getPassword();
    
    // Check if the headers are valid
    if ($auth_user == null || $auth_password == null)
      throw new AuthorizationException('The request did not contain a valid Authorization header');

    // Check if the user is valid
    $user = $app['users.repository']->getByName($auth_user);
    if ($user == null)
      throw new AuthorizationException('The specified user does not exist');
    
    // Check if the password is valid
    if ($user->getPassword() !== $auth_password)
      throw new AuthorizationException('The specified password does not match');

    // All checks passed, so return the user
    $request->request->set('user',$user);
  }
  
  // Authorize optional
  public function optional(Request $request)
  {
    try
    {
      $this->authorize($request);
    } 
    catch (AuthorizationException $ex) 
    {
      return null;
    }
  }
}
