{include file='redirect.tpl'}
{include file='head.tpl' htmlTitle="{#courseStudentsProgress#}"}

<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="view-progress"></div>

<script type="text/javascript" src="{version_hash src="/js/dist/course_students.js"}"></script>
{include file='footer.tpl'}
