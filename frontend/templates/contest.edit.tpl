{include file='redirect.tpl'}
{include file='head.tpl' htmlTitle="{#omegaupTitleContestEdit#}"}

<div class="page-header">
	<h1><span>{#frontPageLoading#}</span> <small></small></h1>
</div>

<ul class="nav nav-tabs nav-justified" id="sections">
	<li class="active"><a href="#edit" data-toggle="tab">{#contestEdit#}</a></li>
	<li><a href="#problems" data-toggle="tab">{#wordsAddProblem#}</a></li>
	<li><a href="#publish" data-toggle="tab">{#makePublic#}</a></li>
	<li><a href="#contestants" data-toggle="tab">{#contestAdduserAddContestant#}</a></li>
	<li><a href="#admins" data-toggle="tab">{#omegaupTitleContestAddAdmin#}</a></li>
	<li><a href="#group-admins" data-toggle="tab">{#omegaupTitleContestAddGroupAdmin#}</a></li>
	<li><a href="#links" data-toggle="tab">{#showLinks#}</a></li>
	<li><a href="#clone" data-toggle="tab">{#courseEditClone#}</a></li>
</ul>
<div class="tab-content">
	<div class="tab-pane active" id="edit">
		{include file='contest.new.form.tpl'}
	</div>

	<div class="tab-pane" id="problems">
		<div class="panel panel-primary">
			<div class="panel-body">
				<form class="form" id="add-problem-form">
					<div class="form-group">
						<label for="problems-dropdown">{#wordsProblem#}</label>
						<input class="typeahead form-control" name="problems" id="problems-dropdown"
							autocomplete="off" placeholder="{#enterProblemAlias#}" />
					</div>

					<div class="form-group">
						<label for="points">{#contestAddproblemProblemPoints#}</label>
						<input id='points' name='points' size="3" value="100" class="form-control" />
					</div>

					<div class="form-group">
						<label for="order">{#contestAddproblemContestOrder#}</label>
						<input id='order' name='order' value='1' size="2" class="form-control" />
					</div>

					<div class="form-group">
						<input id='' name='request' value='submit' type='hidden'>
						<button type="submit" class="btn btn-primary">{#wordsAddProblem#}</button>
					</div>
				</form>
			</div>

			<table class="table table-striped">
				<thead>
					<th>{#contestAddproblemContestOrder#}</th>
					<th>{#contestAddproblemProblemName#}</th>
					<th>{#contestAddproblemProblemPoints#}</th>
					<th>{#contestAddproblemProblemRemove#}</th>
				</thead>
				<tbody id="contest-problems"></tbody>
			</table>
		</div>
	</div>

	<div class='tab-pane' id='publish'>
		<div class="panel panel-primary">
			<div class='panel-body'>
				<form class='contest-publish-form'>
					<div class="form-group">
						<label for="public">{#contestNewFormPublic#}</label>
						<select name='public' id='public' class="form-control">
							<option value='0' selected="selected">{#wordsNo#}</option>
							<option value='1'>{#wordsYes#}</option>
						</select>
						<p class="help-block">{#contestNewFormPublicDesc#}</p>
					</div>

					<button class="btn btn-primary" type="submit">{#wordsSaveChanges#}</button>
				</form>
			</div>
		</div>
	</div>

	<div class="tab-pane" id="contestants">
		<div class="panel panel-primary">
			<div class="panel-body">
				<form class="form" id="add-contestant-form">
					<div class="form-group">
						<label for="username-contestant">{#wordsUser#}</label>
						<input id="username-contestant" name="username" value="" type="text" size="20" class="form-control" autocomplete="off" />
					</div>

					<button class="btn btn-primary user-add-single" type="submit">{#contestAdduserAddUser#}</button>

					<hr>

					<div class="form-group">
						<label for="username-contestants">{#wordsMultipleUser#}</label>
						<textarea name="usernames" rows="4" class="form-control"></textarea>
					</div>

					<button class="btn btn-primary user-add-bulk" type="submit">{#contestAdduserAddUsers#}</button>
				</form>
			</div>

			<table class="table table-striped">
				<thead>
					<th>{#wordsUser#}</th>
					<th>{#contestAdduserRegisteredUserTime#}</th>
					<th>{#contestAdduserRegisteredUserDelete#}</th>
				</thead>
				<tbody id="contest-users"></tbody>
			</table>
		</div>

		<script type="text/json" id="payload">{$REQUEST_PAYLOAD|json_encode}</script>
		<div id="contest-requests"></div>
		<script type="text/javascript" src="{version_hash src="/js/dist/contest_requests.js"}"></script>

	</div>

	<div class="tab-pane" id="admins">
		<div class="panel panel-primary">
			<div class="panel-body">
				<form class="form" id="add-admin-form">
					<div class="form-group">
						<label for="username-admin">{#wordsAdmin#}</label>
						<input id="username-admin" name="username" value="" type="text" size="20" class="form-control" autocomplete="off" />
					</div>

					<div class="form-group">
						<div class="col-xs-5 col-sm-3 col-md-3 action-container">
							<button class="btn btn-primary" type="submit">{#wordsAddAdmin#}</button>
						</div>
						<div class="col-xs-7 col-sm-9 col-md-9 toggle-container">
							<input type="checkbox" name="toggle-site-admins" id="toggle-site-admins">
							<label for="toggle-site-admins">{#wordsShowSiteAdmins#}</label>
						</div>
					</div>

				</form>
			</div>

			<table class="table table-striped">
				<thead>
					<th>{#contestEditRegisteredAdminUsername#}</th>
					<th>{#contestEditRegisteredAdminRole#}</th>
					<th>{#contestEditRegisteredAdminDelete#}</th>
				</thead>
				<tbody id="contest-admins"></tbody>
			</table>
		</div>
	</div>

	<div class="tab-pane" id="group-admins">
		<div class="panel panel-primary">
			<div class="panel-body">
				<form class="form" id="add-group-admin-form">
					<div class="form-group">
						<label for="groupalias-admin">{#wordsGroupAdmin#}</label>
						<input id="groupalias-admin" name="alias" value="" type="text" size="20" class="form-control" autocomplete="off" />
					</div>

					<button class="btn btn-primary" type="submit">{#contestAddgroupAddGroup#}</button>
				</form>
			</div>

			<table class="table table-striped">
				<thead>
					<th>{#contestEditRegisteredGroupAdminName#}</th>
					<th>{#contestEditRegisteredAdminRole#}</th>
					<th>{#contestEditRegisteredAdminDelete#}</th>
				</thead>
				<tbody id="contest-group-admins"></tbody>
			</table>
		</div>
	</div>

	<div class="tab-pane" id="links">
		<div class="panel panel-primary">
			<div class="panel-body">
			<table class="table table-striped">
				<thead>
					<h3>{#contestEditAdministrativeLinks#}</h3>
				</thead>
				<tbody class="contest-admin-links">
					<tr>
						<td><a id="submissions">{#wordsSubmissions#}</a></td>
						<td><a id="conteststats">{#profileStatistics#}</a></td>
						<td><a id="activityreport">{#wordsActivityReport#}</a></td>
						<td><a id="printableversion">{#contestPrintableVersion#}</a></td>
						<td><a id="publicscoreboard">{#contestScoreboardLink#}</a></td>
						<td><a id="adminscoreboard">{#contestScoreboardAdminLink#}</a></td>
					</tr>
				</tbody>
			</table>
			</div>
		</div>
	</div>

	<div class="tab-pane" id="clone">
		<div class="panel panel-primary">
			<div class="panel-body">
				<form class="clone_contest_form">
				<div class="row">
					<div class="form-group col-md-6">
						<label for="title">{#wordsTitle#}</label>
						<input id="title" name="title" value="" type="text" size="30" class="form-control">
					</div>

					<div class="form-group col-md-6">
						<label for="alias">{#contestNewFormShortTitle_alias_#}</label>
						<span aria-hidden="true" data-placement="top" data-toggle="tooltip" title="{#contestNewFormShortTitle_alias_Desc#}" class="glyphicon glyphicon-info-sign"></span>
						<input id="alias_clone" name="alias" value="" type="text" class="form-control" >
					</div>

				</div>
				<div class="row">
					<div class="form-group col-md-6">
						<label for="description">{#contestNewFormDescription#}</label>
						<textarea id="description" name="description" cols="30" rows="10" class="form-control"></textarea>
					</div>
					<div class="form-group col-md-3">
						<label for="start-time">{#contestNewFormStartDate#}</label>
						<span aria-hidden="true" data-placement="top" data-toggle="tooltip" title="{#contestNewFormStartDateDesc#}" class="glyphicon glyphicon-info-sign"></span>
						<input id="start-time" name="start_time" value="" class="form-control" type="text" size ="16">
					</div>
				</div>
				<div class="form-group">
					<button type="submit" class="btn btn-primary">{#wordsCloneContest#}
					</button>
				</div>
				</form>
			</div>
		</div>
	</div>

</div>

<script type="text/javascript" src="/third_party/js/bootstrap-multiselect.js"></script>
<script type="text/javascript" src="{version_hash src="/js/contest.edit.js"}"></script>
<link rel="stylesheet" href="/third_party/css/bootstrap-multiselect.css">
{include file='footer.tpl'}
