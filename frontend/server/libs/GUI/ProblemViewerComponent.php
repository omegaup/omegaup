<?php



class ProblemViewerComponent implements GuiComponent{
	
	private $problema;
	
	public function __construct( $prob = null){
		if(is_null($prob)){
			throw new Exception( "ProblemViewerComponent must be constructed with a Problem VO Object" );
		}
		
		if( ($prob instanceof VO) === false ){
			throw new Exception( "ProblemViewerComponent must be constructed with a Problem VO Object" );
		}
		
		$this->problema = $prob;
		
	}
	
	
	
	
	public function renderCmp(  ){
		?>
			<h1><?php echo $this->problema->getProblemId(); ?>  | <?php echo $this->problema->getTitle(); ?></h1>
		
		<?php
	}
	
}