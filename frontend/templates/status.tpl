{if isset($STATUS_ERROR) && $STATUS_ERROR != ''}
	<div class="alert alert-danger" id='status'>
		<button type="button" class="close" id="alert-close">&times;</button>
		<span class="message">{$STATUS_ERROR}</span>
	</div>
{else if isset($STATUS_SUCCESS) && $STATUS_SUCCESS != ''}
	<div class="alert alert-success" id='status'>
		<button type="button" class="close" id="alert-close">&times;</button>
		<span class="message">{$STATUS_SUCCESS}</span>
	</div>
{else}
	<div class="alert" id='status' style="display: none;">
		<button type="button" class="close" id="alert-close">&times;</button>
		<span class="message"></span>
	</div>
{/if}
{if $smarty.const.OMEGAUP_MAINTENANCE}
	<div id="announcement" class="alert alert-info">
		{$smarty.const.OMEGAUP_MAINTENANCE}
	</div>
{/if}
{if isset($CURRENT_USER_HAS_ACCEPTED_PRIVACY_POLICY) && !$CURRENT_USER_HAS_ACCEPTED_PRIVACY_POLICY}
	<div class="alert alert-danger" id='status'>
		<button type="button" class="close" id="alert-close"></button>
		<span class="message">{#privacyPolicyNotAcceptedYet#}</span>
	</div>
{/if}

<script type="text/javascript" src="{version_hash src="/js/status.dismiss.js"}"></script>
