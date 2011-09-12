<?php

/*
 *  String Validator
 * 
 */
require_once("validator.php");

class HtmlValidator extends Validator
{
    // Reference to string
    private $str;
    
    // Save the reference
    public function HtmlValidator( &$string_ref )
    {
        $this->str = $string_ref;
        Validator::Validator();
    }

    
    public function validate()
    {

      //@TODO Copied from http://stackoverflow.com/questions/3167074/which-function-in-php-validate-if-the-string-is-valid-html
      // Need to test thoroughly  
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

      return count(libxml_get_errors())==0;

        
    }
}

?>
