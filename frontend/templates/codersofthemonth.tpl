{include file='head.tpl' htmlTitle="{#omegaupTitleCodersofthemonth#}"}

<div class="wait_for_ajax panel panel-default" id="coders_list" >
	<div class="panel-heading">
		<h3 class="panel-title">{#codersOfTheMonth#}</h3>
	</div>
	<div class="panel-body">
		<table class="table table-striped table-hover" id="coders-of-the-month-table">
			<thead>
				<tr>
					<th></th>
					<th>{#codersOfTheMonthUser#}</th>
					<th>{#codersOfTheMonthDate#}</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$coders item=data}
					<tr>
						<td><img src="{$data.gravatar_32}"/></td>
						<td><b><a href='/profile/{$data.username|htmlspecialchars}'>{$data.username|htmlspecialchars}</a></b></td>
						<td>{$data.date}</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>

{include file='footer.tpl'}
