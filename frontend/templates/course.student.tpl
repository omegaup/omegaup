{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='schools' htmlTitle="{#courseStudentsProgress#}" inline}

<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="view-student"></div>

<script type="text/javascript" src="{version_hash src="/js/dist/course_student.js"}"></script>
{include file='footer.tpl' inline}
