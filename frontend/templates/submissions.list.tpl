{include file='head.tpl' htmlTitle="{#omegaupTitleLatestSubmissions#}" inline}
<script type="text/json" id="submissions-payload">{$submissionsPayload|json_encode}</script>
<div id="omegaup-submissions-list"></div>
{js_include entrypoint="submissions_list"}
{include file='footer.tpl' inline}