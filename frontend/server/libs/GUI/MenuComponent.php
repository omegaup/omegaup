<?php 

class MenuComponent implements GuiComponent
{

	private $items;

	function __construct()
	{
		$this->items = array();
	}


	function addItem($caption, $url)
	{
		array_push($this->items, new MenuItem($caption, $url));
	}


	function renderCmp()
	{
		$out = "<div>";
		foreach($this->items as $item)
		{
			$out .= "&nbsp;&nbsp;<span><a href=". $item->url .">" . $item->caption . "</a></span>";
		}
		$out .= "</div>";
		return $out;
	}

}


class MenuItem{
	public $caption;
	public $url;

	function __construct($caption, $url)
	{
		$this->caption = $caption;
		$this->url = $url;
	}
}