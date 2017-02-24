<script type="text/javascript" src="{version_hash src="/js/course.add.students.form.js"}"></script>

<template id="add-student-current-list">
	<table class="table table-striped table-over">
		<thead>
			<th>{#wordsUser#}</th>
			<th>{#contestEditRegisteredAdminDelete#}</th>
		</thead>
		<tbody data-bind="foreach: students">
			<tr>
				<td><a data-bind="text: name || username, attr: { href: profile }" /></td>
				<td><button type="button" class="close" data-bind="click: remove">&times;</button></td>
			</tr>
		</tbody>
	</table>
</template>

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
			<div id="add-students-list-table"
				 data-bind="template: { name: 'add-student-current-list',
				            data: { students: students() } }">
			</div>
		</div>
	</div>
</div>
