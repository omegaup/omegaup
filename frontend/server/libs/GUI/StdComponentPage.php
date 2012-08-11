<?php 

class StdComponentPage extends StdPage{
	

	protected $components;
	

	function __construct()
	{
		parent::__construct();
		parent::addJs( "https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" );
		$this->components = array();
		
	}

	public function addComponent( $cmp )
	{
		if( $cmp instanceof GuiComponent ){
			//go ahead
			array_push( $this->components, $cmp );

		}else if( is_string($cmp)){
			array_push( $this->components, new FreeHtmlComponent( $cmp ) );			
			

		}else{
			throw new Exception("This is not a valid component.");

		}
	}


	public function addContent($html)
	{
		throw new Exception("You may not add HTML to this Component based Page. Please use the addComponent() method. ");
	}


	public function render()
	{
		
		$html = "";

		foreach( $this->components as $cmp ){
			$html .= $cmp->renderCmp();
		}

		parent::addContent( $html );

		parent::render();
	}



}