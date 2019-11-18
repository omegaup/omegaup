{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='problems' inline}

<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="qualitynomination-details"></div>
<div id="qualitynomination-demotionpopup"></div>
{js_include entrypoint="qualitynomination_details"}

{include file='footer.tpl' inline}
