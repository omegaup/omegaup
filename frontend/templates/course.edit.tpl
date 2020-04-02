{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='schools' headerPayload=$headerPayload htmlTitle="{#omegaupTitleCourseEdit#}" inline}

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
		<li><a href="#publish" data-toggle="tab">{#contestNewFormAdmissionMode#}</a></li>
		<li><a href="#students" data-toggle="tab">{#courseEditStudents#}</a></li>
		<li><a href="#admins" data-toggle="tab">{#courseEditAdmins#}</a></li>
		<li><a href="#clone" data-toggle="tab">{#courseEditClone#}</a></li>
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
		<div class="tab-pane" id="publish">
			<div></div>
		</div>
		<div class="tab-pane" id="students">
			<div></div>
		</div>
		<div class="tab-pane" id="admins">
			<div></div>
		</div>
		<div class="tab-pane" id="clone">
			<div></div>
		</div>
	</div>
</div>

{js_include entrypoint="course_edit"}
{include file='footer.tpl' inline}
