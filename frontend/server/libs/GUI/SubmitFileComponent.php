<?php


class SubmitFileComponent implements GuiComponent{
	
	private $submitTo;

	function __construct( $submitTo = null ){

		if( is_null($submitTo) )
			$this->submitTo = $_SERVER['PHP_SELF'];
		else
			$this->submitTo = $submitTo;
	}

	function renderCmp(){
		$html = "<form action='".$this->submitTo."' method='POST' enctype='multipart/form-data'>";
		$html .= "<input name='file' type='file'>";
		$html .= "<input name='file_sent' type='hidden'>";
		$html .= "<input type='submit' value='Enviar'>";
		$html .= "</form>";

		return $html;
	}

}