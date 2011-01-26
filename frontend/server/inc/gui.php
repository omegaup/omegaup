<?php

	/*
	 * GUI Class for common interaction with the end user
	 * 
	 * @author Alan Gonzalez <alan@caffeina.mx>
	 * @since 0.1
	 * 
	 * */
	class GUI{
		
		
		
		

		/*
		 * Pretty Error
		 * 
		 * prettyError() is a handy function for drawing a custom pretty error, and
		 * continue with the page normal flow. This is intended for controlled or 
		 * expected errors. One could send an optinal parameter, <i>message</i>
		 * which would display that message instead of a generic error message.
		 * 
		 *
		 * @param String message Optional string for custom error.		
		 * @see prettyDie()
		 * */		
		public static function prettyError( $message = null ){
			if($message){
				return "<div class='prettyError'> This is a pretty error</div>";
			}else{
				return "<div class='prettyError'>". strip_slashes( $message ) ."</div>";
			}

		}		
		


		/*
		 * Pretty Die
		 * 
		 * prettyDie() is a handy function for drawing a custom pretty error
		 * and dying the whole thing. One could send an optinal parameter, 
		 * <i>message</i> which would display that message instead of a generic
		 * error message.
		 * 
		 * 
		 * @param String message Optional string for custom error.
		 * @see prettyError()
		 * */
		public static function prettyDie( $message = null ){
			if($message){
				die("<div class='prettyError'> This is a pretty error</div>");
			}else{
				die("<div class='prettyError'>". strip_slashes( $message ) ."</div>");
			}

		}
		
		
		
		
		
		/*
		 * HTML header for all frontend pages 
		 * 
		 * getHeader() returns a string which will be output at the start of every
		 * page.
		 *  
		 * */
		public static function getHeader(){
			
			return "This is the header !";
			
		}
	
	
	
	
		/*
		 * HTML footer for all frontend pages 
		 * 
		 * getFooter() returns a string which will be output at the end of every
		 * page. This would be a nice place for loading external JS resources, and/or
		 * some analytics javascript. Placing the externernal loading of resources here
		 * the page will display faster as, the resource loading is synchronimous !
		 * Loading resoruces at the beggining of the page, would stop all rendering of
		 * the page until the resource is loaded.
		 *  
		 * */
		public static function getFooter(){
			
			return "This is the footer !";
			
		}
		
	}


?>
	
	