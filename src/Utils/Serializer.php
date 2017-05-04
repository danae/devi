<?php
namespace Devi\Utils;

use DateTime;
use ReflectionClass;

class Serializer 
{
  // Use strategies
  use StrategyTrait;
  
  // Converts an object to an array
  public function serialize($object)
  {
    // Check for null
    if ($object === null || !is_object($object))
      return null;
    
    // Create a reflector for the object
    $reflector = new ReflectionClass($object);
    
    // Iterate over the properties
    foreach ($reflector->getProperties() as $property)
    {
      // Access the property directly
      $property->setAccessible(true);
      
      // Check if there exists a strategy for this property
      $name = $property->getName();      
      if (array_key_exists($name,$this->strategies))
        $value = $this->strategies[$name]($property->getValue($object));
      else
        $value = $property->getValue($object);
      
      // Put the value
      if (array_key_exists($name,$this->replacements))
        $name = $this->replacements[$name];
      $array[$name] = $value;
    }
    
    // Remove transient fields
    foreach ($this->transients as $name)
      unset($array[$name]);
    
    // Return the array
    return $array;
  }
  
  // Static DateTime strategy
  public static function dateTimeStrategy(DateTime $dateTime)
  {
    return $dateTime->format(DateTime::ISO8601);
  }
}
