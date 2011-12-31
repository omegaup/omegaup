<?php 

class DAOFormComponent extends FormComponent
{

	private $api_method_to_call;

	function __construct( $vo )
	{

		parent::__construct();
		
		$this->api_method_to_call = NULL;
		
		if(is_array($vo)){
			for ($a=0; $a < sizeof( $vo ); $a++) 
			{ 
				$fields = json_decode( $vo[$a]->__toString() );

				foreach($fields as $k => $v)
				{

					$caption = ucwords(str_replace ( "_" , " " , $k ));

					parent::addField( $k, $caption, "text", $v, $k );
					
				}							
			}
				
		}else{
			$fields = json_decode( $vo->__toString() );

			foreach($fields as $k => $v)
			{

				$caption = ucwords(str_replace ( "_" , " " , $k ));

				parent::addField( $k, $caption, "text", $v, $k );
				
			}
		}
		



	}

	
	public function sendHidden( $field_name ){
		if( is_array( $field_name ) ){
			foreach ($field_name as $field ) {
				$this->sendHidden( $field );
			}
			return;
		}
		
		
		$sof = sizeof($this->form_fields);

		for ($i=0; $i < $sof; $i++) { 

			if( $this->form_fields[$i]->id == $field_name )
			{
				$this->form_fields[$i]->send_as_hidden = true;
				$this->form_fields[$i]->hidden = true;
				$this->form_fields[$i]->type = "hidden";
				return true;			
			}
		}
		
		throw new Exception("Field `".$field_name."` not found in the VO object.");
	}
	
	
	

	public function hideField( $field_name ){
		
		if( is_array( $field_name ) ){
			foreach ($field_name as $field ) {
				$this->hideField( $field );
			}
			return;
		}
		
		
		$sof = sizeof($this->form_fields);

		for ($i=0; $i < $sof; $i++) { 

			if( $this->form_fields[$i]->id == $field_name )
			{
				$this->form_fields[$i]->type = "hidden";
				$this->form_fields[$i]->send_as_hidden = false;				
				$this->form_fields[$i]->hidden = true;
				return true;			
			}
		}
		
		throw new Exception("Field `".$field_name."` not found in the VO object.");
	}



}

