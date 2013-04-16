<!doctype html>
<html>
<head>
<title>Reporte {$smarty.get.contest_alias}</title>
<style>
{literal}
@media all {
	.page-break	   { display: none; }
	body               { font-family: sans-serif; }
	tr:nth-child(even) { background-color: #eee; }
	td.numeric         { text-align: right; }
}

@media print {
	.page-break	   { display: block; page-break-before: always; }
}
{/literal}
</style>
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
	<th>Memory (MB)</th>
	<th>Syscall count</th>
	<th>Status</th>
	<th>Diff</th>
	</tr>
	{if isset($item.run_details) && isset($item.run_details.cases)}
	{foreach item=case from=$item.run_details.cases} 
		<tr>
		<td>{$case.name}</td>
		<td class="numeric">{$case.meta.time|string_format:"%.3f"}</td>
		<td class="numeric">{$case.meta['time-wall']|string_format:"%.3f"}</td>
		<td class="numeric">{$case.meta.mem|string_format:"%.2f"}</td>
		<td class="numeric">{if isset($case.meta['syscall-count'])}{$case.meta['syscall-count']}{/if}</td>
		<td>{$case.meta.status}</td>
		<td>{if isset($case.out_diff)}<pre>{$case.out_diff|escape:'html'}</pre>{/if}</td>
		</tr>
	{/foreach}
	{/if}
	</table>
	<br />
  {/foreach}
	<hr/>
	<div class="page-break"></div>
{/foreach}
