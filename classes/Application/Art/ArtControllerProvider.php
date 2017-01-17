<?php
namespace Gallerie\Application\Art;

use Gallerie\Art\ArtRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class ArtControllerProvider implements ControllerProviderInterface
{
  // Connect to the application
  public function connect(Application $app)
  {
    // Create art repository
    $app['arts.repository'] = new ArtRepository($app['database'],'art');

    // Create art controller
    $app['arts.controller'] = function() use ($app) {
      return new ArtController($app['arts.repository']);
    };
    
    // Create controllers
    $controllers = $app['controllers_factory']
      ->convert('art','arts.repository:getByName');

    // Create art routes
    $controllers
      ->post('/arts','arts.controller:post')
      ->before('authorization:authorize');
    $controllers
      ->get('/arts/{art}','arts.controller:get')
      ->before('authorization:optional');
    $controllers
      ->patch('/arts/{art}','arts.controller:patch')
      ->before('authorization:authorize');
    $controllers
      ->delete('/arts/{art}','arts.controller:delete')
      ->before('authorization:authorize');

    // Create raw art routes
    $controllers
      ->post('/arts/{art}/raw','arts.controller:postRaw')
      ->before('authorization:authorize');
    $controllers
      ->get('/arts/{art}/raw','arts.controller:getRaw')
      ->before('authorization:optional');
    
    // Return the controllers
    return $controllers;
  }
}
