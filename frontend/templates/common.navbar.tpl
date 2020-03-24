<script type="text/json" id="header-payload">{$headerPayload|json_encode}</script>
<div id="common-navbar"></div>
{if $headerPayload.inContest eq false && (!isset($inArena))}
{js_include entrypoint="common_navbar"}
{/if}
