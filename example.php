<?php

/**
 * Example PHP file to demonstrate obfuscation
 * This file contains simple PHP code that will be obfuscated
 */

class ExampleClass
{
    private $name;
    private $value;
    
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function calculate($multiplier)
    {
        return $this->value * $multiplier;
    }
}

function greetUser($username)
{
    $greeting = "Hello, " . $username . "!";
    return $greeting;
}

// Example usage
$example = new ExampleClass("Test", 42);
echo greetUser($example->getName()) . "\n";
echo "Value: " . $example->getValue() . "\n";
echo "Calculated: " . $example->calculate(2) . "\n";
