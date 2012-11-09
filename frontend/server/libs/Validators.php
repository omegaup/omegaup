<?php

/**
 * Conjunto de validadores genéricos
 *
 * @author joemmanuel
 */

class Validators
{
    // @todo Localization
    const IS_EMPTY = " is empty";
    const IS_INVALID = "is invalid";
    
    /**
     * Check if email is valid
     * 
     * @param string $email
     * @param string $parameterName Name of parameter that will appear en error message
     * @param boolean $required If $required is TRUE and the parameter is not present, check fails.
     * @return boolean
     * @throws InvalidArgumentException
     */
    public static function isEmail($email, $parameterName, $required = true){    
        if ($required && is_null($email)){
            throw new InvalidParameterException($parameterName.Validators::IS_EMPTY);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            throw new InvalidParameterException($parameterName.Validators::IS_INVALID);
        }
        
        return true;
    }

}
