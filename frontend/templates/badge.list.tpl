{include file='head.tpl' htmlTitle="{#omegaupTitleBadges#}" inline}

{if !isset($STATUS_ERROR)}
<script type="text/json" id="payload">{['logged_in' => $LOGGED_IN == "1"]|json_encode}</script>
<div id="badges-list"></div>
{js_include entrypoint="badge_list"}
{/if}