{include file='redirect.tpl'}
{include file='head.tpl' htmlTitle="{#groupEditIdentity#}"}

<div id="group_edit_identity"></div>
<script type="text/json" id="payload">{$payload|json_encode}</script>
<script src="{version_hash src="/third_party/js/iso-3166-2.js/iso3166.min.js"}"></script>
<script src="{version_hash src="/js/dist/group_edit_identity.js"}" type="text/javascript"></script>

{include file='footer.tpl'}