<?php


class SubmitProblemComponent implements GuiComponent{
	
	private $submitTo;

	function __construct( $submitTo = null ){

		if( is_null($submitTo) )
			$this->submitTo = $_SERVER['PHP_SELF'];
		else
			$this->submitTo = $submitTo;
	}

	function renderCmp(){
	
	    $new_problem = new DAOFormComponent( new Problems( ) );
		$new_problem->hideField(array( "submissions", "visits", "remote_id", "creation_date", "server", "problem_id" ));
	
		?>
			<form action='<php $this->submitTo; ?>' method='POST' enctype='multipart/form-data'>
				<input name='file' type='file'>
				<input name='file_sent' type='hidden'>
				<input type='submit' value='Enviar'>
			</form>
		<?php

		echo $new_problem->renderCmp();
		
		
	}

}