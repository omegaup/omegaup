<?php



class RunsListComponent implements GuiComponent{
	
	private  $user_id;
	
	public function __construct(){
		$this->user_id = null;
	}
	
	
	public function setUser($uid){
		//@todo test for user
		$this->user_id = $uid;
		
	}
	
	
	private function getList(){
		$query = new Runs();
		
		if(!is_null($this->user_id)){
			$query->setUserId( $this->user_id );
		}
		
		return RunsDAO::search($query);
	}
	
	public function renderCmp(){
		
		$list = $this->getList(  );
		
		
		echo "<h2>Runs</h2>";
		
		//order them?
		if(sizeof($list) == 0){
			echo "no hay runs";
			return;
		}
		
		?>
			<table border="0">
				<tr><th>run id</th></tr>
				<tr>
				<?php
				foreach($list as $run){
					?><td>
						
					</td><?php
				}
				?>
				</tr>
			</table>
		<?php
	}
	
}