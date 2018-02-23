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
		{if !$is_index}
			<h3 class="panel-title">{#rankRangeHeader#|omegaup_format:[lowCount=>($page-1)*$length+1, highCount=>$page*$length]}</h3>
			{if $page > 1}
				<a href="/rank/?page={$page-1}">{#wordsPrevPage#}</a> |
			{/if}
			<a href="/rank/?page={$page+1}">{#wordsNextPage#}</a>
		    {if count($availableFilters) > 0}
		        <select class="filter">
		        	<option value="">{#wordsSelectFilter#}</option>
		        	{foreach key=key item=item from=$availableFilters}
		        	<option value="{$key}" {if isset($filter) && $filter == $key}selected="selected"{/if}>
		        		{$item}
		        	</option>
		        	{/foreach}
		        </select>
		    {/if}
		{else}
		    <h3 class="panel-title">{#rankHeader#|omegaup_format:[count=>$length]}</h3>
		{/if}
	</div>
	<div class="panel-body no-padding">
		<div class="table-responsive">
			<table class="table table-striped table-hover no-margin" id="rank-by-problems-solved" data-length="{$length}" data-page="{$page}" {if isset($filter)}data-filter="{$filter}" {/if}is-index="{$is_index}">
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
					<a href="/rank/?page={$page-1}{if isset($filter)}&filter={$filter}{/if}">{#wordsPrevPage#}</a> |
					{/if}
					<a href="/rank/?page={$page+1}{if isset($filter)}&filter={$filter}{/if}">{#wordsNextPage#}</a>
				{/if}
				<br/>
			</div>
		</div>
	</div>
	<script language="javascript" src="{version_hash src="/js/rank.table.js"}"></script>
</div>
