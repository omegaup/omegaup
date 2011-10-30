<?php


class TableComponent implements GuiComponent{


	private $header;
	protected $rows;	
	private $actionFunction;
	private $actionField;
	
	private	$actionSendID;
	private $renderRowIds;
	private $specialRender;
	
	private $noDataText;

	protected $simple_render;

	public function __construct
	(
			$header = array(), 
			$rows = array()
	){
		$this->header = $header;
		$this->rows = $rows;
		$this->specialRender = array();
		$renderRowIds = null;
		$this->noDataText = "No hay datos para mostrar.";
		$this->simple_render = false;
	}
	
	



	public function renderRowId( $prefix )
	{
		$this->renderRowIds = $prefix;
	}
	



	public function addNoData ( $msg )
	{
		$this->noDataText = $msg;
	}




	public function addRow( $row )
	{
		array_push($this->rows, $row);
	}
	
	
	public function addOnClick( $actionField , $actionFunction, $sendJSON = false, $sendId = false )
	{
		$this->actionField 	  	= $actionField;
		$this->actionFunction 	= $actionFunction;
		$this->actionSendJSON 	= $sendJSON;
		$this->actionSendID 	= $sendId;		
	}
	

	
	public function addColRender( $id, $fn )
	{
		array_push( $this->specialRender, array( $id => $fn ) );
	}
	


	public function renderCmp(  )
	{
		
		
		if(sizeof($this->rows) == 0){
			return $this->noDataText;
		}
		

		$html = '<table border="0" style="width:100%">';
		$html .= '<tr align = "left">';
		
		foreach ( $this->header  as $key => $value){
			$html .= '<th>' . $value . '</th>';			
		}
		

		$html .= '</tr>';
		
		//cicle trough rows
		for( $a = 0; $a < sizeof($this->rows) ; $a++ ){

			//@TODO pagination should be implemented here
			/*if($a == 50){
				$html .= "<tr style='background-color:#3F8CE9; color:white; text-align:center;'><td colspan=" .sizeof($this->header). ">Mostrar siguientes 50</td></tr>";
				break;
			}*/

			//si el row no es un array intentar convertirlo
			if( !is_array($this->rows[$a]) ){
				$row = $this->rows[$a]->asArray();
			}else{
				$row = $this->rows[$a];
			}


			if( isset($this->actionField)){
				if($this->actionSendJSON){
					
					$html .= '<tr style=" cursor: pointer;" onClick="' . $this->actionFunction. '( \''. urlencode(json_encode($row)) . '\' )" ';

				}elseif($this->actionSendID){
					$html .= '<tr style=" cursor: pointer;" onClick="' . $this->actionFunction. '( \'' . $this->renderRowIds . $a . '\' )" ';

				}else{
					$html .= '<tr style=" cursor: pointer;" onClick="' . $this->actionFunction. '( ' . $row[ $this->actionField ] . ' )" ';		
							
				}
				
			}else{
				$html .= '<tr ';
			}			

			//renderear ids o no
			if($this->renderRowIds != null)
			{
				$html .= " id=\"". $this->renderRowIds . $a ."\" ";
			}

			//efecto
            $html .= ' onmouseover="this.style.backgroundColor = \'#D7EAFF\'" onmouseout="this.style.backgroundColor = \'white\'" >';
            
			$i = 0;
			

			if($this->simple_render){
				/**
				  *
				  *	Just print the damn rows
				  **/				
				foreach($this->rows[$a] as $column){
					if($i++ % 2 == 0){
						$bgc = "";
					}else{
						$bgc = ""; //"rgba(200, 200, 200, 0.199219)";
					}
					$html .=  "<td align='left' style='background-color:".$bgc.";'>" . $column . "</td>";
				}

			}else{
				/**
				  *
				  *	Render based on the header
				  **/
				foreach ( $this->header  as $key => $value){
			
					if( array_key_exists( $key , $row )){

						//ver si necesita rendereo especial
						$found = null;
					
						for( $k = 0; $k < sizeof($this->specialRender); $k++ ){
							
							if( array_key_exists( $key, $this->specialRender[$k] )){
									$found = $this->specialRender[$k];
							}
						}
						
						if($i++ % 2 == 0){
							$bgc = "";
						}else{
							$bgc = ""; //"rgba(200, 200, 200, 0.199219)";
						}
						
						if( $found ){
							
							$html .=  "<td align='left' style='background-color:".$bgc.";'>" . call_user_func( $found[$key] , $row[ $key ], $row ) . "</td>";							

						}else{
							$html .=  "<td align='left' style='background-color:".$bgc.";'>" . $row[ $key ] . "</td>";
						}
						

					}//if array key exists

				}//foreach

			}//simple_render

			
			
			$html .='</tr>';
		}
		
		$html .= "</table>";
		

		return $html;


	}
}




class SimpleTableComponent extends TableComponent{
	
	function __construct()
	{
		parent::__construct();
		$this->simple_render = true;
	}

	function addRow( $foo )
	{
		$row = array();
		
		$n_args =  func_num_args();

		for ($ai=0; $ai < $n_args; $ai++)
		{ 
			array_push( $row , func_get_arg( $ai ) );	
		}

		parent::addRow( $row );
	}


	function setRows( $rows )
	{
		$this->rows = $rows;
	}	

}





