{include file='head.tpl' htmlTitle="{#omegaupTitleProfile#}"}

{if !isset($STATUS_ERROR)}
<script type="text/json" id="payload">{['logged_in' => $LOGGED_IN == "1"]|json_encode}</script>
<div id="badges-list">HEE HEE</div>
<script type="text/javascript" src="{version_hash src="/js/dist/badge_list.js"}"></script>
{/if}