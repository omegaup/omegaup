<script type="text/json" id="header-payload">{$headerPayload|json_encode}</script>
<div id="common-navbar"></div>

<script type="text/javascript" src="{version_hash src="/js/dist/common_navbar.js"}"></script>
{if $CURRENT_USER_IS_ADMIN eq '1'}
  <script type="text/javascript" src="{version_hash src="/js/common.navbar.grader_status.js"}"></script>
{/if}
