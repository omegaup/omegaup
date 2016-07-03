{assign var="htmlTitle" value="{#omegaupTitleProfile#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

{if !isset($STATUS_ERROR)}

<div class="row" id="inner-content">
	<div class="col-md-2 no-right-padding" id="userbox">
		<div class="panel panel-default" id="userbox-inner">
			<div class="panel-body">
				<div class="thumbnail bottom-margin"> <img src="{$profile.userinfo.gravatar_92}"/></div>
				{if isset($profile.userinfo.email)}
				<div id="profile-edit"><a href="/profile/edit/" class="btn btn-default">{#profileEdit#}</a></div>
				{/if}
			</div>
		</div>
	</div>

	{block name="content"}
	<div class="col-md-10 no-right-padding">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">{if $profile.userinfo.rankinfo.rank > 0}#{$profile.userinfo.rankinfo.rank} - {/if}{$profile.userinfo.username} {if isset($profile.userinfo.country_id)}
						<img src="/media/flags/{$profile.userinfo.country_id|lower}.png" width="16" height="11" title="{$profile.userinfo.country_id}"/> {/if}</h2>
			</div>
{include file='profile.basicinfo.tpl'}
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">{#profileContests#} <span class="badge" id="contests-total">0</span></h2>
			</div>
			<table class="table table-striped" id="contest-results">
				<thead>
					<tr>
						<th>{#profileContestsTableContest#}</th>
						<th>{#profileContestsTablePlace#}</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
			<div id="contest-results-wait"><img src="/media/wait.gif" /></div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">{#profileSolvedProblems#} <span class="badge" id="problems-solved-total">0</span></h2>
			</div>
			<table class="table table-striped" id="problems-solved">
				<thead>
					<tr>
						<th>{#profileSolvedProblemsTableTitle#}</th>
						<th>{#profileSolvedProblemsTableTitle#}</th>
						<th>{#profileSolvedProblemsTableTitle#}</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
			<div id="problems-solved-wait"><img src="/media/wait.gif" /></div>
		</div>

		<div class="panel panel-default no-bottom-margin">
			<div class="panel-heading">
				<h2 class="panel-title">{#profileStatistics#}</h2>
			</div>
			<div class="panel-body">
				<div id="verdict-chart"><img src="/media/wait.gif" /></div>
			</div>
		</div>

	</div>
	{/block}

</div>
{/if}

<div id="username" style="display:none" data-username="{IF isset($smarty.get.username)}{$smarty.get.username|replace:"\\":""}{ELSE}{$CURRENT_USER_USERNAME}{/IF}">
</div>

<script src="/js/profile.js?ver=36609f"></script>

{include file='footer.tpl'}
