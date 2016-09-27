<script type="text/javascript" src="/js/omegaup/arena/contest_list.js?ver=950c55"></script>
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
    <tbody data-bind="foreach: page">
        <tr>
            <td><a data-bind="attr: { href: contestLink }">
                <span data-bind="text: title"</span>
                <span class="glyphicon glyphicon-ok" aria-hidden="true"
                      data-bind="visible: recommended !== '0'"></span>
            </a></td>
            <td class="forcebreaks forcebreaks-arena"
                data-bind="text: description"></td>
            <td class="no-wrap" data-bind="visible: $parent.showTimes">
                <a data-bind="attr: { href: startLink }, text: startText"></a>
            </td>
            <td class="no-wrap" data-bind="visible: $parent.showTimes">
                <a data-bind="attr: { href: finishLink }, text: finishText"></a>
            </td>
            <td class="no-wrap" data-bind="visible: $parent.showTimes, text: duration"></td>
            </td>
            <td data-bind="visible: $parent.showPractice">
                <a data-bind="attr: { href: '/arena/' + alias + '/practice/' }">
                    <span>{#wordsPractice#}</span>
                </a>
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr data-bind="visible: hasNext || hasPrevious" align="center">
            <td class="no-wrap" data-bind="attr: { colspan: pagerColumns }">
                <a data-bind="visible: hasPrevious, click: previous">{#wordsPrevPage#}</a>
                <span class="page-num" data-bind="text: pageNumber"></span>
                <a data-bind="visible: hasNext, click: next">{#wordsNextPage#}</a>
            </td>
        </tr>
    </tfoot>
</table>
</script>
