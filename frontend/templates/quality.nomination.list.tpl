{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='problems' inline}

<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="qualitynomination-list"></div>
<script type="text/javascript" src="{version_hash src="/js/dist/qualitynomination_list.js"}"></script>

{include file='footer.tpl' inline}
