<script type="text/javascript" src="{version_hash src="/js/omegaup/arena/contest_list.js"}"></script>
<script type="text/html" id="contest-list">
<div class="panel">
	<h5 data-bind="visible: recommended">{#arenaPageRecommendedContestsText#}</h5>
	<div class="panel-body">
		<table class="contest-list table">
			<thead><tr>
				<th class="col-md-6">{#wordsContest#}</th>
				<th class="time col-md-2" data-bind="visible: showTimes">{#wordsStartTime#}</th>
				<th class="time col-md-2" data-bind="visible: showTimes">{#wordsEndTime#}</th>
				<th class="col-md-2" data-bind="visible: showTimes">{#wordsDuration#}</th>
				<th class="col-md-2" colspan="2" data-bind="visible: showPractice" ></th>
				<th class="col-md-2" data-bind="visible: showVirtual" ></th>
				<th class="col-md-2" data-bind="visible: showPublicUpdated" >{#wordsPublicUpdated#}</th>
			</tr></thead>
			<tbody data-bind="foreach: page" class="contest-list row">
				<tr>
					<td class="col-md-6"><a data-bind="attr: { href: contestLink }">
						<span data-bind="text: titleText"></span>
						<span class="glyphicon glyphicon-ok" aria-hidden="true"
							  data-bind="visible: recommended !== '0'"></span>
					</a></td>
					<td class="no-wrap col-md-2" data-bind="visible: $parent.showTimes">
						<a data-bind="attr: { href: startLink }, text: startText"></a>
					</td>
					<td class="no-wrap col-md-2" data-bind="visible: $parent.showTimes">
						<a data-bind="attr: { href: finishLink }, text: finishText"></a>
					</td>
					<td class="no-wrap col-md-2" data-bind="visible: $parent.showTimes, text: duration"></td>
					<td class="col-md-2" data-bind="visible: $parent.showPractice">
						<a data-bind="attr: { href: '/arena/' + alias + '/practice/' }">
							<span>{#wordsPractice#}</span>
						</a>
					</td>
					<td class="col-md-2" data-bind="visible: $parent.showPractice">
						<a data-bind="attr:  { href: scoreboardLink }">
							<span>{#wordsContestsResults#}</span>
						</a>
					</td>
					<td class="col-md-2" data-bind="visible: (!isVirtual && $parent.showVirtual)">
						<a data-bind="attr: { href: '/arena/' + alias + '/virtual/' }">
							<span>{#virtualContest#}</span>
						</a>
					</td>
					<td class="no-wrap col-md-2" data-bind="visible: $parent.showPublicUpdated, text: publicUpdateText"></td>
				</tr>
				<tr>
					<td colspan="5" class="forcebreaks forcebreaks-arena"
						data-bind="text: description"></td>
				</tr>
			</tbody>
			<tfoot>
				<tr data-bind="visible: hasNext || hasPrevious" align="center">
					<td class="no-wrap" data-bind="attr: { colspan: pagerColumns }">
						<a data-bind="visible: hasPrevious, click: previous" href="#">{#wordsPrevPage#}</a>
						<span class="page-num" data-bind="text: pageNumber"></span>
						<a data-bind="visible: hasNext, click: next" href="#">{#wordsNextPage#}</a>
					</td>
				</tr>
			</tfoot>
		</table>
	</div> <!-- panel-body -->
</div> <!-- panel -->
</script>
