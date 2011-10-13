<?php

class FreeHtmlComponent implements GuiComponent{

	private $html;

	function __construct ($html)
	{
		$this->html = $html;
	}

	function renderCmp()
	{
		return $this->html;
	}
}