{include file='head.tpl' htmlTitle="{#omegaupTitleBadges#}" inline}

{if !isset($STATUS_ERROR)}
<script type="text/json" id="payload">{['badge' => $badge_alias, 'logged_in' => $LOGGED_IN == "1"]|json_encode}</script>
<div id="badge-details"></div>
{js_include entrypoint="badge_details"}
{/if}

{include file='footer.tpl' inline}
