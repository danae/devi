<?php
namespace Devi\Utils;

trait StrategyTrait 
{
  // Variables
  protected $transients = [];
  protected $strategies = [];
  protected $replacements = [];
  
  // Mark a field as a transient field
  public function useTransient($name): self
  {
    if (is_array($name))
      $this->transients = array_merge($this->transients,$name);
    else
      $this->transients[] = $name;
    return $this;
  }
  
  // Use a strategy for a named object
  public function useStrategy(string $name, callable $strategy, string $replacement = ''): self
  {
    $this->strategies[$name] = $strategy;
    if (!empty($replacement))
      $this->replacements[$name] = $replacement;
    return $this;
  }
}
