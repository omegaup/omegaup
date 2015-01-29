<div class="panel panel-primary">
	<div class="panel-body">
		<form class="form" id="scoreboard-add-contest-form">
			<div class="row">
				<div class="form-group col-md-6">
					<label for="contests">{#wordsContests#}</label>
					<select class='form-control' name='contests' id='contests'>
						<option value=""></option>
					</select>
				</div>

				<div class="form-group col-md-6">
					<label for="only-ac">{#groupNewFormOnlyAC#}</label>
					<select name='only-ac' id='only-ac' class="form-control">
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

			<div class="row">
				<div class="form-group col-md-6">
					<button class="btn btn-primary" type='submit'>{#groupEditScoreboardsAddContest#}</button>
				</div>
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
