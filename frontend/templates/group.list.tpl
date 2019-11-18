{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='contests' htmlTitle="{#omegaupTitleGroups#}" inline}

<div id="group_list"></div>

<script type="text/json" id="payload">{$payload|json_encode}</script>
<script type="text/javascript" src="{version_hash src="/js/dist/group_list.js"}"></script>

{include file='footer.tpl' inline}
