
{assign var="htmlTitle" value="Entrevistas"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="page-header">
	<h1><span>{#frontPageLoading#}</span> <small></small></h1>
</div>

<ul class="nav nav-tabs nav-justified" id="sections">
	<li class="active"><a href="#interview-details" data-toggle="tab">{#wordsDetails#}</a></li>
	<li><a href="#interview-problems" data-toggle="tab">{#wordsProblems#}</a></li>
	<li><a href="#current-candidates" data-toggle="tab">{#wordsCandidates#}</a></li>
	<li><a href="#admins" data-toggle="tab">{#omegaupTitleContestAddAdmin#}</a></li>
	<li><a href="#group-admins" data-toggle="tab">{#omegaupTitleContestAddGroupAdmin#}</a></li>
</ul>

<div class="tab-content">

	<div class="tab-pane active" id="interview-details">
		<div class="panel panel-primary">
			<div class="panel-body">
			<form class="new_interview_form" role="form">
				<div class="row">
					<div class="form-group col-md-6">
						<label for="title">{#wordsTitle#}</label>
						<input id='title' disabled="true"  name='title' value='' type='text' size='30' class="form-control">
					</div>
					<div class="form-group col-md-6">
						<label for="duration">{#wordsDuration#}</label>
						<select id='window_length' name='window_length' class="form-control" >
							<option value="60">60 {#wordsMinutes#}</option>
							<option value="120">120 {#wordsMinutes#}</option>
							<option value="300">5 {#wordsHours#}</option>
						</select>
					</div>
				</div>
				<button type='submit' class="btn btn-primary">{#wordsEdit#}</button>
			</form>
			</div>
		</div>
	</div>

	<div class="tab-pane" id="interview-problems">
		<div class="panel panel-primary">
			<div class="panel-body">
				<form class="form" id="add-problem-form">
					<div class="form-group">
						<label for="problems-dropdown">{#wordsProblems#}</label>
						<input class="typeahead form-control" name="problems" id="problems-dropdown"
							autocomplete="off" />
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
						<button type='submit' class="btn btn-primary">{#wordsAddProblem#}</button>
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
				<tbody id="contest-problems-table"></tbody>
			</table>
		</div>
	</div>

	<div class="tab-pane " id="current-candidates">
		<div class="panel panel-primary">
			<div id="candidate_list">
				<table class="table">
					<thead>
						<th>{#wordsUser#}</th>
						<th>{#loginEmail#}</th>
						<th>{#rankScore#}</th>
						<th>{#wordsStartTime#}</th>
						<th>{#wordsStartTime#}</th>
						<th>{#wordsResult#}</th>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>

		<div class="panel panel-primary">
			<div class="panel-body">
				<form id="add_user_to_interview" role="form">
					<div class="row">
						<div class="form-group col-md-6">
							<input id='usernameOrEmail' placeholder="{#userEmailEditEmail#}"  name='usernameOrEmail' type='text' size='30' class="form-control">
						</div>
						<div class="form-group col-md-6">
							<button type='submit' class="btn btn-primary">{#inviteToInterview#}</button>
						</div>
					</div>
				</form>
			</div>
			<div id="invitepeople">
				<table class="table">
					<thead>
						<th></th>
						<th>{#wordsE#}</th>
						<th></th>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			<div class="panel-body">
				<form id="send_invites" role="form" style="display:none;">
					<div class="row">
						<div class="form-group col-md-6">
							<textarea id='emailBody' name='emailBody' type='text' rows='10' class="form-control">{#interviewEmailDraft#}</textarea>
						</div>
						<div class="form-group col-md-6">
							<button type='submit' class="btn btn-primary">{#sendInvitesToCandidates#}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div><!--tab-->

	<div class="tab-pane" id="admins">
		<div class="panel panel-primary">
			<div class="panel-body">
				<form class="form" id="add-admin-form">
					<div class="form-group">
						<label for="username-admin">{#wordsAdmin#}</label>
						<input id="username-admin" name="username" value="" type="text" size="20" class="form-control" autocomplete="off" />
					</div>

					<button class="btn btn-primary" type='submit'>{#contestAdduserAddUser#}</button>
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
	</div><!--tab-->

	<div class="tab-pane" id="group-admins">
		<div class="panel panel-primary">
			<div class="panel-body">
				<form class="form" id="add-group-admin-form">
					<div class="form-group">
						<label for="groupalias-admin">{#wordsGroupAdmin#}</label>
						<input id="groupalias-admin" name="alias" value="" type="text" size="20" class="form-control" autocomplete="off" />
					</div>

					<button class="btn btn-primary" type='submit'>{#contestAddgroupAddGroup#}</button>
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
	</div><!--tab-->

</div>

<script type="text/javascript" src="/js/interviews.edit.js?ver=2e15b4"></script>
{include file='footer.tpl'}

