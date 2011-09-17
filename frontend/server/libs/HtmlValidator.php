<?php

/*
 *  String Validator
 * 
 */
require_once("Validator.php");

class HtmlValidator extends Validator
{
        
    // Save the reference
    public function HtmlValidator( )
    {
        
        Validator::Validator();
    }

    
    public function validate($string)
    {

      // Copied from http://stackoverflow.com/questions/3167074/which-function-in-php-validate-if-the-string-is-valid-html
      
      $start =strpos($string, '<');
      $end  =strrpos($string, '>',$start);
      if ($end !== false) {
        $string = substr($string, $start);
      } else {
        $string = substr($string, $start, $len-$start);
      }
      libxml_use_internal_errors(true);
      libxml_clear_errors();
      $xml = simplexml_load_string($string);

      $xmlerrors = libxml_get_errors(); 
      
      if( count($xmlerrors) !==0 )
      {
          foreach($xmlerrors as $xmlerror)
          {
            $this->setError($xmlerror->message); 
          }
              
          return false;
      }
      else 
      {
          return true;
      }
        
    }
    

}

?>
