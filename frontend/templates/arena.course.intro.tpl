{include file='head.tpl' htmlTitle="{#enterCourse#}"}

<div id="intro-page" class="course">
	<script type="text/json" id="course-payload">{$course_payload|json_encode}</script>
	<div id="course-intro"></div>
	<script type="text/javascript" src="{version_hash src="/js/dist/course_intro.js"}"></script>
</div>

{include file='footer.tpl'}

