<?php

/**
 * Conjunto de validadores genéricos
 *
 * @author joemmanuel
 */

class Validators
{
    /**
     * Check if email is valid
     * 
     * @param string $email
     * @param boolean $required
     * @return boolean
     * @throws InvalidArgumentException
     */
    public static function isEmail($email, $required = true){    
        if ($required && is_null($email)){
            throw new InvalidParameterException("Email is empty");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            throw new InvalidParameterException("Email is invalid");
        }
        
        return true;
    }

}
