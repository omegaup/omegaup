{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleGroups#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<span id="form-data" data-name="groups" data-page="list"></span>
<script src="/js/groups.js?ver=5a0ed7"></script>

<div class="panel panel-default">
	<div class="panel-body">
		<div class="bottom-margin">
			<a href="/group/new/" class="btn btn-primary" id="contest-create">{#groupsCreateNew#}</a>
		</div>

		<div id="parent_groups_list">
			<div class="wait_for_ajax panel panel-default no-bottom-margin" id="groups_list">
				<div class="panel-heading">
					<h3 class="panel-title">{#wordsGroups#}</h3>
				</div>
				<table class="table">
					<thead>
						<th>{#wordsTitle#}</th>
						<th></th>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

{include file='footer.tpl'}
