{include file='head.tpl' htmlTitle="{#omegaupTitleBadges#}"}

{if !isset($STATUS_ERROR)}
<script type="text/json" id="payload">{['badge' => $badge_alias, 'logged_in' => $LOGGED_IN == "1"]|json_encode}</script>
<div id="badge-details"></div>
<script type="text/javascript" src="{version_hash src="/js/dist/badge_details.js"}"></script>
{/if}

{include file='footer.tpl'}
