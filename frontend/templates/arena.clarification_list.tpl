			<div id="clarifications" class="tab">
				<table class="clarifications">
					<caption>
						{#wordsClarifications#}
						<div class="clarifpager">
							<button class="clarifpagerprev">&lt;</button>
							<button class="clarifpagernext">&gt;</button>
						</div>
					</caption>
					<thead>
						<tr>
{if $contest}
							<th class="problem">{#wordsProblem#}</th>
{else}
							<th class="contest">{#wordsContest#}</th>
{/if}
							<th class="author">{#wordsAuthor#}</th>
							<th class="time">{#wordsTime#}</th>
							<th class="message">{#wordsMessage#}</th>
							<th class="answer">{#wordsResult#}</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="6"><a href="#clarifications/new">{#wordsNewClarification#}</a></td>
						</tr>
					</tfoot>
					<tbody class="clarification-list">
						<tr class="template">
{if $contest}
							<td class="problem"></th>
{else}
							<td class="contest"></td>
{/if}
							<td class="author"></td>
							<td class="time"></td>
							<td><a class="anchor"></a><pre class="message"></pre></td>
							<td class="answer">
								<pre></pre>
								<form id="create-response-form" class="form-inline template">
									<select id="create-response-canned">
										<option value="yes">{#wordsYes#}</option>
										<option value="no">{#wordsNo#}</option>
										<option value="nocomment">{#wordsNoComment#}</option>
										<option value="readAgain">{#wordsReadAgain#}</option>
										<option value="other">{#wordsOther#}</option>
									</select>
									<textarea id="create-response-text" class="form-control" placeholder="{#wordsAnswer#}" style="display:none;" ></textarea>
									<label><input type="checkbox" id="create-response-is-public"/> {#wordsPublic#}</label>
									<input type="submit" />
								</form>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
