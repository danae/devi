<?php
namespace Devi\Authorization;

use Devi\Controller\ApplicationException;
use Exception;

class AuthorizationException extends ApplicationException
{
  // Constructor
  public function __construct($message, $code = 401, Exception $previous = null)
  {
    parent::__construct($message,$code,$previous);
  }
}
