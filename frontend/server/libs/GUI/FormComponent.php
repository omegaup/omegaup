<?php 

class FormComponent implements GuiComponent{

	protected 	$form_fields;
	protected	$submit_form;
	protected 	$on_click;
	protected 	$send_to_api;
	protected 	$wrap_id;
	protected	$wrap_style;

	
	
	private 	$send_to_api_http_method;
	private		$send_to_api_callback;
	private 	$send_to_api_redirect;
	private 	$is_editable;
	

	
	function __construct(  ){
		$this->send_to_api 			= null;
		$this->on_click 			= null;
	 	$this->submit_form 			= null;
		$this->send_to_api_callback = null;
		$this->send_to_api_redirect = null;	
		
		//defaults
		$this->is_editable 		= true;
		$this->form_fields      = array(  );
		

	}



	public function wrapWith($type, $val){
		switch($type){
			case "id" 		: $this->wrap_id = $val; break;
			case "class" 	: break;
			case "name"  	: break;
			case "style"  	: $this->wrap_style = $val; break;
						
			default:
				throw new Exception ( "Must wrap with `id`, `class` or `name`" );
		}
	}



	/**
	 * 
	 * 
	 * */
	public function setEditable( $editable ){
		$this->is_editable = $editable;
	}



	/**
	 * 
	 * 
	 * */
	public function addField( $id, $caption, $type, $value = "", $name = null ){
		array_push( $this->form_fields, new FormComponentField($id, $caption, $type, $value, $name ) );
	}



	/**
	 * 
	 * 
	 * */
	private function removeDuplicates(){
		usort( $this->form_fields, array( "FormComponentField", "idSort"  ));
		$top_i = 0;

		
		for ($i=1; $i < sizeof( $this->form_fields ); $i++) {
			if( ( $this->form_fields[$i]->id != $this->form_fields[$top_i]->id) ){
				$this->form_fields[++$top_i] = $this->form_fields[$i];
			}
		}
		
		$this->form_fields =  array_slice( $this->form_fields, 0,  $top_i+1, true);
		
	}



	/**
	 * 
	 * 
	 * */
	function renderCmp(){
		
		//remove fields with the same id
		$this->removeDuplicates();
		
		//sort fields by the necesary attribute
		usort( $this->form_fields, array( "FormComponentField", "obligatorySort"  ));
		
		$html = "";

		
		if( !is_null($this->send_to_api)){
			
			$html.= "<script>";
			$html.= "var obligatory = [];";
			foreach( $this->form_fields as $f )
			{
				if( $f->obligatory )
					$html .= "obligatory.push( '". $f->id . "' );";
			}
				
			$html .= "function getParams(){";
			
			$html .= "var p = {};";
			$html .= "var found=false;";
			

				
				foreach( $this->form_fields as $f )
				{

					
					if( $f->hidden === true ){
						Logger::debug( $f->id );
						Logger::debug( $f->hidden );
						Logger::debug( $f->send_as_hidden );
						if($f->send_as_hidden === true){
							$html .= "p." . $f->id . " = " . $f->value . ";" ;
						}
						continue;								
					}

					
					$html .= "if( Ext.get('". $f->id . "').getValue().length > 0 ){ p." . $f->id . " = Ext.get('". $f->id . "').getValue() ; } else{" ;
						//else si no esta lleno de datos, vamos a buscarlo en los obligatorios, 
						//si esta en los obligatorios entonces mandamos el error
						$html .= "for (var i = obligatory.length - 1; i >= 0; i--){";
						$html .= "	if(obligatory[i] == '". $f->id . "') {";
						$html .= "		found = true;";
						$html .= "		Ext.get('". $f->id . "').highlight('#DD4B39');";
						$html .= "	}";
						$html .= "};";
						$html .= "";
					$html .= "}" ;
				}

			$html .= "	if(!found) sendToApi(p);";
			$html .= "}";
			
			$html .= "function sendToApi( params ){";
			$html.= "	POS.API.". $this->send_to_api_http_method ."(\"". $this->send_to_api ."\", params, ";
			$html.= "	{";
			$html.= "		callback : function( a ){ ";
			$html.= "			";
			$html.= "			/* remove unload event */";			
			$html.= "			window.onbeforeunload = function(){ return;	};";			
			$html.= "			Ext.MessageBox.show({
			           title: 'OK',
			           msg: 'OK.',
			           buttons: Ext.MessageBox.OK
			       });	/* console.log('OKAY'); */ ";

			if( !is_null($this->send_to_api_callback) )
				$html.= "			" . $this->send_to_api_callback . "( a );";
			
			if( !is_null($this->send_to_api_redirect) )
				$html.= "			window.location = '".$this->send_to_api_redirect."';";

			$html.= "			";
			$html.= "			";									
			$html.= "	 	}";
			$html.= "	});";
			$html.= "}";
			$html.= "</script>";			
			
		}
		
		$html .= "<div ";
		
		/**************** WRAPING ******/
		if( !is_null($this->wrap_id)  ){
			$html .= " id='". $this->wrap_id ."' ";
		}
		
		if( !is_null($this->wrap_style)  ){
			$html .= " style='". $this->wrap_style ."' ";
		}
		
		
		$html .= ">";
		
		$html .= "<table width=100%>";

		if( !is_null ( $this->submit_form ) ){
			$html .= "<form method='". $this->submit_form["method"] . "' action='". $this->submit_form["submit_form_url"] . "'>";

		}else{
			$html .= "<form >";	
			
		}
		
		$new_row = 0;
		$html .= "<tr>";
		foreach( $this->form_fields as $f )
		{
			if($f->hidden) continue;
			//incrementar el calculo de la fila actual
			$new_row++;
			
	
			if($f->type !== "hidden"){
				$html .= "<td>";
				if($f->obligatory === true) $html .= "<b>";
				$html .= $f->caption;
				if($f->obligatory === true) $html .= "</b>";
				$html .= "</td><td>";				
			}

			switch( $f->type ){
				//
				// Combo boxes
				// 
				case "combo" :
					$html .= "<select id='". $f->id ."'" ;
                                        if($this->is_editable===false)
                                            $html .= " disabled='disabled' ";
                                        $html .= ">";
					$html .= "<option value=''>Selecciona una opcion</option>";
					foreach($f->value as $o){
                                        {
                                                if($o["selected"])
                                                    $html .= "<option value='".$o["id"]."' selected>".$o["caption"]."</option>";
                                                else
                                                    $html .= "<option value='".$o["id"]."'>".$o["caption"]."</option>";
                                        }
					}
						
					
					$html .= "</select>";

				break;
                
				//
				// List boxes
				//             
                case "listbox" :
                        $html .= "<select multiple='true' id='". $f->id ."' name='".$f->name."'>";
                        
                        foreach($f->value as $o){
							$html .= "<option value='".$o["id"]."'>".$o["caption"]."</option>";	
						}

                        
                        $html .= "</select>";
                break;
				
				//
				// Everything else
				// 
				default:
					if( $this->is_editable === false ){
						//$html .= "<input id='" . $f->id .  "' name='" . $f->name .  "' value='" . $f->value .  "' type='". $f->type ."' >";
						$html .= $f->value ;
					} else if ($f->type == 'textarea') {
						$html .= "<textarea id='{$f->id}' name='{$f->name}'>{$f->value}</textarea>";
					}else{
						$html .= "<input id='" . $f->id .  "' name='" . $f->name .  "' value='" . $f->value .  "' type='". $f->type ."' >";
					}
			}

			
			if($f->type !== "hidden"){
				$html .= "</td>";
			}
			
			if($new_row == 2){
				$html .= "</tr><tr>";
				$new_row = 0;
			}
		}
		
		$html .= "</tr><tr>";

		$html .= "<td></td><td></td>";

			

		if( !is_null ( $this->submit_form 	) ){
			$html .= "<td align=right>";
			$html .= "<input value='" . $this->submit_form["caption"] .  "' type='submit'  >";
			$html .= "</td></tr>";
		}

		if( !is_null ( $this->on_click 		) ){
			$html .= "<td>";
			$html .= "</td><td align=right>";
			$html .= "<div class='POS Boton' onClick='". $this->on_click["function"] ."' >".$this->on_click["caption"]."</div>";
			$html .= "</td></tr>";
		}

		if( !is_null ( $this->send_to_api	) ){
			$html .= "<td>";
			$html .= "</td><td align=right>";
			$html .= "<div class='POS Boton' onClick=''  >Cancelar</div>";						
			$html .= "<div class='POS Boton OK' onClick='this.onClick=null;getParams()'  >Aceptar</div>";			
			$html .= "</td></tr>";			
		}
		
//		if( $this->is_editable === false ){
//			$html .= "<script>var is_editable_now = false; function make_editable(  ){ ";
//			
//			$html .= " }</script>";
//			$html .= "<td></td></tr>";
//			$html .= "<tr><td colspan='4'>";
//			$html .= "<div class='POS Boton'>Editar</div>";
//			$html .="</td></tr>";
//		
//		}

		$html .= "</form></table>";
		$html .= "</div>";		
		return $html;

	}



	public function addSubmit( $caption, $submit_form_url = "", $method = "POST"){
		$this->submit_form = array( "caption" => $caption, "submit_form_url" => $submit_form_url, "method" => $method );
	}



	public function addOnClick( $caption, $js_function){
		$this->on_click = array( "caption" => $caption, "function" => $js_function );
	}



	public function addApiCall( $method_name, $http_method = "POST" ){
		if( !($http_method === "POST" || $http_method === "GET") ){
			throw new Exception("Http method must be POST or GET");
		}
		
		$this->send_to_api = $method_name;
		$this->send_to_api_http_method = $http_method;		
		
	}


	
	/**
	 * Esta es una funcion en js que se llamara 
	 * cuando la llamada al api sea exitosa.
	 *
	 * */
	public function onApiCallSuccess( $jscallback ){
		$this->send_to_api_callback = $jscallback;
	}


	
	/**
	 * 
	 * Redirect to a new page on apicall sucess
	 * 
	 * */
	public function onApiCallSuccessRedirect( $url, $send_param = null ){
		$this->send_to_api_redirect = $url;		
	}


	
	public function renameField( $field_array ){
		
		$found = false;
		foreach ($field_array as $old_name => $new_name) {
			$found = false;
			$sof = sizeof( $this->form_fields );

			for ($i=0; $i < $sof; $i++) { 
				
				if( $this->form_fields[$i]->id === $old_name )
				{
					$this->form_fields[$i]->id = $new_name;
					$this->form_fields[$i]->caption = ucwords(str_replace ( "_" , " " , $new_name ));
					$found = true;
					//no break since there could be plenty of same id's
				}//if

			}//for
			
			if($found === false) throw new Exception("Field `".$old_name."` not found in the VO object.");
			
		}//foreach field in the array
	}



	/**
	  *
	  * @param array or string
	  *
	  **/
	public function makeObligatory( $field_array ){
		
		if( !is_array($field_array)){
			$field_array = array( $field_array );
		}
		
		foreach ($field_array as $field) {
			
			$sof = sizeof( $this->form_fields );

			for ($i=0; $i < $sof; $i++) { 

				if( $this->form_fields[$i]->id === $field )
				{
					$this->form_fields[$i]->obligatory = true;
				}//if

			}//for
		}
	}



	public function createComboBoxJoin( $field_name, $field_name_in_values, $values_array, $selected_value=null ){
		if( sizeof( $values_array ) == 0 ){
			//do something
		}

		$sof = sizeof( $this->form_fields );

		for ($i=0; $i < $sof; $i++) { 
			
			if( $this->form_fields[$i]->id === $field_name )
			{
				$this->form_fields[$i]->type  = "combo";
				
				$end_values = array(  );

				foreach ($values_array as $v ){
					
					if( !($v instanceof VO)  ){
						
						if(is_array($v))
						{
                                                
                            if( $selected_value == $v["id"] ){
	                        	array_push( 
									$end_values,  
									array( "id" => $v["id"], "caption" => $v["caption"], "selected" => true ) );
	                    	}else{
	                        	array_push( 
									$end_values,  
									array( "id" => $v["id"], "caption" => $v["caption"], "selected" => false ) );
							}
							
                       }else{
                                            
		                    if( $selected_value == $v ){
		                        array_push( 
									$end_values,  
									array( "id" => $v, "caption" => $v, "selected" => true ) );
		                    }else{
		                        array_push( 
									$end_values,  
									array( "id" => $v, "caption" => $v, "selected" => false ) );
							}
							
						}
										
					}else{
						
						$v = $v->asArray();
						
	                    if( $selected_value == $v["$field_name"] ){
	                        array_push( 
								$end_values,  
								array( "id" => $v["$field_name"], "caption" => $v["$field_name_in_values"], "selected" => true ) );
	                    }else{
	                        array_push( 
								$end_values,  
								array( "id" => $v["$field_name"], "caption" => $v["$field_name_in_values"], "selected" => false ) );
						}
					}
						
					


				}
				
				$this->form_fields[$i]->value =  $end_values;

				break;
			}//if
		}//for

	}
        


	public function createComboBoxJoinDistintName( $field_name, $table_name, $field_name_in_values, $values_array, $selected_value=null ){
		if( sizeof( $values_array ) == 0 ){
			//do something
		}

		$sof = sizeof( $this->form_fields );

		for ($i=0; $i < $sof; $i++) { 
			
			if( $this->form_fields[$i]->id === $field_name )
			{
				$this->form_fields[$i]->type  = "combo";
				
				$end_values = array();

				foreach ($values_array as $v ){
					$v = $v->asArray();
                                        if($selected_value == $v["$table_name"])
                                            array_push( $end_values,  array( "id" => $v["$table_name"], "caption" => $v["$field_name_in_values"], "selected" => true ) );
                                        else
                                            array_push( $end_values,  array( "id" => $v["$table_name"], "caption" => $v["$field_name_in_values"], "selected" => false ) );

				}
				
				$this->form_fields[$i]->value =  $end_values;

				break;
			}//if
		}//for

	}
    

    
	public function createListBoxJoin( $field_name, $field_name_in_values, $values_array ){
		if( sizeof( $values_array ) == 0 ){
			//do something
		}

		$sof = sizeof( $this->form_fields );

		for ($i=0; $i < $sof; $i++) { 
			
			if( $this->form_fields[$i]->id === $field_name )
			{
				$this->form_fields[$i]->type  = "listbox";
				
				$end_values = array();

				foreach ($values_array as $v ){
					$v = $v->asArray();
					array_push( $end_values,  array( "id" => $v["$field_name"], "caption" => $v["$field_name_in_values"] ) );

				}
				
				$this->form_fields[$i]->value =  $end_values;

				break;
			}//if
		}//for

	}



	public function createComboBox( $field_name, $values ){
		
	}



	public function setValueField( $field_name, $value ){
		$sof = sizeof( $this->form_fields );

		for ($i=0; $i < $sof; $i++) { 
			if( $this->form_fields[$i]->id === $field_name ){
				$this->form_fields[$i]->value = $value;
			}
		}
	}
        

}




class FormComponentField{

	public $id;
	public $caption;
	public $type;
	public $value;
	public $name;
	public $obligatory;
	public $send_as_hidden;
	public $hidden;


	public function __construct( $id, $caption, $type, $value = "", $name = null, $obligatory = false, $hidden = false, $send_as_hidden = false ){
			$this->id 			= $id;
			$this->caption 		= $caption;
			$this->type 		= $type;
			$this->value 		= $value;
			$this->name 		= $name;
			$this->obligatory 	= $obligatory;
			$this->hidden	 	= $hidden;
			$this->send_as_hidden 	= $send_as_hidden;
	}


	public static function obligatorySort( $f1, $f2 ){
	
		if ($f1->obligatory == $f2->obligatory) {
			return 0;
		}
		
		if( $f1->obligatory ) return -1;

		return 1;
	}


	public static function idSort( $f1, $f2 ){
		if ($f1->id == $f2->id) {
			return 0;
		}
		
		return strcmp( $f1->id, $f2->id );
	}
	
}//FormComponentField


