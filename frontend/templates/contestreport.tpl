{literal}
<style>
@media all {
	.page-break	{ display: none; }
}

@media print {
	.page-break	{ display: block; page-break-before: always; }
}
</style>
{/literal}

{foreach name=outer item=contestantData from=$contestReport}
    <h1>Username: {$contestantData.username}</h1>
    <h1>Total: {$contestantData.total.points} </h1>
    {foreach key=key item=item from=$contestantData.problems}
	
    	<h4> Problem: {$key} </h4>
	<h4>Points: {$item.points} </h4>
   	<table>	
	<tr>
	<th>Case</th>
	<th>Time (Sec) </th>
	<th>Time-wall (Sec) </th>
	<th>Memory (KB) </th>
	<th>Syscall count </th>
	<th>Status </th>
	<th>Diff</th>
	</tr>
	{foreach item=case from=$item.run_details.cases} 
		<tr>
		<td>  {$case.name} </td>
		<td> {$case.meta.time} </td>
		<td> {$case.meta.time-wall} </td>
		<td> {$case.meta.mem} </td>
		<td> {$case.meta.syscall-count} </td>
		<td> {$case.meta.status} </td>
			<td> {$case.out_diff}	</td>
		</tr>
	{/foreach} 	
	</table>
	<br />
  {/foreach}
	<hr/>
	<div class="page-break"></div>
{/foreach}
