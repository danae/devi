<?php
namespace Gallerie\Application\User;

use Gallerie\User\UserRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class UserControllerProvider implements ControllerProviderInterface
{
  // Connect to the application
  public function connect(Application $app)
  {
    // Create user repository
    $app['users.repository'] = new UserRepository($app['database'],'users');
    
    // Create user controller
    $app['users.controller'] = function() use ($app) {
      return new UserController($app['users.repository']);
    };
    
    // Create controllers
    $controllers = $app['controllers_factory']
      ->convert('user','users.repository:getByName');

    // Create routes
    $controllers
      ->post('/users','users.controller:post');
    $controllers
      ->get('/users/{user}','users.controller:get')
      ->before('authorization:optional');
    $controllers
      ->patch('/users/{user}','users.controller:patch')
      ->before('authorization:authorize');
    $controllers
      ->delete('/users/{user}','users.controller:delete')
      ->before('authorization:authorize');
    
    // Create art routes
    $controllers
      ->get('/users/{user}/arts','users.controller:getAllArts')
      ->before('authorization:optional');
    
    // Return the controllers
    return $controllers;
  }
}
