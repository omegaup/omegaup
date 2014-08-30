{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleGroups#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<span id="form-data" data-name="group-scoreboards" data-page="details" data-alias="{$smarty.get.scoreboard}" data-group-alias="{$smarty.get.group}"></span>
<script src="/js/groups.scoreboards.js"></script>

<div class="panel panel-default">
	<div class="panel-body">
		
		<!-- Rank -->
		<div class="post">
			<div class="copy" id="ranking">
				
			</div>
		</div>

	</div>
</div>