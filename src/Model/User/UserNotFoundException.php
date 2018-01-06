<?php
namespace Devi\Model\User;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserNotFoundException extends NotFoundHttpException
{
  // Variables
  private $user;
  
  // Constructor
  public function __construct($user)
  {
    parent::__construct('The specified user was not found');
    $this->user = $user;
  }
  
  // Return the user
  public function getUser()
  {
    return $this->user;
  }
}