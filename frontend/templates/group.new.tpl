{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleGroupsNew#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

{if !isset($IS_UPDATE)}
	{assign "IS_UPDATE" 0}
{/if}

<span id="form-data" data-name="groups" data-page="new"></span>
<script src="/js/alias.generate.js?ver=bcc72a"></script>
<script src="/js/groups.js?ver=284da8"></script>

<div class="panel panel-primary">
	{if $IS_UPDATE neq 1}
	<div class="panel-heading">
		<h3 class="panel-title">
			{#omegaupTitleContestNew#}
		</h3>
	</div>
	{/if}
	
	<div class="panel-body">
		<form class="new_group_form">
			<div class="row">
				<div class="form-group col-md-6">
					<label for="title">{#wordsName#}</label>
					<input id='title' name='title' value='' type='text' size='30' class="form-control">
				</div>

				<div class="form-group col-md-6">
					<label for="alias">{#contestNewFormShortTitle_alias_#}</label>
					<input id='alias' name='alias' value='' type='text' class="form-control" disabled="true">
					<p class="help-block">{#contestNewFormShortTitle_alias_Desc#}</p>
				</div>
			</div>
				
			<div class="row">
				<div class="form-group col-md-6">
					<label for="description">{#groupNewFormDescription#}</label>
					<textarea id='description' name='description' cols="30" rows="5" class="form-control"></textarea>
				</div>
			</div>
			
			<div class="form-group">
				{if $IS_UPDATE eq 1}
					<button type='submit' class="btn btn-primary">{#groupNewFormUpdateGroup#}</button>
				{else}
					<button type='submit' class="btn btn-primary">{#groupNewFormCreateGroup#}</button>
				{/if}
			</div>
		</form>				
	</div>
</div>
			
{include file='footer.tpl'}