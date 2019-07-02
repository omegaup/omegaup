{include file='head.tpl' htmlTitle="{#omegaupTitleBadges#}"}

{if !isset($STATUS_ERROR)}
<script type="text/json" id="payload">{['badge' => $badge, 'logged_in' => $LOGGED_IN == "1"]|json_encode}</script>
<div id="user-profile"></div>
{/if}

{include file='footer.tpl'}
