<?php 

class FormComponent implements GuiComponent
{

	protected $form_fields;
	protected $submit_form;
	protected $on_click;

	function __construct(  )
	{
		$this->on_click 		= null;
	 	$this->submit_form 		= null;
		$this->form_fields      = array();
	}


	function addField( $id, $caption, $type, $value = "", $name = null )
	{
		array_push( $this->form_fields, new FormComponentField($id, $caption, $type, $value, $name ) );
	}


	function renderCmp()
	{

		$html = "<table>";
		if( !is_null ( $this->submit_form ) ){
			$html .= "<form method='". $this->submit_form["method"] . "' action='". $this->submit_form["submit_form_url"] . "'>";
		}else{
			$html .= "<form >";	
		}
		

		foreach( $this->form_fields as $f )
		{
			if($f->type !== "hidden"){
				$html .= "<tr><td>";
				
				$html .= $f->caption;
				
				$html .= "</td><td>";				
			}


			$html .= "<input name='" . $f->name .  "' value='" . $f->value .  "' type='". $f->type ."' >";

			
			if($f->type !== "hidden"){
				$html .= "</td></tr>";	
			}
			
			
		}

		if( !is_null ( $this->submit_form ) ){
			$html .= "<tr><td>";

			$html .= "</td><td align=right>";

			$html .= "<input value='" . $this->submit_form["caption"] .  "' type='submit'  >";

			$html .= "</td></tr>";
		}




		if( !is_null ( $this->on_click ) ){

			$html .= "<tr><td>";

			$html .= "</td><td align=right>";

			$html .= "<input value='" . $this->on_click["caption"] .  "' type='button' onClick='". $this->on_click["function"] ."' >";

			$html .= "</td></tr>";
		}


		$html .= "</form>";
		$html .= "</table>";

		return $html;

	}

	public function addSubmit($caption, $submit_form_url = "", $method = "GET"){
		$this->submit_form = array( "caption" => $caption, "submit_form_url" => $submit_form_url, "method" => $method );
	}


	public function addOnClick($caption, $js_function){
		$this->on_click = array( "caption" => $caption, "function" => $js_function );
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