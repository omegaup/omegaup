{include file='head.tpl' navbarSection='users' htmlTitle="{#omegaupTitleProfile#}" inline}

{if !isset($STATUS_ERROR) && !empty($payload['profile'])}
<script type="text/json" id="payload">{['payload' => $payload['profile'], 'logged_in' => $LOGGED_IN == "1"]|json_encode}</script>
<div id="user-profile"></div>
{js_include entrypoint="user_profile"}
{/if}

{include file='footer.tpl' inline}
