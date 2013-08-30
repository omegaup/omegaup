{if $STATUS_ERROR neq ''} 
	<div class="alert alert-danger" id='status'>
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<span class="message">{$STATUS_ERROR}</span>
	</div>
{else if $STATUS_SUCCESS neq ''}
	<div class="alert alert-success" id='status'>
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<span class="message">{$STATUS_SUCCESS}</span>
	</div>
{else}
	<div class="alert" id='status' style="display: none;">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<span class="message"></span>
	</div>
{/if}
