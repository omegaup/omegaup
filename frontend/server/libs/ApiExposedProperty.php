<?php

/*
 *  Class for exposed properies defined in the API
 */

class ApiExposedProperty
{
    private $property_name;
    private $isRequiredAsInput;
    private $validators;
    private $errorMessage;
    private $value;
    
    
    public function ApiExposedProperty($property_name, $isRequiredAsInput, &$value, $validators = NULL)
    {        
        $this->validators = $validators;
        $this->property_name = $property_name;
        $this->isRequiredAsInput = $isRequiredAsInput;
        $this->value = $value;
    }
    
    public function setPropertyName($name)
    {
        $this->property_name = $name;
    }
    
    public function getPropertyName()
    {
        return $this->property_name;
    }
    
    public function setIsRequiredAsInput($isRequired)
    {
        $this->isRequiredAsInput = $isRequired;
    }
    
    public function getIsRequiredAsInput()
    {
        return $this->isRequiredAsInput;
    }
    
    public function setValidators($validatorsArray)
    {
        $this->validators = $validatorsArray;        
    }
    
    public function getValidators()
    {
        return $this->validators;
    }
    
    public function addValidator($validator)
    {
        array_push($this->validators, $validator);
    }
    
    public function setValue($value)
    {
        $this->value = $value;
    }
    
    public function getValue()
    {        
        return $this->value;
    }
    
    public function getError()
    {        
        return $this->errorMessage;
    }
      
    // Run all registered validators. Returns TRUE if ALL validators registered return true. 
    public function validate()
    {
        
        // If the value is required and it's null, return an error
        if ($this->isRequiredAsInput && is_null($this->value))
        {
            $this->errorMessage = "Required parameter ". $this->property_name ." is missing.";
            return false;
        }
        
        // If we don't have validators, assume that data is fine
        if (is_null($this->validators)) return true;
        
        // Only validate if value is required or is not required and contains some input
        if( $this->isRequiredAsInput || (!$this->isRequiredAsInput && !is_null($this->value)) )
        {
            foreach($this->validators as $v)
            {

                if ( !$v->validate() )
                {
                    // One validator failed, propagate error message
                    $this->errorMessage = "Validation failed for parameter " . $this->property_name . ": ". $v->getError();
                    return false;
                }       
            }
        }
        
        // All validators passed
        return true;
    }
    
    
}

?>
