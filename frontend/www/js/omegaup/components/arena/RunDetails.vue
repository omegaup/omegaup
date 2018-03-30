<template>
  <form class="run-details-view">
    <div v-if="data">
      <button class="close">❌</button>
      <div class="cases"
           v-if="data.groups">
        <h3>{{ T.wordsCases }}</h3>
        <div></div><omegaup-arena-groupcases v-bind:group-element="group"
             v-for="group in data.groups"></omegaup-arena-groupcases>
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
        </table>
      </div>
      <h3>{{ T.wordsSource }}</h3>
      <pre class="source"
           v-if="data.source_link">
                                <a download="data.zip"
     v-bind:href="data.source">{{ T.wordsDownload }}</a>
                        </pre>
      <pre class="source"
           v-else=""
           v-html="data.source"></pre>
      <div class="compile_error"
           v-if="data.compile_error">
        <h3>{{ T.wordsCompilerOutput }}</h3>
        <pre class="compile_error"
             v-text="data.compile_error"></pre>
      </div>
      <div class="logs"
           v-if="data.logs">
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
import arena_GroupCases from './GroupCases.vue';
export default {
  props: {
    data: Object,
  },
  data: function() {
    return { T: T, }
  },
  computed: {
    sourceHTML: function() {
      let sourceHTML;
      if (data.source.indexOf('data:') === 0) {
        sourceHTML = '<a href="' + data.source + '" download="data.zip">' +
                     T.wordsDownload + '</a>';
      } else if (data.source == 'lockdownDetailsDisabled') {
        sourceHTML = (typeof(sessionStorage) !== 'undefined' &&
                      sessionStorage.getItem('run:' + guid)) ||
                     T.lockdownDetailsDisabled;
      } else {
        sourceHTML = data.source;
      }
    }
  },
  components: {
    'omegaup-arena-groupcases': arena_GroupCases,
  },
}

</script>
