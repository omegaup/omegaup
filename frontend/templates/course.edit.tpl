{include file='redirect.tpl'}
{include file='head.tpl' htmlTitle="{#omegaupTitleCourseEdit#}"}

<div class='panel'>
	<div class="page-header">
		<h1><span><a class="course-header">{#frontPageLoading#}</a></span></h1>
	</div>

	<ul class="nav nav-tabs" id="sections">
		<li class="active"><a href="#edit" data-toggle="tab">{#courseEdit#}</a></li>
		<li><a href="#assignments" data-toggle="tab">{#wordsAssignments#}</a></li>
		<li><a href="#problems" data-toggle="tab">{#wordsProblems#}</a></li>
		<li><a href="#students" data-toggle="tab">{#courseEditAddStudents#}</a></li>
		<li><a href="#view-progress" data-toggle="tab">{#courseViewProgress#}</a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="edit">
			<div></div>
		</div>
		<div class="tab-pane" id="assignments">
			<div class="list"></div>
			<div class="form"></div>
		</div>
		<div class="tab-pane" id="problems">
			<div class="list"></div>
			<div class="form"></div>
		</div>
		<div class="tab-pane" id="students">
			<div></div>
		</div>
		<div class="tab-pane" id="view-progress">
			<div></div>
		</div>
	</div>
</div>

<script type="text/javascript" src="{version_hash src="/js/dist/course_edit.js"}"></script>
{include file='footer.tpl'}
