<?php
namespace Gallerie\Authorization;

use Exception;
use Gallerie\Application\ApplicationException;

class AuthorizationException extends ApplicationException
{
  // Constructor
  public function __construct($message, $code = 401, Exception $previous = null)
  {
    parent::__construct($message,$code,$previous);
  }
}
