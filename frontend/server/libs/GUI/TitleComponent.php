<?php

class TitleComponent implements GuiComponent{

	private $t;

	function __construct ($t, $l = 1)
	{
		$this->t = "<h".$l.">".$t."</h".$l.">"; 		
	}

	function renderCmp()
	{
		return $this->t;
	}
}