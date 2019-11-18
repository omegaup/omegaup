{include file='redirect.tpl' inline}
{include file='head.tpl' htmlTitle="{#omegaupTitleGroupsScoreboardEdit#}" inline}

<span id="form-data" data-name="group-scoreboards" data-page="edit" data-alias="{$smarty.get.scoreboard}" data-group-alias="{$smarty.get.group}"></span>
<script src="{version_hash src="/js/groups.scoreboards.js"}"></script>

<ul class="nav nav-tabs nav-justified" id="sections">
	<li class="active"><a href="#contests" data-toggle="tab">{#groupEditScoreboardsContests#}</a></li>
</ul>

<div class="tab-content">
	<div class="tab-pane active" id="members">
		{include file='group.scoreboard.edit.contests.tpl' inline}
	</div>
</div>

{include file='footer.tpl' inline}