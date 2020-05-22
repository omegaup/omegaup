{include file='head.tpl' recaptchaFile='https://www.google.com/recaptcha/api.js' htmlTitle="{#omegaupTitleLogin#}" inline}

<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="login-sign-in"></div>
{js_include entrypoint="login_sign_in"}

{include file='footer.tpl' inline}
