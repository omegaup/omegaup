<?php


	/*
	 * Theme interface
	 * 
	 * All new interfaces must implement this interface
	 * to work correctly.
	 * 
	 * @author Alan Gonzalez <alan@caffeina.mx>
	 *
	 * */
	interface Theme{
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
		 *
		 * */
 		public static function prettyError( $message = null);


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
		 * 
		 * */
		public static function prettyDie( $message = null );
		

		/*
		 * HTML header for all frontend pages 
		 * 
		 * getHeader() returns a string which will be output at the start of every
		 * page.
		 *  
		 * */
		public static function getHeader();
	
	
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
		public static function getFooter();
		

		/*
		 * return the main menu
		 * 
		 * getMainMenu() returns a string which will be the html code for generating the
		 * main menu at the top of the page.
		 *  
		 *  
		 *  @access public
		 *  						
		 * */
		public static function getMainMenu();
		
		
		
		
		/*
		 * get CSS file requirements
		 * 
		 * getExternalCSS() returns a string with the appropiate link tag to the 
		 * css files needed for this theme  
		 *
		 *  @return String 
		 *  @access public
		 *  						
		 * */
		public static function getExternalCSS();		
		
	}
	
	
	
	
	
	
	
	
	
	
	

	/*
	 * GUI Class for common interaction with the end user.
	 *
	 * 
	 * @author Alan Gonzalez <alan@caffeina.mx>
	 * @since 0.1
	 * 
	 * */
	class ClassicTheme implements Theme{
		
		
		/*
		 * get CSS file requirements
		 * 
		 * getExternalCSS() returns an array of filenames needed bye this theme
		 *  
		 *  @return String String 
		 *  @access public
		 *  						
		 * */
		public static function getExternalCSS(){
			$cssFiles = array( "style.css" );
			
			$out = "";
			foreach($cssFiles as $cssFile ){
				$out .= '<link href="css/' . $cssFile . '" media="screen" rel="stylesheet" type="text/css" />';
			}
			return $out;
		}
		
		
		
		
		
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
			if($message == null){
				return "<div class='prettyError'>This is a pretty error !</div>";
			}else{
				return "<div class='prettyError'>". stripslashes( $message ) ."</div>";
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
			if($message == null){
				die("<div class='prettyError'>This is a pretty error !</div>");
			}else{
				die("<div class='prettyError'>". stripslashes( $message ) ."</div>");
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
			$html = '<div style="margin-left: 40%;"><img src="media/omegaup_curves.png"></div>';
			return $html;
			
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
		
		
		
		
		
		
		
		
		
		/*
		 * return the main menu
		 * 
		 * getMainMenu() returns a string which will be the html code for generating the
		 * main menu at the top of the page.
		 *  
		 *  
		 *  @access public
		 *  						
		 * */
		public static function getMainMenu(){
			//here, one might wish to display different menues
			//wether the user is logged in or not
			$html = "<ul >";
			$html .= "	<li><a href='problemas.php'>Problemas</a></li>";
			$html .= "	<li><a href='ranking.php'>Ranking</a></li>";
			$html .= "	<li><a href='actividad.php'>Actividad reciente</a></li>";
			$html .= "	<li><a href='faq.php'>FAQ</a></li>";
			$html .= "	<li><a href='about.php'>About</a></li>";
			$html .= "	<li><a href='escuelas.php'>Escuelas</a></li>";
			$html .= "	<li><a href='profesores.php'>Profesores</a></li>";			
			$html .= "	<li><a href='colabora.php'>Colabora</a></li>";
			$html .= "	<li><input type='text' placeholder='Buscar'></li>";			
			$html .= "</ul>";
			return $html;
		}
		
		
		
		
		
		/*
		 * div wrapper function
		 * 
		 * 
		 * @access private
		 * 
		 * */
		private static function div($foo){
			return "<div>" . $foo . "</div>";
		}
		
	}


?>
	
	