{include file='head.tpl' htmlTitle="{#omegaupTitleCodersofthemonth#}" inline}
{js_include entrypoint="coder_of_the_month"}
<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="coder-of-the-month"></div>
{include file='footer.tpl' inline}
