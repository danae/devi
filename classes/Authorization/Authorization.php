<?php
namespace Gallerie\Authorization;

use DateTime;
use Gallerie\User\User;
use Gallerie\User\UserDatabase;
use Symfony\Component\HttpFoundation\Request;

class Authorization
{
  // Required authorization
  public function authorize(Request $request)
  {
    // Get the Authorization and Date headers
    $public_key = $request->getUser();
    $hash_request = $request->getPassword();
    $date_request = self::getDate($request);
    $date_now = time();
    
    // Check if the headers are valid
    if ($public_key == null || $hash_request == null)
      throw new AuthorizationException('The request did not contain a valid Authorization header');
    if ($date_request == null)
      throw new AuthorizationException('The request did not contain a valid Date or X-Request-Date header');
    
    // Check if the date is recent
    if ($date_now < $date_request || $date_now - $date_request > 900)
      throw new AuthorizationException('The request was made more than 900 seconds in the past');

    // Check if the user is valid
    $user = UserDatabase::getByPublicKey($public_key);
    if ($user == null)
      throw new AuthorizationException('The request authorization is invalid');
    
    // Check if the hash is valid
    $hash_now = self::hash($request,$user);
    if ($hash_request !== $hash_now)
      throw new AuthorizationException('The request authorization is invalid');

    // All checks passed, so return the user
    $request->request->set('user',$user);
    return $user;
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
  
  // Get date of a request
  private static function getDate(Request $request)
  {
    $date_header = $request->headers->has('Date') ? $request->headers->get('Date') : $request->headers->get('X-Request-Date');
    return strtotime($date_header) ?: (int)$date_header;
  }
  
  // Generate a sign based on the request
  public static function sign(Request $request)
  {
    $date_timestamp = self::getDate($request);
    $date = (new DateTime)->setTimestamp($date_timestamp)->format(DateTime::ISO8601);
    
    return $request->getMethod() . " " . $request->getPathInfo() . " " . $date;
  }
  
  // Generate a hash based on the request and user
  public static function hash(Request $request, User $user)
  {
    return hash_hmac('sha256',self::sign($request),$user->getPrivateKey());
  }
}
