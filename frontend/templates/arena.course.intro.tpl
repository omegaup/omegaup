{include file='redirect.tpl' inline}
{include file='head.tpl' htmlTitle="{#enterCourse#}" loadMarkdown=true inline}

<div class="container-fluid">
	<script type="text/json" id="course-payload">{$coursePayload|json_encode}</script>
	<div id="course-intro"></div>
	{js_include entrypoint="course_intro"}
</div>

{include file='footer.tpl' inline}

