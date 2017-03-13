{include file='redirect.tpl'}
{include file='head.tpl' htmlTitle="{#courseStudentsProgress#}"}

<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="view-student"></div>

<script type="text/javascript" src="{version_hash src="/js/dist/course_student.js"}"></script>
{include file='footer.tpl'}
