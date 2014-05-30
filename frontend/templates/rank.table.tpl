{if !isset($page)}
	{$page = 0}
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
			{if $page > 0}
				<a href="{$smarty.server.PHP_SELF}?p={$page-1}">{#wordsPrevPage#}</a> |
			{/if}
			<a href="{$smarty.server.PHP_SELF}?p={$page+1}">{#wordsNextPage#}</a>
		{/if}
	</div>
	<table class="table table-striped table-hover" id="rank-by-problems-solved" data-length="{$length}" data-page="{$page}">
		<thead>
			<tr>
				<th>#</th>
				<th>{#wordsUser#}</th>
				<th>{#rankSolved#}</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>	
	<div class="panel-body">
		{if $is_index}
		<a href='rank.php'>{#rankViewFull#}</a>
		{else}		
			{if $page > 0}
				<a href="{$smarty.server.PHP_SELF}?p={$page-1}">{#wordsPrevPage#}</a> | 
			{/if}
			<a href="{$smarty.server.PHP_SELF}?p={$page+1}">{#wordsNextPage#}</a>
		{/if}
	</div>	

	<script language="javascript" src="/js/rank.table.js"></script>
</div>
