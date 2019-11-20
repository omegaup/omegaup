{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='contests' htmlTitle="{#omegaupTitleGroups#}" inline}

<div id="group_list"></div>

<script type="text/json" id="payload">{$payload|json_encode}</script>
{js_include entrypoint="group_list"}

{include file='footer.tpl' inline}
