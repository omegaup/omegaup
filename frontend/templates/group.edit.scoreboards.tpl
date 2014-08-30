<script src="/js/alias.generate.js"></script>

<div class="panel panel-primary">
	<div class="panel-body">
		<form class="form" id="add-scoreboard-form">
			<div class="form-group">
				<div class="row">
					<div class="form-group col-md-6">
						<label for="title">{#wordsName#}</label>
						<input id="title" name="title" value="" type="text" size="20" class="form-control" autocomplete="off" />
					</div>

					<div class="form-group col-md-6">
						<label for="alias">{#contestNewFormShortTitle_alias_#}</label>
						<input id='alias' name='alias' value='' type='text' class="form-control" disabled="true">
						<p class="help-block">{#contestNewFormShortTitle_alias_Desc#}</p>
					</div>
				</div>
				<div class="row">
					<div class="form-group col-md-6">
						<label for="description">{#groupNewFormDescription#}</label>
						<textarea id='description' name='description' cols="30" rows="5" class="form-control"></textarea>
					</div>
				</div>
					
				<button class="btn btn-primary" type='submit'>{#groupEditScoreboardsAdd#}</button>
			</div>			
		</form>
	</div>

	<table class="table table-striped">
		<thead>
			<th>{#groupEditScoreboards#}</th>			
			<th></th>			
		</thead>
		<tbody id="group-scoreboards"></tbody>
	</table>
</div>