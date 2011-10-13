<?php 

class MessageComponent implements GuiComponent{
	
	private $msg;

	function __construct($msg){
		$this->msg = $msg;
	}
	
	function renderCmp(){
		return "<div align=center>" . $this->msg . "</div>";
	}


}