{if $STATUS neq ''} 
	<div class="alert alert-danger" id='status'>
		{$STATUS}
	</div>
{else}
	<div class="alert" id='status' style="display: none;">			
	</div>
{/if}
