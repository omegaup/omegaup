{include file='redirect.tpl' inline}
{include file='head.tpl' htmlTitle="{#enterCourse#}" loadMarkdown=true inline}

<div class="container-fluid">
	<script type="text/json" id="course-payload">{$coursePayload|json_encode}</script>
	<div id="course-intro"></div>
	<script type="text/javascript" src="{version_hash src="/js/dist/course_intro.js"}"></script>
</div>

{include file='footer.tpl' inline}

