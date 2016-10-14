<template id='runs-table'>
    <table class="runs">
        <caption>{#wordsSubmissions#}
            <div class="runspager" data-bind="visible: showPager">
                <button class="runspagerprev"
                        data-bind="enable: filter_offset &gt; 0">&lt;</button>
                <button class="runspagernext">&gt;</button>
                <label>{#wordsVerdict#}:
                    <select class="runsverdict" name="runsverdict"
                            data-bind="value: filter_verdict">
                        <option value="">{#wordsAll#}</option>
                        <option value="AC">AC</option>
                        <option value="PA">PA</option>
                        <option value="WA">WA</option>
                        <option value="TLE">TLE</option>
                        <option value="MLE">MLE</option>
                        <option value="OLE">OLE</option>
                        <option value="RTE">RTE</option>
                        <option value="RFE">RFE</option>
                        <option value="CE">CE</option>
                        <option value="JE">JE</option>
                        <option value="NO-AC">No AC</option>
                    </select>
                </label>
                <label>{#wordsStatus#}:
                    <select class="runsstatus" name="runsstatus"
                            data-bind="value: filter_status">
                        <option value="">{#wordsAll#}</option>
                        <option value="new">new</option>
                        <option value="waiting">waiting</option>
                        <option value="compiling">compiling</option>
                        <option value="running">running</option>
                        <option value="ready">ready</option>
                    </select>
                </label>
                <label>{#wordsLanguage#}:
                    <select class="runslang" name="runslang"
                            data-bind="value: filter_language">
                        <option value="">{#wordsAll#}</option>
                        <option value="cpp11">C++11</option>
                        <option value="cpp">C++</option>
                        <option value="c">C</option>
                        <option value="hs">Haskell</option>
                        <option value="java">Java</option>
                        <option value="pas">Pascal</option>
                        <option value="py">Python</option>
                        <option value="rb">Ruby</option>
                        <option value="kp">Karel (Pascal)</option>
                        <option value="kj">Karel (Java)</option>
                        <option value="cat">{#wordsJustOutput#}</option>
                    </select>
                </label>
                <span data-bind="visible: showProblem">
                    <label>{#wordsProblem#}:
                        <input type="text" class="runsproblem typeahead form-control"
                               autocomplete="off" />
                    </label>
                    <button type="button" class="close runsproblem-clear"
                            style="float: none;">&times;</button>
                </span>
                <span data-bind="visible: showUser">
                    <label>{#wordsUser#}:
                        <input type="text" class="runsusername typeahead form-control"
                               autocomplete="off" />
                    </label>
                    <button type="button" class="close runsusername-clear"
                            style="float: none;">&times;</button>
                </span>
            </div>
        </caption>
        <thead>
            <tr>
                <th>{#wordsTime#}</th>
                <th>GUID</th>
                <th data-bind="visible: showUser">{#wordsUser#}</th>
                <th data-bind="visible: showContest">{#wordsContest#}</th>
                <th data-bind="visible: showProblem">{#wordsProblem#}</th>
                <th>{#wordsStatus#}</th>
                <th data-bind="visible: showPoints" class="numeric">{#wordsPoints#}</th>
                <th data-bind="visible: showPoints" class="numeric">{#wordsPenalty#}</th>
                <th data-bind="visible: !showPoints"
                    class="numeric">{#wordsPercentage#}</th>
                <th>{#wordsLanguage#}</th>
                <th class="numeric">{#wordsMemory#}</th>
                <th class="numeric">{#wordsRuntime#}</th>
                <th data-bind="visible: showRejudge">{#wordsRejudge#}</th>
                <th data-bind="visible: showDetails">{#wordsDetails#}</th>
            </tr>
        </thead>
        <tfoot data-bind="visible: showSubmit">
            <tr>
                <td id="new-run" data-bind="attr: { colspan: numColumns }" >
                    <a href="#problems/new-run">{#wordsNewSubmissions#}</a>
                </td>
                <td id="new-run-practice-msg" style="display: none"
                    data-bind="attr: { colspan: numColumns }">
                    <a>{#arenaContestEndedUsePractice#}</a>
                </td>
            </tr>
        </tfoot>
        <tbody data-bind="foreach: display_runs">
            <tr>
                <td class="time" data-bind="text: time_text"></td>
                <td class="guid">
                    <acronym data-bind="text: short_guid, attr: { title: guid }"></acronym>
                </td>
                <td class="username" data-bind="html: user_html, visible: $parent.showUser">
                </td>
                <td class="contest" data-bind="visible: $parent.showContest">
                    <a data-bind="text: contest_alias, attr: { href: contest_alias_url }" />
                </td>
                <td class="problem" data-bind="visible: $parent.showProblem">
                    <a data-bind="text: alias, attr: { href: problem_url }" />
                </td>
                <td class="status"
                    data-bind="text: status_text, style: { backgroundColor: status_color }">
                </td>
                <td class="points numeric"
                    data-bind="text: points, visible: $parent.showPoints">
                </td>
                <td class="penalty numeric"
                    data-bind="text: penalty_text, visible: $parent.showPoints">
                </td>
                <td class="points numeric"
                    data-bind="text: percentage, visible: !$parent.showPoints">
                </td>
                <td class="language" data-bind="text: language"></td>
                <td class="memory numeric" data-bind="text: memory_text"></td>
                <td class="runtime numeric" data-bind="text: runtime_text"></td>
                <td class="rejudge" data-bind="visible: $parent.showRejudge">
                    <button class="glyphicon glyphicon-repeat" title="rejudge"
                            data-bind="click: rejudge" />
                    <button class="glyphicon glyphicon-flag" title="debug"
                            data-bind="click: debug_rejudge" />
                </td>
                <td data-bind="visible: $parent.showDetails">
                    <button class="details glyphicon glyphicon-zoom-in"
                            data-bind="click: details"></button>
                </td>
            </tr>
        </tbody>
    </table>
</template>
