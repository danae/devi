<?php
namespace Picturee\Authorization;

use Exception;
use Picturee\Application\ApplicationException;

class AuthorizationException extends ApplicationException
{
  // Constructor
  public function __construct($message, $code = 401, Exception $previous = null)
  {
    parent::__construct($message,$code,$previous);
  }
}
