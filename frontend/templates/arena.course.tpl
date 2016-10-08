{assign var="htmlTitle" value="{#courseDetails#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<script src="/js/course.js?ver=963091"></script>
<script type="text/javascript" src="/third_party/js/knockout-4.3.0.js?ver=059d58"></script>
<script type="text/javascript" src="/third_party/js/knockout-secure-binding.min.js"></script>
<script type="text/html" id="assignments-list">
<div id="intro-page" class="course">
	<div class="panel">
		<!-- <div class="panel-heading">
			<h2 class="panel-title" >{#courseContents#}</h2>
		</div> -->
		<div class="page-header">
			<a style="text-decoration:none"><h1 id="title" data-bind="text: name"></h1></a>
			<p id="description" data-bind="text: description" class='container'></p>
		</div>
		<div class="panel-body table-responsive">
			<div id="course-contents">
				<h3>{#wordsHomework#}</h3>
				<table class="assignments-list table table-striped table-hover">
					<thead><tr>
						<th>{#wordsAssignment#}Assignment</th>
						<th class="time">{#wordsStartTime#}</th>
						<th class="time">{#wordsEndTime#}</th>
					</tr></thead>
					<tbody data-bind="foreach: homework">
						<tr>
							<td data-bind="text: name" />
							<td data-bind="text: startTime" />
							<td data-bind="text: finishTime" />
						</tr>
					</tbody>
				</table>
				<h3>{#wordsTest#}</h3>
				<table class="test-list table table-striped table-hover">
					<thead><tr>
						<th>{#wordsAssignment#}Assignment</th>
						<th class="time">{#wordsStartTime#}</th>
						<th class="time">{#wordsEndTime#}</th>
					</tr></thead>
					<tbody data-bind="foreach: test">
						<tr>
							<td data-bind="text: name" />
							<td data-bind="text: startTime" />
							<td data-bind="text: finishTime" />
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
</script>

<div id="course-info" data-bind="template: 'assignments-list'">
<div>
{include file='footer.tpl'}
