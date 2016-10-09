{assign var="htmlTitle" value="{#courseDetails#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<script src="/js/course.js?ver=989549"></script>
<script type="text/javascript" src="/third_party/js/knockout-4.3.0.js?ver=059d58"></script>
<script type="text/javascript" src="/third_party/js/knockout-secure-binding.min.js"></script>

<script type="text/html" id="assignments-list">
<h3 data-bind="text: header"></h3>
<table class="assignments-list table table-striped table-hover">
	<thead><tr>
		<th>{#wordsAssignment#}</th>
		<th class="time">{#wordsStartTime#}</th>
		<th class="time">{#wordsEndTime#}</th>
        <th>{#wordsProgress#}</th>
	</tr></thead>
	<tbody data-bind="foreach: assignment">
		<tr>
			<td><a data-bind="text: name,
                              attr: { href: assignmentUrl }" /></td>
			<td data-bind="text: startTime" />
			<td data-bind="text: finishTime" />
            <td data-bind="text: progress" />
		</tr>
	</tbody>
</table>
<div class="container" data-bind="visible: course.isAdmin">
    <div class="row">
        <a class="btn btn-primary pull-right"
           data-bind="text: newLabel, attr: { href: course.addAssignmentUrl }"></a>
    </div>
</div>
</script>

<script type="text/html" id="course-info-template">
<div id="intro-page" class="course">
	<div class="panel">
		<div class="page-header">
			<a style="text-decoration:none"><h1 id="title" data-bind="text: name"></h1></a>
			<p id="description" data-bind="text: description" class='container'></p>
		</div>
		<div class="panel-body table-responsive">
			<div id="course-contents">
                <span data-bind="template: { name: 'assignments-list',
                                             data: { header: '{#wordsHomework#}',
                                                     newLabel: '{#wordsNewHomework#}',
                                                     course: $data,
                                                     assignment: homework } } "></span>
                <span data-bind="template: { name: 'assignments-list',
                                             data: { header: '{#wordsTest#}',
                                                     newLabel: '{#wordsNewTest#}',
                                                     course: $data,
                                                     assignment: test } } "></span>
			</div>
		</div>
	</div>
</div>
</script>

<div id="course-info" data-bind="template: 'course-info-template'">
<div>
{include file='footer.tpl'}
