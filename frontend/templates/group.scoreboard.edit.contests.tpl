<div class="panel panel-primary">
	<div class="panel-body">
		<form class="form" id="scoreboard-add-contest-form">
			<div class="form-group">
				<div class="row">
					<label for="problems">{#wordsContests#}</label>
					<select class='form-control' name='contests' id='contests'>
						<option value=""></option>
					</select>
				</div>
					
				<button class="btn btn-primary" type='submit'>{#groupEditScoreboardsAddContest#}</button>
			</div>			
		</form>
	</div>

	<table class="table table-striped">
		<thead>
			<th>{#wordsContests#}</th>			
			<th></th>			
		</thead>
		<tbody id="scoreboard-contests"></tbody>
	</table>
</div>