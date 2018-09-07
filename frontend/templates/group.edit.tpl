{include file='redirect.tpl'}
{include file='head.tpl' htmlTitle="{#omegaupTitleGroupsEdit#}"}

<span id="form-data" data-name="groups" data-page="edit" data-alias="{$smarty.get.group}"></span>
<script src="{version_hash src="/js/groups.js"}"></script>

<ul class="nav nav-tabs nav-justified" id="sections">
	<li class="active"><a href="#members" data-toggle="tab">{#groupEditMembers#}</a></li>
	<li><a href="#scoreboards" data-toggle="tab">{#groupEditScoreboards#}</a></li>
	{if $IS_ORGANIZER}
		<li><a href="#identities" data-toggle="tab">{#groupEditIdentities#}</a></li>
	{/if}
</ul>

<div class="tab-content">
	<div class="tab-pane active" id="members">
		{include file='group.edit.members.tpl'}
	</div>
	<div class="tab-pane" id="scoreboards">
		{include file='group.edit.scoreboards.tpl'}
	</div>
	{if $IS_ORGANIZER}
		<div class="tab-pane" id="identities">
			{include file='group.edit.identities.tpl'}
		</div>
	{/if}
</div>

{include file='footer.tpl'}