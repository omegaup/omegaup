{include file='head.tpl' htmlTitle="{#omegaupTitleSchoolProfile#}" inline}

{if !isset($STATUS_ERROR)}
<script type="text/json" id="payload">{['profile' => $profile.details]|json_encode}</script>
{/if}

{include file='footer.tpl' inline}
