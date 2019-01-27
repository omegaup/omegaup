{if !isset($page)}
	{$page = 1}
{/if}

{if !isset($length)}
	{$length = 100}
{/if}

{if !isset($is_index)}
	{$is_index = false}
{/if}

{if !isset($availableFilters)}
	{$availableFilters = array()}
{/if}

{if !isset($filter)}
	{$filter = ''}
{/if}

<script type="text/json" id="payload">{['page' => $page, 'length' => $length, 'is_index' => $is_index, 'availableFilters' => $availableFilters, 'filter' => $filter]|json_encode}</script>
<div id="rank-table"></div>
<script type="text/javascript" src="{version_hash src="/js/dist/rank_table.js"}" async></script>
