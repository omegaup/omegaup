{include file='redirect.tpl'}
{include file='head.tpl'}

<script type="text/json" id="payload">{$payload|json_encode}</script>
<div id="qualitynomination-list"></div>
<script type="text/javascript" src="{version_hash src="/js/dist/qualitynomination_list.js"}"></script>

{include file='footer.tpl'}
