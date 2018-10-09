<template>
  <table class="runs">
    <caption>
      {{T.wordsSubmissions}}
      <div class="runspager"
           v-if="showPager">
        <button class="runspagerprev">&lt;</button> <button class="runspagernext">&gt;</button>
        <label>{{T.wordsVerdict + ':'}} <select v-model="filterVerdict">
          <option value="">
            {{T.wordsAll}}
          </option>
          <option value="AC">
            AC
          </option>
          <option value="PA">
            PA
          </option>
          <option value="WA">
            WA
          </option>
          <option value="TLE">
            TLE
          </option>
          <option value="MLE">
            MLE
          </option>
          <option value="OLE">
            OLE
          </option>
          <option value="RTE">
            RTE
          </option>
          <option value="RFE">
            RFE
          </option>
          <option value="CE">
            CE
          </option>
          <option value="JE">
            JE
          </option>
          <option value="NO-AC">
            No AC
          </option>
        </select></label> <label>{{T.wordsStatus + ':'}} <select v-model="filterStatus">
          <option value="">
            {{T.wordsAll}}
          </option>
          <option value="new">
            new
          </option>
          <option value="waiting">
            waiting
          </option>
          <option value="compiling">
            compiling
          </option>
          <option value="running">
            running
          </option>
          <option value="ready">
            ready
          </option>
        </select></label> <label>{{T.wordsLanguage + ':'}} <select v-model="filterLanguage">
          <option v-for="language in languages"
                  value="language">
            {{language}}
          </option>
        </select></label> <label v-if="showProblem">{{T.wordsProblem + ':'}}
        <omegaup-autocomplete v-bind:init=
        "el =&gt; isContest ? UI.problemContestTypeahead(el) : UI.problemTypeahead(el)"
                              v-model="filterProblem"></omegaup-autocomplete></label> <button class=
                              "close runsproblem-clear"
             style="float: none;"
             type="button"
             v-if="showProblem"
             v-on:click="filterProblem = ''">×</button> <label v-if="showUser">{{T.wordsUser +
             ':'}} <omegaup-autocomplete v-bind:init="el =&gt; UI.userTypeahead(el)"
                              v-model="filterUsername"></omegaup-autocomplete></label>
                              <button class="close runsusername-clear"
             style="float: none;"
             type="button"
             v-if="showUser">×</button>
      </div>
    </caption>
    <thead>
      <tr>
        <th>{{T.wordsTime}}</th>
        <th>GUID</th>
        <th v-if="showUser">{{T.wordsUser}}</th>
        <th v-if="showContest">{{T.wordsContest}}</th>
        <th v-if="showProblem">{{T.wordsProblem}}</th>
        <th>{{T.wordsStatus}}</th>
        <th class="numeric"
            v-if="showPoints">{{T.wordsPoints}}</th>
        <th class="numeric"
            v-if="showPoints">{{T.wordsPenalty}}</th>
        <th class="numeric"
            v-else="">{{T.wordsPercentage}}</th>
        <th>{{T.wordsLanguage}}</th>
        <th>{{T.wordsMemory}}</th>
        <th>{{T.wordsRuntime}}</th>
        <th v-if="showRejudge">{{T.wordsRejudge}}</th>
        <th v-if="showDisqualify">{{T.wordsDisqualify}}</th>
        <th v-if="showDetails">{{T.wordsDetails}}</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="run in runs">
        <td>{{timeStatus(run.time)}}</td>
        <td><acronym>{{shortGUID(run.guid)}}</acronym></td>
        <td v-if="showUser">{{run.username}}</td>
        <td v-if="showContest">
          <a>{{run.contest_alias}}</a>
        </td>
        <td v-if="showProblem">
          <a>{{run.alias}}</a>
        </td>
        <td><span>{{statusText(run)}}</span> <button class="glyphicon glyphicon-question-sign"
                data-toggle="popover"
                type="button"
                v-if="statusHelp(run)"></button></td>
        <td v-if="showPoints">{{run.points}}</td>
        <td v-if="showPoints">{{run.penalty}}</td>
        <td v-else="">{{percentage(run)}}</td>
        <td>{{run.language}}</td>
        <td>{{memory(run)}}</td>
        <td>{{runtime(run)}}</td>
        <td v-if="showRejudge"><button class="glyphicon glyphicon-repeat"
                title="rejudge"
                type="button"
                v-on:click="rejudge"></button> <button class="glyphicon glyphicon-flag"
                title="debug"
                type="button"
                v-on:click="debug_rejudge"></button></td>
        <td v-if="showDisqualify"><button class="glyphicon glyphicon-ban-circle"
                title="disqualify"
                type="button"
                v-on:click="disqualify"></button></td>
        <td v-if="showDetails"><button class="details glyphicon glyphicon-zoom-in"
                type="button"></button></td>
      </tr>
    </tbody>
    <tfoot v-if="showSubmit">
      <tr>
        <td colspan="9">
          <a href="#problems/new-run">{{T.wordsNewSubmissions}}</a>
        </td>
        <td colspan="9"
            style="display:none">
          <a>{{T.arenaContestEndedUsePractice}}</a>
        </td>
      </tr>
    </tfoot>
  </table>
</template>

<script>
import {T, UI} from '../../omegaup.js';
import Autocomplete from '../Autocomplete.vue';

export default {
  props: {
    isContest: Boolean,
    data: Array,
    showPager: Boolean,
    showProblem: Boolean,
    showPoints: Boolean,
    showContest: Boolean,
    showRejudge: Boolean,
    showDisqualify: Boolean,
    showDetails: Boolean,
    showSubmit: Boolean,
    showUser: Boolean,
    languages: Array
  },
  data: function() {
    return {
      T: T, UI: UI, filterUsername: "", filterVerdict: "", filterLanguage: "",
          filterProblem: "", filterOffset: 0, filterStatus: "",
          showVerdict: false, runs: this.data
    }
  },
  methods: {
    shortGUID: function(guid) { return guid.substring(0, 8);},
    statusColor: function(run) {
      if (run.status != 'ready') return '';
      if (run.type == 'disqualified') return '#F00';
      if (run.verdict == 'AC') return '#CF6';
      if (run.verdict == 'CE') return '#F90';
      if (run.verdict == 'JE') return '#F00';
      return '';
    },
    timeStatus: function(time) {
      return Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', time.getTime());
    },
    statusText: function(run) {
      if (run.type == 'disqualified') return T.wordsDisqualify;
      return run.status == 'ready' ? T['verdict' + run.verdict] : run.status;
    },
    statusHelp: function(run) {
      if (run.status != 'ready' || run.verdict == 'AC') return null;
      if (run.language == 'kj' || run.language == 'kp') {
        if (run.verdict == 'RTE' || run.verdict == 'RE') {
          return T.verdictHelpKarelRTE;
        } else if (run.verdict == 'TLE' || run.verdict == 'TO') {
          return T.verdictHelpKarelTLE;
        }
      }
      return T['verdictHelp' + run.verdict];
    },
    percentage: function(run) {
      if (run.status == 'ready' && run.verdict != 'JE' && run.verdict != 'CE') {
        return (parseFloat(run.score || '0') * 100).toFixed(2) + '%';
      } else {
        return '—';
      }
    },
    memory: function(run) {
      if (run.status == 'ready' && run.verdict != 'JE' && run.verdict != 'CE') {
        let prefix = '';
        if (run.verdict == 'MLE') prefix = '>';
        return prefix + (parseFloat(run.memory) / (1024 * 1024)).toFixed(2) +
               'Mb';
      } else {
        return '—';
      }
    },
    runtime: function(run) {
      if (run.status == 'ready' && run.verdict != 'JE' && run.verdict != 'CE') {
        let prefix = '';
        if (run.verdict == 'TLE') prefix = '>';
        return prefix + (parseFloat(run.runtime || '0') / 1000).toFixed(2) +
               's';
      } else {
        return '—';
      }
    }
  },
  components: {'omegaup-autocomplete': Autocomplete}
}
</script>

<style>
.typeahead {
    padding: 8px 11px;
    border: 2px solid #ccc;
    border-radius: 8px;
    outline: none;
}

.runs td, .runs th {
    border: 1px solid #ccc;
    border-width: 1px 0;
    text-align: center;
}

.runs {
    width: 100%;
    border: 1px solid #ccc;
    margin-top: 2em;
    font-family: sans-serif;
}

.runs caption {
    font-weight: bold;
    margin-bottom: 1em;
}
</style>
