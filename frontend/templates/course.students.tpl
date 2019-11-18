{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='schools' htmlTitle="{#courseStudentsProgress#}" inline}

<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="view-progress"></div>

{js_include entrypoint="course_students"}
{include file='footer.tpl' inline}
