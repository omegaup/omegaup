{include file='head.tpl' navbarSection='schools' headerPayload=$headerPayload htmlTitle="{#courseDetails#}" inline}

<div id="course-details"></div>
<script type="text/json" id="payload">{$payload|json_encode}</script>
{js_include entrypoint="course_details"}

{include file='footer.tpl' inline}
