<div class="panel panel-primary">
	<div class="panel-body">
		<form class="form" id="add-member-form">
			<div class="form-group">
				<label for="member-username">{#wordsMember#}</label>
				<input id="member-username" name="username" value="" type="text" size="20" class="form-control" autocomplete="off" />
			</div>

			<input id="member-user" name="user" value="" type="hidden">

			<button class="btn btn-primary" type='submit'>{#wordsAddMember#}</button>
		</form>
	</div>

	<table class="table table-striped">
		<thead>
			<th>{#wordsUser#}</th>
			<th>{#contestEditRegisteredAdminDelete#}</th>
		</thead>
		<tbody id="group-members"></tbody>
	</table>
</div>