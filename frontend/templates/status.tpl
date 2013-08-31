{if $STATUS_ERROR neq ''} 
	<div class="alert alert-danger" id='status'>
		<button type="button" class="close" id="alert-close">&times;</button>
		<span class="message">{$STATUS_ERROR}</span>
	</div>
{else if $STATUS_SUCCESS neq ''}
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

<script type="text/javascript">
	$("#alert-close").click(function () {
		$("#status").slideUp();
	});
</script>