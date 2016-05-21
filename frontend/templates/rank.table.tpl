{if !isset($page)}
	{$page = 1}
{/if}

{if !isset($length)}
	{$length = 100}
{/if}

{if !isset($is_index)}
	{$is_index = false}
{/if}

<div class=" panel panel-default" id="problems_list" >
	<div class="panel-heading">
		<h3 class="panel-title">{#rankHeaderPreCount#} {$length} {#rankHeaderPostCount#}</h3>
		{if !$is_index}
			{if $page > 1}
				<a href="/rank/?page={$page-1}">{#wordsPrevPage#}</a> |
			{/if}
			<a href="/rank/?page={$page+1}">{#wordsNextPage#}</a>
		{/if}
	</div>
	<div class="panel-body no-padding">
		<div class="table-responsive">
			<table class="table table-striped table-hover no-margin" id="rank-by-problems-solved" data-length="{$length}" data-page="{$page}" is-index="{$is_index}">
				<thead>
					<tr>
						<th>#</th>
						<th colspan="2">{#wordsUser#}</th>
						<th class="numericColumn">{#rankScore#}</th>
						{if !$is_index}
						<th class="numericColumn">{#rankSolved#}</th>
						{/if}
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
		<div class="container-fluid">
			<div class="col-xs-12 vertical-padding">
				{if $is_index}
				<a href='/rank/'>{#rankViewFull#}</a>
				{else}
					{if $page > 1}
					<a href="/rank/?page={$page-1}">{#wordsPrevPage#}</a> |
					{/if}
					<a href="/rank/?page={$page+1}">{#wordsNextPage#}</a>
				{/if}
				<br/>
			</div>
		</div>
	</div>
	<script language="javascript" src="/js/rank.table.js?ver=ab98a6"></script>
</div>
