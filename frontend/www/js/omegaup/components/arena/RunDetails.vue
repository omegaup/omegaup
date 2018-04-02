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
          <template v-for="element in data.groups">
            <tbody>
              <tr class="group">
                <th class="center">{{ element.group }}</th>
                <th colspan="2">
                  <div class="dropdown-cases"
                      v-on:click="toggle(element.group)">
                    <span v-bind:class="{'glyphicon glyphicon-collapse-up': groupVisible[element.group], 'glyphicon glyphicon-collapse-down': !groupVisible[element.group]}"></span>
                  </div>
                </th>
                <th class="score">{{ element.contest_score ? element.contest_score :
                element.score}}</th>
                <th class="center"
                    width="10">{{ element.max_score ? '/':''}}</th>
                <th>{{ element.max_score ? element.max_score : '' }}</th>
              </tr>
              <template v-for="problem in element.cases">
                <tr v-if="groupVisible[element.group]">
                  <td></td>
                  <td class="text-center">{{ problem.name }}</td>
                  <td class="text-center">{{ problem.verdict }}</td>
                  <td class="score">{{ problem.contest_score ? problem.contest_score : problem.score }}</td>
                  <td class="center"
                      width="10">{{ problem.max_score ? '/':'' }}</td>
                  <td>{{ problem.max_score ? problem.max_score:'' }}</td>
                </tr>
              </template>
            </tbody>
          </template>
        </table>
      </div>
      <h3>{{ T.wordsSource }}</h3>
      <pre v-if="data.source_link" class="source">
        <a download="data.zip" v-bind:href="data.source">{{ T.wordsDownload }}</a>
      </pre>
      <pre v-else class="source" v-html="data.source"></pre>
      <div v-if="data.compile_error" class="compile_error">
        <h3>{{ T.wordsCompilerOutput }}</h3>
        <pre class="compile_error" v-text="data.compile_error"></pre>
      </div>
      <div v-if="data.logs" class="logs">
        <h3>{{ T.wordsLogs }}</h3>
        <pre v-text="data.logs"></pre>
      </div>
      <div class="download">
        <h3>{{ T.wordsDownload }}</h3>
        <ul>
          <li>
            <a class="sourcecode"
                v-bind:download="data.source_name"
                v-bind:href="data.source_url">{{ T.wordsDownloadCode}}</a>
          </li>
          <li>
            <a class="output"
                v-bind:href="'/api/run/download/run_alias/' + data.guid + '/'"
                v-if="data.problem_admin">{{ T.wordsDownloadOutput }}</a>
          </li>
          <li>
            <a class="details"
                v-bind:href="'/api/run/download/run_alias/' + data.guid + '/complete/true/'"
                v-if="data.problem_admin">{{ T.wordsDownloadDetails }}</a>
          </li>
        </ul>
      </div>
      <div class="judged_by"
           v-if="data.judged_by">
        <h3>{{ T.wordsJudgedBy }}</h3>
        <pre v-text="data.judged_by"></pre>
      </div>
    </div>
  </form>
</template>

<script>
import {T} from '../../omegaup.js';
export default {
  props: {
    data: Object,
  },
  data: function() {
    return { T: T, groupVisible: {}}
  },
  methods: {
    toggle(group) {
      const visible = this.groupVisible[group];
      this.$set(this.groupVisible, group, !visible);
    },
  }
}
</script>

<style>
  .dropdown-cases {
    height: 100%;
    width: 100%;
    margin: 0 auto;
    text-align: center;
    background: rgb(245, 245, 245);
    border-radius: 5px;
  }
</style>

