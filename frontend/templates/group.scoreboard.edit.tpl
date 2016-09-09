{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleGroupsScoreboardEdit#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<span id="form-data" data-name="group-scoreboards" data-page="edit" data-alias="{$smarty.get.scoreboard}" data-group-alias="{$smarty.get.group}"></span>
<script src="/js/groups.scoreboards.js?ver=41dc19"></script>

<ul class="nav nav-tabs nav-justified" id="sections">
	<li class="active"><a href="#contests" data-toggle="tab">{#groupEditScoreboardsContests#}</a></li>
</ul>

<div class="tab-content">
	<div class="tab-pane active" id="members">
		{include file='group.scoreboard.edit.contests.tpl'}
	</div>
</div>

{include file='footer.tpl'}