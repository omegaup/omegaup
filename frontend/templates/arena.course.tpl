{assign var="htmlTitle" value="{#courseDetails#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<script src="/js/course.js?ver=5e70ed"></script>
<script type="text/html" id="assignments-list">
<div id="intro-page" class="course">
	<div class="panel panel-default">

		<div class="panel-heading">
			<h2 class="panel-title" >{#courseContents#}</h2>
		</div>
		
		<div class="panel-body table-responsive">
				<div id="course-contents">
					<h2 id="title" data-bind="text: header"></h2>
					<p id="description" data-bind="text: description"></p>
					<table class="assignments-list table table-striped table-hover">
							<thead><tr>
									<th>{#wordsAssignment#}assignment</th>
									<th>{#wordsAssignmentType#}type</th>
									<th class="time" data-bind="visible: showTimes">{#wordsStartTime#}</th>
									<th class="time" data-bind="visible: showTimes">{#wordsEndTime#}</th>
							</tr></thead>
							<tbody data-bind="foreach: page">
								<tr>
								</tr>
							</tbody>
					</table>
				</div>
		</div>
	</div>
</div>
</script>

{include file='footer.tpl'}
