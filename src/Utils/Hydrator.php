<?php
namespace Devi\Utils;

use DateTime;
use ReflectionClass;

class Hydrator
{
  // Use strategies
  use StrategyTrait;
  
  // Variables
  private $className = 'stdClass';
  private $constructorArgs = [];
  
  // Uses a class to hydrate into
  public function useClass(string $className): self
  {
    $this->className = $className;
    return $this;
  }
  
  // Use constructor arguments for the hydration class
  public function useConstructorArgs(array $constructorArgs): self
  {
    $this->constructorArgs = $constructorArgs;
    return $this;
  }
  
  // Convert an array into an object
  public function hydrate(array $array, $className = null, $constructorArgs = null)
  {
    // Set default variables
    $className = $className ?? $this->className;
    $constructorArgs = $constructorArgs ?? $this->constructorArgs;
    
    // Create a reflector for the object
    $reflector = new ReflectionClass($className);
    if ($reflector->getConstructor() !== null)
      $object = $reflector->newInstance($constructorArgs);
    else
      $object = $reflector->newInstanceWithoutConstructor();
    
    // Iterate over the array
    foreach ($array as $name => $value)
    {
      // Check if the property exists
      if (!$reflector->hasProperty($name) || array_key_exists($name,$this->transients))
        continue;

      // Access the property directly
      $property = $reflector->getProperty($name);
      $property->setAccessible(true);
      
      // Check if there exists a strategy for this property
      $name = $property->getName();
      if (array_key_exists($name,$this->strategies))
        $property->setValue($object,$this->strategies[$name]($value));
      else
        $property->setValue($object,$value);
    }
    
    // Return the object
    return $object;
  }
  
  // Static boolean strategy
  public static function boolStrategy(int $int)
  {
    return (bool)$int;
  }
  
  // Static DateTime strategy
  public static function dateTimeStrategy(string $string)
  {
    return new DateTime($string);
  }
}
