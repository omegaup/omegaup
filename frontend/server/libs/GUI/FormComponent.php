<?php 

class FormComponent implements GuiComponent
{

	protected $form_fields;

	function __construct(  )
	{

		$this->form_fields = array();
	}


	function addField( $id, $caption, $type, $value = "", $name = null )
	{
		array_push( $this->form_fields, new FormComponentField($id, $caption, $type, $value, $name ) );
	}


	function renderCmp()
	{

		$html = "<table>";

		foreach( $this->form_fields as $f )
		{
			$html .= "<tr><td>";
			
			$html .= $f->caption;
			
			$html .= "</td><td>";

			$html .= "<input value='" . $f->value .  "' >";

			$html .= "</td></tr>";
			
		}

		$html .= "</table>";

		return $html;

	}

}


class FormComponentField{
	public $id;
	public $caption;
	public $type;
	public $value;
	public $name;

	public function __construct( $id, $caption, $type, $value = "", $name = null ){
			$this->id 		= $id;
			$this->caption 	= $caption;
			$this->type 	= $type;
			$this->value 	= $value;
			$this->name 	= $name;
	}
}