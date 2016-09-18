<script type="text/html" id="contest-list">
<div class="panel-heading">
	<h2 class="panel-title" data-bind="text: header"></h2>
</div>
<table class="contest-list table table-striped table-hover">
    <thead><tr>
        <th>{#wordsContest#}</th>
        <th>{#wordsDescription#}</th>
        <th class="time" data-bind="visible: showTimes">{#wordsStartTime#}</th>
        <th class="time" data-bind="visible: showTimes">{#wordsEndTime#}</th>
        <th data-bind="visible: showTimes"></th>
        <th data-bind="visible: showPractice">{#wordsPractice#}</th>
    </tr></thead>
    <tbody>
        <!-- ko foreach: page -->
	    <tr>
            <td><a data-bind="attr: { href: '/arena/' + alias }">
                <span data-bind="text: title"</span>
                <span class="glyphicon glyphicon-ok" aria-hidden="true"
                      data-bind="visible: recommended !== '0'"></span>
            </a></td>
            <td class="forcebreaks forcebreaks-arena"
                data-bind="text: description"></td>
            <td class="no-wrap" data-bind="visible: $parent.showTimes">
                <a data-bind="attr: { href: 'http://timeanddate.com/worldclock/fixedtime.html?iso=' + start_time.iso() }, text: start_time.long()"></a>
            </td>
            <td class="no-wrap" data-bind="visible: $parent.showTimes">
                <a data-bind="attr: { href: 'http://timeanddate.com/worldclock/fixedtime.html?iso=' + finish_time.iso() }, text: finish_time.long()"></a>
            </td>
            <td class="no-wrap" data-bind="visible: $parent.showTimes, text: duration"></td>
            </td>
            <td data-bind="visible: $parent.showPractice">
                <a data-bind="attr: { href: '/arena/' + alias + '/practice/' }">
                    <span>{#wordsPractice#}</span>
                </a>
            </td>
        </tr>
        <!-- /ko -->
        <tr data-bind="visible: hasNext || hasPrevious" align="center">
            <td data-bind="attr: { colspan: pagerColumns }">
                <a data-bind="visible: hasPrevious, click: previous, text: 'Previous'"></a>
                &nbsp;
                <span data-bind="text: pageNumber"></span>
                &nbsp;
                <a data-bind="visible: hasNext, click: next, text: 'Next'"></a>
            </td>
        </tr>
    </tbody>
</table>
</script>

{include file='arena.head.tpl' jsfile='/ux/arena.js?ver=62c67a'}
			<div class="container" id="main">
				<div class="panel panel-default">
					<div class="panel-body">
						<h1>{#arenaPageTitle#}</h1>
						<p>{#arenaPageIntroduction#}</p>
						<p>{#arenaPageRecommendedContestsText#}</p>

						<p>{#frontPageIntroduction#}</p>
						<div class="text-center">
							<a href="http://blog.omegaup.com/category/omegaup/omegaup-101/" class="btn btn-primary btn-lg">{#frontPageIntroductionButton#}</a>
						</div>
					</div>
				</div>

				<div class="panel panel-primary" id="recommended-current-contests"
				     data-bind="template: 'contest-list'"></div>
				<div class="panel panel-primary" id="current-contests"
				     data-bind="template: 'contest-list'"></div>
				<div class="panel panel-primary" id="recommended-past-contests"
				     data-bind="template: 'contest-list'"></div>
				<div class="panel panel-primary" id="past-contests"
				     data-bind="template: 'contest-list'"></div>
			</div>
		</div>
		{if $OMEGAUP_GA_TRACK eq 1}
		<script type="text/javascript" src="/js/google-analytics.js?ver=bc0b14"></script>
		{/if}
	</body>
</html>
