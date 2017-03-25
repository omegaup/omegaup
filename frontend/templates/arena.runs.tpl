				<table class="runs">
					<caption>
						{#wordsSubmissions#}
{if isset($show_pager)}
						<div class="runspager">
							<button class="runspagerprev" data-bind="enable: filter_offset &gt; 0">&lt;</button>
							<button class="runspagernext">&gt;</button>

							<label>{#wordsVerdict#}:
								<select class="runsverdict" name="runsverdict" data-bind="value: filter_verdict">
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
								<select class="runsstatus" name="runsstatus" data-bind="value: filter_status">
									<option value="">{#wordsAll#}</option>
									<option value="new">new</option>
									<option value="waiting">waiting</option>
									<option value="compiling">compiling</option>
									<option value="running">running</option>
									<option value="ready">ready</option>
								</select>
							</label>

							<label>{#wordsLanguage#}:
								<select class="runslang" name="runslang" data-bind="value: filter_language">
									<option value="">{#wordsAll#}</option>
									<option value="cpp11">C++11</option>
									<option value="cpp">C++</option>
									<option value="c">C</option>
									<option value="hs">Haskell</option>
									<option value="java">Java</option>
									<option value="pas">Pascal</option>
									<option value="py">Python</option>
									<option value="rb">Ruby</option>
									<option value="lua">Lua</option>
									<option value="kp">Karel (Pascal)</option>
									<option value="kj">Karel (Java)</option>
									<option value="cat">{#wordsJustOutput#}</option>
								</select>
							</label>

{if isset($show_problem)}
							<label>{#wordsProblem#}:
								<input type="text" class="runsproblem typeahead form-control" autocomplete="off" />
							</label>
							<button type="button" class="close runsproblem-clear" style="float: none;">&times;</button>
{/if}

{if isset($show_user)}
							<label>{#wordsUser#}:
								<input type="text" class="runsusername typeahead form-control" autocomplete="off" />
							</label>
							<button type="button" class="close runsusername-clear" style="float: none;">&times;</button>
{/if}

						</div>
{/if}
					</caption>
					<thead>
						<tr>
							<th>{#wordsTime#}</th>
							<th>GUID</th>
{if isset($show_user)}
							<th>{#wordsUser#}</th>
{/if}
{if isset($show_contest)}
							<th>{#wordsContest#}</th>
{/if}
{if isset($show_problem)}
							<th>{#wordsProblem#}</th>
{/if}
							<th>{#wordsStatus#}</th>
{if isset($show_points)}
							<th class="numeric">{#wordsPoints#}</th>
							<th class="numeric">{#wordsPenalty#}</th>
{else}
							<th class="numeric">{#wordsPercentage#}</th>
{/if}
							<th>{#wordsLanguage#}</th>
							<th class="numeric">{#wordsMemory#}</th>
							<th class="numeric">{#wordsRuntime#}</th>
{if isset($show_rejudge)}
							<th>{#wordsRejudge#}</th>
{/if}
{if isset($show_details)}
							<th>{#wordsDetails#}</th>
{/if}
						</tr>
					</thead>
{if isset($show_submit)}
					<tfoot>
						<tr>
							<td id="new-run" colspan="9"><a href="#problems/new-run">{#wordsNewSubmissions#}</a></td>
							<td id="new-run-practice-msg" colspan="9" style="display:none"><a>{#arenaContestEndedUsePractice#}</a></td>
						</tr>
					</tfoot>
{/if}
					<tbody data-bind="foreach: display_runs">
						<tr>
							<td class="time" data-bind="text: time_text"></td>
							<td class="guid"><acronym data-bind="text: short_guid, attr: { title: guid }"></acronym></td>
{if isset($show_user)}
							<td class="username" data-bind="html:user_html"></td>
{/if}
{if isset($show_contest)}
							<td class="contest"><a data-bind="text: contest_alias, attr: { href: contest_alias_url }"></a></td>
{/if}
{if isset($show_problem)}
							<td class="problem"><a data-bind="text: alias, attr: { href: problem_url }"></a></td>
{/if}
							<td class="status" data-bind="style: { backgroundColor: status_color }">
								<span data-bind="text: status_text"></span>
								<button data-bind="visible: status_help,
								                   click: showVerdictHelp,
								                   attr: { title: status_text,
								                           data-content: status_help }"
								        data-toggle="popover"
								        data-trigger="focus"
								        class="glyphicon glyphicon-question-sign"></button>
							</td>
{if isset($show_points)}
							<td class="points numeric" data-bind="text: points"></td>
							<td class="penalty numeric" data-bind="text: penalty_text"></td>
{else}
							<td class="points numeric" data-bind="text: percentage"></td>
{/if}
							<td class="language" data-bind="text: language"></td>
							<td class="memory numeric" data-bind="text: memory_text"></td>
							<td class="runtime numeric" data-bind="text: runtime_text"></td>
{if isset($show_rejudge)}
							<td class="rejudge"><button class="glyphicon glyphicon-repeat" title="rejudge" data-bind="click: rejudge" /><button class="glyphicon glyphicon-flag" title="debug" data-bind="click: debug_rejudge" /></td>
{/if}
{if isset($show_details)}
							<td><button class="details glyphicon glyphicon-zoom-in" data-bind="click: details"></button></td>
{/if}
						</tr>
					</tbody>
				</table>
