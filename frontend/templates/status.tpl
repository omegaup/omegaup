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

<script type="text/javascript" src="/js/status.dismiss.js?ver=474f8b"></script>
