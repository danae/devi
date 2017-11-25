<?php
namespace Devi\Exception;

use Exception;

class AuthorizationException extends Exception
{
  // Constructor
  public function __construct($message, $code = 401, Exception $previous = null)
  {
    parent::__construct($message,$code,$previous);
  }
}
