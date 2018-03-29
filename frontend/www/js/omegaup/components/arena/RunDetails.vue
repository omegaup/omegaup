<template>
	<form class="run-details-view">
		<div v-if="data">
			<button class="close">❌</button>
			<div v-if="data.groups" class="cases">
				<h3>{{ T.wordsCases }}</h3>
				<div></div>
				<table>
					<thead>
						<tr>
							<th>{{ T.wordsGroup }}</th>
              <th>{{ T.wordsCase }}</th>
							<th>{{ T.wordsVerdict}}</th>
							<th colspan="3">{{ T.rankScore }}</th>
							<th width="1"></th>
						</tr>
					</thead>
					<group-cases v-for="group in data.groups" :t=T :group-element=group></group-cases>
				</table>
			</div>
			<h3>{{ T.wordsSource }}</h3>
			<pre class="source" v-html="data.source"></pre>
			<div v-if="data.compile_error" class="compile_error">
				<h3>{{ T.wordsCompilerOutput }}</h3>
				<pre class="compile_error" v-html="data.compile_error"></pre>
			</div>
			<div v-if="data.logs" class="logs">
				<h3>{{ T.wordsLogs }}</h3>
				<pre v-html="data.logs"></pre>
			</div>
			<div class="download">
				<h3>{{ T.wordsDownload }}</h3>
				<ul>
					<li><a :href="data.source_url" :download="data.source_name" class="sourcecode">{{ T.wordsDownloadCode}}</a></li>
					<li><a v-if="data.problem_admin" :href="'/api/run/download/run_alias/' + data.guid + '/'" class="output">{{ T.wordsDownloadOutput }}</a></li>
					<li><a v-if="data.problem_admin" :href="'/api/run/download/run_alias/' + data.guid + '/complete/true/'" class="details">{{ T.wordsDownloadDetails }}</a></li>
				</ul>
			</div>
			<div v-if="data.judged_by" class="judged_by">
				<h3>{{ T.wordsJudgedBy }}</h3>
				<pre v-html="data.judged_by"></pre>
			</div>
		</div>
	</form>
</template>

<script>
import {T} from '../../omegaup.js';
import GroupCases from './GroupCases.vue'
export default {
  props: {
    data: Object,
  },
  data: function() {
    return { T: T, }
  },
  components: {
    GroupCases,
  },
}
</script>
