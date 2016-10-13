<div class="panel">
	<div class="panel-body">
		<form class="form" id="add-member-form">
			<div class="form-group">
				<label for="member-username">{#wordsStudent#}</label>
				<span data-toggle="tooltip" data-placement="top" title="Add students using they username in omegaup"  class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
				<input id="member-username" name="username" value="" type="text" size="20" class="form-control" autocomplete="off" />
			</div>
			<div class="form-group">
				<button class="btn btn-primary" type='submit'>{#wordsAddStudent#}</button>
			</div>
		</form>
		<div>
			<table class="table table-striped table-over">
				<thead>
					<th>{#wordsUser#}</th>
					<th>{#contestEditRegisteredAdminDelete#}</th>
				</thead>
				<tbody id="group-members"></tbody>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript" src="/js/course.assignment.add.problems.form.js?ver=ac0d8b"></script>