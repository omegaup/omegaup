{include file='redirect.tpl'}
{include file='head.tpl' htmlTitle="{#omegaupTitleCourseEdit#}"}

<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title course-header">{#frontPageLoading#}</h3>
	</div>
	<div class="page-header">
		<h1><span><a class="course-header"></a></span></h1>
	</div>
	<ul class="nav nav-tabs" id="sections">
		<li class="active"><a href="#edit" data-toggle="tab">{#courseEdit#}</a></li>
		<li><a href="#assignments" data-toggle="tab">{#wordsAssignments#}</a></li>
		<li><a href="#problems" data-toggle="tab">{#wordsProblems#}</a></li>
		<li><a href="#students" data-toggle="tab">{#courseEditStudents#}</a></li>
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
	</div>
</div>

<script type="text/javascript" src="{version_hash src="/js/dist/course_edit.js"}"></script>
{include file='footer.tpl'}
