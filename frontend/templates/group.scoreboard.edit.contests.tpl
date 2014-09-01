<div class="panel panel-primary">
	<div class="panel-body">
		<form class="form" id="scoreboard-add-contest-form">
			<div class="form-group">
				<div class="row">
					<label for="problems">{#wordsContests#}</label>
					<select class='form-control' name='contests' id='contests'>
						<option value=""></option>
					</select>
					
					<div class="form-group col-md-6">
						<label for="only_ac">{#groupNewFormOnlyAC#}</label>
						<select name='only_ac' id='only_ac' class="form-control">
							<option value='0'>{#wordsNo#}</option>
							<option value='1'>{#wordsYes#}</option>							
						</select>
					</div>
				</div>
						
				<div class="row">
					<div class="form-group col-md-6">
						<label for="weight">{#groupNewFormWeight#}</label>
						<input id='weight' name='weight' value='1.0' type='text' size='4' class="form-control">
					</div>
				</div>
					
				<button class="btn btn-primary" type='submit'>{#groupEditScoreboardsAddContest#}</button>
			</div>			
		</form>
	</div>

	<table class="table table-striped">
		<thead>
			<th>{#wordsContests#}</th>
			<th>{#groupNewFormOnlyAC#}</th>
			<th>{#groupNewFormWeight#}</th>
			<th></th>			
		</thead>
		<tbody id="scoreboard-contests"></tbody>
	</table>
</div>