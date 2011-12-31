<?php

require_once('Validator.php');
require_once('StringValidator.php');
require_once('NotEmptyValidator.php');
require_once('DateValidator.php');
require_once('DateRangeValidator.php');
require_once('NumericValidator.php');
require_once('NumericRangeValidator.php');
require_once('EnumValidator.php');
require_once('CustomValidator.php');
require_once('HtmlValidator.php');
require_once('StringLengthValidator.php');

class ValidatorFactory 
{
    // In case of poor performance due to object creation, we can cache objects
    
    public static function stringNotEmptyValidator()
    {
        
        $stringValidator = new Validator;
        
        $stringValidator->addValidator(new StringValidator)
                        ->addValidator(new NotEmptyValidator);                        
        
        
        return $stringValidator;
    }
    
    public static function dateRangeValidator($start, $end)
    {                        

        $dateRangeValidator = new Validator;
        $dateRangeValidator->addValidator(new DateValidator)
                           ->addValidator(new DateRangeValidator($start, $end));        
                        
        return $dateRangeValidator;
        
    }

    public static function numericValidator()
    {
        $numericValidator = new Validator;
        $numericValidator->addValidator(new NumericValidator);
        
        return $numericValidator;
    }
    
    public static function numericRangeValidator($start, $end)
    {                
            
        $numericRangeValidator = new Validator;
        $numericRangeValidator->addValidator(new NumericValidator)
                              ->addValidator(new NumericRangeValidator($start, $end));
        
        return $numericRangeValidator;
    }    
    
    public static function enumValidator(array $enum_array)
    {
        $enumValidator = new Validator;
        $enumValidator->addValidator(new EnumValidator($enum_array));
        
        return $enumValidator;
    }
    
    public static function htmlValidator()
    {
        $htmlValidator = new Validator;
        $htmlValidator->addValidator(new StringValidator())
                      ->addValidator(new HtmlValidator());
        
        return $htmlValidator;
    }
    
    public static function stringOfMaxLengthValidator($length)
    {
        $stringOfMaxLengthValidator = new Validator;
        
        $stringOfMaxLengthValidator->addValidator(new StringLengthValidator($length))
                                    ->addValidator(new NotEmptyValidator);                        
        
        
        return $stringOfMaxLengthValidator;
    }
}

?>
