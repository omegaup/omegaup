{include file='head.tpl' navbarSection='schools' headerPayload=$headerPayload htmlTitle="{#courseDetails#}" inline}

<script src="{version_hash src="/js/course.js"}"></script>

<script type="text/html" id="assignments-list">
<h3 data-bind="text: header"></h3>
<table class="assignments-list table table-striped table-hover">
	<thead><tr>
		<th>{#wordsAssignment#}</th>
		<th>{#wordsProgress#}</th>
		<th class="time">{#wordsStartTime#}</th>
		<th class="time">{#wordsEndTime#}</th>
		{if $showRanking}
		<th class="time">{#courseDetailsScoreboard#}</th>
		{/if}
	</tr></thead>
	<tbody data-bind="foreach: assignment">
		<tr>
			<td><a data-bind="text: name,
                              attr: { href: assignmentUrl }" /></td>
			<td data-bind="text: progress" />
			<td data-bind="text: startTime" />
			<td data-bind="text: finishTime" />
			{if $showRanking}
			<td>
				<a data-bind="attr: { href: scoreboardUrl }" class="glyphicon glyphicon-link">{#wordsPublic#}</a>
				<a data-bind="attr: { href: scoreboardUrlAdmin }" class="glyphicon glyphicon-link">{#wordsAdmin#}</a>
				<a data-bind="attr: { href: adminURL }" class="glyphicon glyphicon-dashboard">{#wordsRuns#}</a>
			</td>
			{/if}
		</tr>
	</tbody>
</table>
<div class="container-fluid" data-bind="if: course.isAdmin">
    <div class="row">
        <a class="btn btn-primary pull-right"
           data-bind="text: newLabel, attr: { href: course.addAssignmentUrl + assignmentType + '/' }"></a>
    </div>
</div>
</script>

<script type="text/html" id="course-info-template">
<div id="intro-page" class="course">
	<div class="panel">
		<div class="panel-header">
			<div class="pull-right" data-bind="if: isAdmin">
				<a class="btn btn-primary" data-bind="attr: { href: editUrl }">{#wordsEditCourse#}</a>
			</div>
			<div class="">
				<a style="text-decoration:none"><h1 id="title" data-bind="text: name"></h1></a>
				<p id="description" data-bind="text: description" class="container-fluid"></p>
			</div>
		</div>
		<div class="panel-body table-responsive">
			<div data-bind="if: isAdmin">
				<span>{#courseStudentCountLabel#} <span data-bind="text: student_count"></span>
				<div class="pull-right">
					<a class="btn btn-primary" data-bind="attr: { href: scoreboardUrl }">{#courseStudentsProgress#}</a>
					<a class="btn btn-primary" data-bind="attr: { href: addStudentsUrl }">{#wordsAddStudent#}</a>
				</div>
			</div>
			<div id="course-contents">
                <span data-bind="template: { name: 'assignments-list',
                                             data: { header: '{#wordsHomework#}',
                                                     newLabel: '{#wordsNewHomework#}',
                                                     course: $data,
                                                     assignment: homework,
                                                     assignmentType: 'homework' } } "></span>
                <span data-bind="template: { name: 'assignments-list',
                                             data: { header: '{#wordsTest#}',
                                                     newLabel: '{#wordsNewTest#}',
                                                     course: $data,
                                                     assignment: test,
                                                     assignmentType: 'test' } } "></span>
			</div>
		</div>
	</div>
</div>
</script>

<div id="course-info" data-bind="template: 'course-info-template'"></div>
{include file='footer.tpl' inline}
