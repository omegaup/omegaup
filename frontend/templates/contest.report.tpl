<!doctype html>
<html>
<head>
<title>Reporte {$smarty.get.contest_alias}</title>

<link rel="stylesheet" href="/css/report.css" />
</head>
<body>
{foreach name=outer item=contestantData from=$contestReport}
    <h1>Username: {$contestantData.username}</h1>
    <h1>Total: {if isset($contestantData.total) && isset($contestantData.total.points)}{$contestantData.total.points}{else}0{/if}</h1>
    {foreach key=key item=item from=$contestantData.problems}
    	<h4>Problem: {$key}</h4>
	<h4>Points: {$item.points}</h4>
   	<table>
	<tr>
	<th>Case</th>
	<th>Time (Sec)</th>
	<th>Time-wall (Sec)</th>
	<th>Memory (MiB)</th>
	<th>Status</th>
        <th>Score</th>
	<th>Diff</th>
	</tr>
	{if isset($item.run_details) && isset($item.run_details.groups)}
		{foreach item=group from=$item.run_details.groups}
			{foreach item=case from=$group.cases}
			<tr>
			<td>{$group.group}.{$case.name}</td>
			<td class="numeric">{$case.meta.time|string_format:"%.3f"}</td>
			<td class="numeric">{$case.meta['time-wall']|string_format:"%.3f"}</td>
			<td class="numeric">{$case.meta.mem|string_format:"%.2f"}</td>
			<td>{$case.verdict}</td>
			<td>{$case.score}</td>
			<td>{if isset($case.out_diff)}<pre>{$case.out_diff|escape:'html'}</pre>{/if}</td>
			</tr>
			{/foreach}
		{/foreach}
	{/if}

	</table>
	<table>
		<tr>
			<th>Group</th>
			<th>Score</th>
		</tr>
		{foreach item=group from=$item.run_details.groups}
		<tr>
			<td>{$group.group}</td>
			<td>{$group.score}</td>
		</tr>
		{/foreach}
	</table>
	<br />
  {/foreach}
	<hr/>
	<div class="page-break"></div>
{/foreach}
