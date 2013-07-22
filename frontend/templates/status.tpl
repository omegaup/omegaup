{if $STATUS neq ''} 
	<div class="post footer">
		<div class="copy error" id='status'>
			{$STATUS}
		</div>
	</div>
{else}
	<div class="post footer" style="display: none;">
		<div class="copy error" id='status'>			
		</div>
	</div>
{/if} 