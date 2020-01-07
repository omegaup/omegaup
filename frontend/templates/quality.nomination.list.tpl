{include file='redirect.tpl' inline}
{include file='head.tpl' htmlTitle="{#qualityNomination#}" navbarSection='problems' inline}

<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="qualitynomination-list"></div>
{js_include entrypoint="qualitynomination_list"}

{include file='footer.tpl' inline}
