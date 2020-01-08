{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='schools' headerPayload=$headerPayload htmlTitle="{#enterCourse#}" inline}

<div class="container-fluid">
	<div id="course-submissions-list"></div>
	{js_include entrypoint="course_submissions_list"}
</div>

{include file='footer.tpl' inline}
