{include file='head.tpl' htmlTitle="{#omegaupTitleSchoolProfile#}" inline}

{if !isset($STATUS_ERROR)}
<script type="text/json" id="payload">{$details|json_encode}</script>
<div id="school-profile"></div>
{js_include entrypoint="school_profile"}
{/if}

{include file='footer.tpl' inline}
