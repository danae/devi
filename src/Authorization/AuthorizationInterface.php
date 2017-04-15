<?php
namespace Devi\Authorization;

use Symfony\Component\HttpFoundation\Request;

interface AuthorizationInterface
{
  // Authorizes a request or throws an exception if failed
  public function authorize(Request  $request): void;
  
  // Optionally authorizes a request
  public function optional(Request $request): void;
}
