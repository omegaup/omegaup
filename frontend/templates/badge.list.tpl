{include file='head.tpl' htmlTitle="{#omegaupTitleBadges#}"}

{if !isset($STATUS_ERROR)}
<script type="text/json" id="payload">{['logged_in' => $LOGGED_IN == "1"]|json_encode}</script>
<div id="badges-list"></div>
<script type="text/javascript" src="{version_hash src="/js/dist/badge_list.js"}"></script>
{/if}