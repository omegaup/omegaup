{include file='head.tpl' navbarSection='users' headerPayload=$headerPayload htmlTitle="{#omegaupTitleProfile#}" inline}

{if !isset($STATUS_ERROR)}

<div class="row" id="inner-content">
	<div class="col-md-2 no-right-padding" id="userbox">
		<div class="panel panel-default" id="userbox-inner">
			<div class="panel-body">
				<div class="thumbnail bottom-margin"> <img src="{$profile.gravatar_92}"/></div>
				{if isset($profile.email)}
				<div id="profile-edit"><a href="/profile/edit/" class="btn btn-default">{#profileEdit#}</a></div>
				{/if}
			</div>
		</div>
	</div>

	{block name="content"}
	<div class="col-md-10 no-right-padding">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">{if $profile.rankinfo.rank > 0}#{$profile.rankinfo.rank} - {/if}{$profile.username} {if isset($profile.country_id)} <img src="/media/flags/{$profile.country_id|lower}.png" width="16" height="11" title="{$profile.country_id}"/> {/if}</h2>
			</div>
{include file='profile.basicinfo.tpl' inline}
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
				<tbody>
				</tbody>
			</table>
			<div id="problems-solved-wait"><img src="/media/wait.gif" /></div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">{#profileUnsolvedProblems#} <span class="badge" id="problems-unsolved-total">0</span></h2>
			</div>
			<table class="table table-striped" id="problems-unsolved">
				<tbody>
				</tbody>
			</table>
			<div id="problems-unsolved-wait"><img src="/media/wait.gif" /></div>
		</div>

		<div class="panel panel-default no-bottom-margin">
			<div class="panel-heading">
				<h2 class="panel-title">{#profileStatistics#}</h2>
			</div>
		</div>

	</div>
	{/block}

</div>
<div id="username" style="display:none" data-username="{$profile.username|replace:"\\":""}"></div>

<script src="{version_hash src="/third_party/js/iso-3166-2.js/iso3166.min.js"}"></script>

{/if}

{include file='footer.tpl' inline}
