<script type="text/javascript" src="{version_hash src="/js/course.add.students.form.js"}"></script>
<script type="text/javascript" src="{version_hash src="/js/course.add.students.js"}"></script>

<template id="add-student-current-list">
	<table class="table table-striped table-over">
		<thead>
			<th>{#wordsUser#}</th>
			<th>{#contestEditRegisteredAdminDelete#}</th>
		</thead>
		<tbody data-bind="foreach: student">
			<tr>
				<td><a data-bind="text: name, attr: { href: profile }" /></td>
				<td>X</td>
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
							data: { student: getStudentsList() }  }">
			</div>
		</div>
	</div>
</div>
