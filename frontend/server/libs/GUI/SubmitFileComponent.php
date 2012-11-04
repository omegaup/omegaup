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
		?>
			<form action='<?php $this->submitTo; ?>' method='POST' enctype='multipart/form-data'>
				<input name='file_sent' type='hidden'>
				
				<?php function printHtmlParams($name){
					echo "name='$name' placeholder='$name' ";
					if(isset($_POST[ $name ])) echo " value='". $_POST[ $name ] ."' ";
				} ?>
				
				<table border="0">

				<tr><td>contest_alias</td><td><input type='text' <?php printHtmlParams("contest_alias");?> ></td></tr>
				<tr><td>author_username</td><td><input type='text' <?php printHtmlParams("author_username");?> ></td></tr>
				<tr><td>title</td><td><input type='text' <?php printHtmlParams("title");?> ></td></tr>
				<tr><td>source</td><td><input type='text' <?php printHtmlParams("source");?> ></td></tr>
				<tr><td>alias</td><td><input type='text' <?php printHtmlParams("alias");?> ></td></tr>
				<tr><td>time_limit</td><td><input type='text' <?php printHtmlParams("time_limit");?> ></td></tr>
				<tr><td>memory_limit</td><td><input type='text' <?php printHtmlParams("memory_limit");?> ></td></tr>
				<tr><td>points</td><td><input type='text' <?php printHtmlParams("points");?> ></td></tr>

				<tr><td>validator</td><td><select name="validator">
					<option value="remote">remote</option>
					<option value="literal">literal</option>
					<option value="token">token</option>
					<option value="token-caseless">token-caseless</option>
					<option value="token-numeric">token-numeric</option>
					<option value="custom">custom</option>
				</select></td></tr>
								
				<tr><td>order</td><td><select name="order">
					<option value="normal">normal</option>
					<option value="inverse">inverse</option>
				</select></td></tr>
				
				<tr><td></td><td><input name='problem_contents' type='file'></td></tr>
				<tr><td></td><td><input type="submit" name="submit_problem" value="enviar" id="submit_problem"></td></tr>
					
				</table>
			</form>
		<?php
	}
}
