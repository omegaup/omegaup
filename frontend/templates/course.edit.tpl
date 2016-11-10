{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleCourseEdit#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class='panel'>
	<div class="page-header">
		<h1><span>{#frontPageLoading#}</span> <small></small></h1>
	</div>

	<ul class="nav nav-tabs" id="sections">
		<li class="active"><a href="#edit" data-toggle="tab">{#courseEdit#}</a></li>
		<li><a href="#add-assignment" data-toggle="tab">{#courseEditAddAssignment#}</a></li>
		<li><a href="#add-problems" data-toggle="tab">{#courseEditAddProblems#}</a></li>
		<li><a href="#add-students" data-toggle="tab">{#courseEditAddStudents#}</a></li>
		<li><a href="#view-progress" data-toggle="tab">{#courseViewProgress#}</a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="edit">
			{include file='course.new.form.tpl'}
		</div>
		<div class="tab-pane" id="add-assignment">
			{include file='course.assignment.new.form.tpl'}
		</div>
		<div class="tab-pane" id="add-problems">
			{include file='course.assignment.add.problems.form.tpl'}
		</div>
		<div class="tab-pane" id="add-students">
			{include file='course.assignment.add.students.form.tpl'}
		</div>
		<div class="tab-pane" id="view-progress">
			{include file='course.view.progress.tpl'}
		</div>
	</div>
</div>

<script type="text/javascript" src="/js/course.edit.js?ver=96e7cf"></script>
<script type="text/javascript" src="/js/course.assignment.new.js?ver=33c32a"></script>
<script type="text/javascript" src="/js/course.assignment.add.problems.js?ver=95d473"></script>
{include file='footer.tpl'}
