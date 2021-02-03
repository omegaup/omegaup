<script type="text/json" id="header-payload">{$headerPayload|json_encode}</script>
{if $headerPayload.inContest eq false && (!isset($inArena))}
<div id="common-navbar"></div>
{js_include entrypoint="common_navbar_v2"}
{/if}
