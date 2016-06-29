{assign var="htmlTitle" value="{#interviewList#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<span id="form-data" data-name="interviews" data-page="new"></span>
<script src="/js/alias.generate.js?ver=827193"></script>

<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title">{#interviewCreateNew#}</h3>
	</div>

	<div class="panel-body">
		<form id="new_interview_form" role="form">
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

				<div class="form-group col-md-6">
					<label for="duration">{#wordsDuration#}</label>
					<select id='duration' name='duration' class="form-control" >
						<option value="60">60 {#wordsMinutes#}</option>
						<option value="120">120 {#wordsMinutes#}</option>
						<option value="300">5 {#wordsHours#}</option>
					</select>
				</div>
			</div>

			<button type='submit' class="btn btn-primary">{#interviewCreateNew#}</button>

		</form>
	</div>
</div>

<div class="wait_for_ajax panel panel-primary" id="contest_list">
	<div class="panel-heading">
		<h3 class="panel-title">{#myInterviews#}</h3>
	</div>
	<table class="table">
		<thead>
			<th></th>
			<th>{#wordsTitle#}</th>
			<th>{#wordsDuration#}</th>
			<th>{#wordsPublished#}</th>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>

<script type="text/javascript" src="/js/interviews.list.js?ver=772023"></script>
{include file='footer.tpl'}

