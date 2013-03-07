{include file='head.tpl'}
{include file='mainmenu.tpl'}
<div style="width: 920px; position: relative; margin: 0 auto 0 auto; ">
	<table>
	<tr>
		<td>
			<div class="post footer" style="width: 130px; min-height: 300px;">
				<div class="copy" >
				<a href='/api/explorer/session/'>Session</a><br>
				<a href='/api/explorer/user/'>User</a>
			</div>
			</div>
		</td>


		<td >
			<div class="post" style="width: 760px; min-height: 300px;">
				<div class="copy" >
					{$msg}
					{if $msg eq 'API_NO_METHOD'}
						<table>

							{foreach from=$METHODS item=CMETHOD}
							<tr>
								<!--<td>api/controoler/</td><td>{$CMETHOD.name}</td><td>( {$CMETHOD.params} )</td>-->
								<td><code><a href="{$CMETHOD.name}/">omegaup.com/api/{$CONTROLLER_NAME}/{$CMETHOD.name}/{$CMETHOD.params}</a></code></td>
							</tr>
							{/foreach}
						</table>
					{/if}

					{if $msg eq 'API_NO_CONTROLLER'}
						here be the controllers
					{/if}

					{if $msg eq 'API_EXECUTED'}
					<code>
						{$API_RESULT}
					</code>
					{/if}

				</div>
			</div>
		</td>
	</tr>
	</table>
</div>




{include file='footer.tpl'}

