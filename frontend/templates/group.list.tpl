{include file='redirect.tpl'}
{include file='head.tpl' htmlTitle="{#omegaupTitleGroups#}"}

<div id="group_list"></div>

<script type="text/json" id="payload">{$payload|json_encode}</script>
<script type="text/javascript" src="{version_hash src="/js/dist/group_list.js"}"></script>

{include file='footer.tpl'}
