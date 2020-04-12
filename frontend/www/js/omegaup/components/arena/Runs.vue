<template>
  <table class="runs">
    <caption>
      {{
        T.wordsSubmissions
      }}
      <div v-if="showPager">
        <button v-bind:disabled="filterOffset <= 0" v-on:click="filterOffset--">
          &lt;
        </button>
        {{ filterOffset + 1 }}
        <button
          v-bind:disabled="runs.length < rowCount"
          v-on:click="filterOffset++"
        >
          &gt;
        </button>

        <label
          >{{ T.wordsVerdict }}:
          <select v-model="filterVerdict">
            <option value="">{{ T.wordsAll }}</option>
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
            <option value="VE">VE</option>
            <option value="NO-AC">No AC</option>
          </select>
        </label>

        <label
          >{{ T.wordsStatus }}:
          <select v-model="filterStatus">
            <option value="">{{ T.wordsAll }}</option>
            <option value="new">new</option>
            <option value="waiting">waiting</option>
            <option value="compiling">compiling</option>
            <option value="running">running</option>
            <option value="ready">ready</option>
          </select>
        </label>

        <label
          >{{ T.wordsLanguage }}:
          <select v-model="filterLanguage">
            <option value="">{{ T.wordsAll }}</option>
            <option value="cpp17-gcc">C++17 (g++ 7.4)</option>
            <option value="cpp17-clang">C++17 (clang++ 6.0)</option>
            <option value="cpp11-gcc">C++11 (g++ 7.4)</option>
            <option value="cpp11-clang">C++11 (clang++ 6.0)</option>
            <option value="c11-gcc">C (gcc 7.4)</option>
            <option value="c11-clang">C (clang 6.0)</option>
            <option value="cs">C# (dotnet 2.2)</option>
            <option value="hs">Haskell (ghc 8.0)</option>
            <option value="java">Java (openjdk 11.0)</option>
            <option value="pas">Pascal (fpc 3.0)</option>
            <option value="py3">Python 3.6</option>
            <option value="py2">Python 2.7</option>
            <option value="rb">Ruby (2.5)</option>
            <option value="lua">Lua (5.2)</option>
            <option value="kp">Karel (Pascal)</option>
            <option value="kj">Karel (Java)</option>
            <option value="cat">{{ T.wordsJustOutput }}</option>
          </select>
        </label>

        <template v-if="showProblem">
          <label
            >{{ T.wordsProblem }}:
            <omegaup-autocomplete
              v-bind:init="initProblemAutocomplete"
              v-model="filterProblem"
            ></omegaup-autocomplete>
          </label>
          <button
            type="button"
            class="close"
            style="float: none;"
            v-on:click="filterProblem = ''"
          >
            &times;
          </button>
        </template>

        <template v-if="showUser">
          <label
            >{{ T.wordsUser }}:
            <omegaup-autocomplete
              v-bind:init="el => typeahead.userTypeahead(el)"
              v-model="filterUsername"
            ></omegaup-autocomplete>
          </label>
          <button
            type="button"
            class="close"
            style="float: none;"
            v-on:click="filterUsername = ''"
          >
            &times;
          </button>
        </template>
      </div>
    </caption>
    <thead>
      <tr>
        <th>{{ T.wordsTime }}</th>
        <th>GUID</th>
        <th v-if="showUser">{{ T.wordsUser }}</th>
        <th v-if="showContest">{{ T.wordsContest }}</th>
        <th v-if="showProblem">{{ T.wordsProblem }}</th>
        <th>{{ T.wordsStatus }}</th>
        <th v-if="showPoints" class="numeric">{{ T.wordsPoints }}</th>
        <th v-if="showPoints" class="numeric">{{ T.wordsPenalty }}</th>
        <th v-if="!showPoints" class="numeric">{{ T.wordsPercentage }}</th>
        <th>{{ T.wordsLanguage }}</th>
        <th class="numeric">{{ T.wordsMemory }}</th>
        <th class="numeric">{{ T.wordsRuntime }}</th>
        <th v-if="showRejudge">{{ T.wordsRejudge }}</th>
        <th v-if="showDisqualify">{{ T.wordsDisqualify }}</th>
        <th v-if="showDetails">{{ T.wordsDetails }}</th>
      </tr>
    </thead>
    <tfoot v-if="showSubmit">
      <tr>
        <td colspan="9">
          <a href="#problems/new-run">{{ T.wordsNewSubmissions }}</a>
        </td>
        <td colspan="9">
          <a>{{ T.arenaContestEndedUsePractice }}</a>
        </td>
      </tr>
    </tfoot>
    <tbody v-for="run in filteredRuns">
      <tr>
        <td>{{ time.formatTimestamp(run.time) }}</td>
        <td>
          <acronym v-bind:title="run.guid">{{
            run.guid.substring(0, 8)
          }}</acronym>
        </td>
        <td v-if="showUser">{{ run.username }}</td>
        <td v-if="showContest">
          <a
            v-bind:href="
              run.contest_alias ? `/arena/${run.contest_alias}/` : ''
            "
            >{{ run.contest_alias }}</a
          >
        </td>
        <td v-if="showProblem">
          <a v-bind:href="`/arena/problem/${run.alias}/`">{{ run.alias }}</a>
        </td>
        <td v-bind:style="{ backgroundColor: statusColor(run) }">
          <span>{{ status(run) }}</span>
          <button
            type="button"
            v-show="!!statusHelp(run)"
            v-bind:data-content="statusHelp(run)"
            v-on:click="showVerdictHelp"
            data-toggle="popover"
            data-trigger="focus"
            class="glyphicon glyphicon-question-sign"
          ></button>
        </td>
        <td v-if="showPoints" class="numeric">{{ points(run) }}</td>
        <td v-if="showPoints" class="numeric">{{ penalty(run) }}</td>
        <td v-if="!showPoints" class="numeric">{{ percentage(run) }}</td>
        <td>{{ run.language }}</td>
        <td class="numeric">{{ memory(run) }}</td>
        <td class="numeric">{{ runtime(run) }}</td>
        <td v-if="showRejudge">
          <button
            type="button"
            class="glyphicon glyphicon-repeat"
            title="rejudge"
            v-on:click="$emit('rejudge', run)"
          ></button>
        </td>
        <td v-if="showDisqualify">
          <button
            type="button"
            class="glyphicon glyphicon-ban-circle"
            title="disqualify"
            v-on:click="$emit('disqualify', run)"
          ></button>
        </td>
        <td v-if="showDetails">
          <button
            type="button"
            class="glyphicon glyphicon-zoom-in"
            v-on:click="$emit('details', run)"
          ></button>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import * as time from '../../time';
import * as typeahead from '../../typeahead';

import Autocomplete from '../Autocomplete.vue';

declare global {
  interface JQuery {
    popover(action: string): JQuery;
  }
}

@Component({
  components: {
    'omegaup-autocomplete': Autocomplete,
  },
})
export default class Runs extends Vue {
  @Prop({ default: false }) showContest!: boolean;
  @Prop({ default: false }) showDetails!: boolean;
  @Prop({ default: false }) showDisqualify!: boolean;
  @Prop({ default: false }) showPager!: boolean;
  @Prop({ default: false }) showPoints!: boolean;
  @Prop({ default: false }) showProblem!: boolean;
  @Prop({ default: false }) showRejudge!: boolean;
  @Prop({ default: false }) showSubmit!: boolean;
  @Prop({ default: false }) showUser!: boolean;
  @Prop({ default: null }) problemsetProblems!: {
    [alias: string]: types.ProblemsetProblem;
  } | null;
  @Prop({ default: null }) username!: string | null;
  @Prop({ default: 100 }) rowCount!: number;
  @Prop() runs!: types.Run[];

  T = T;
  time = time;
  typeahead = typeahead;

  filterLanguage: string = '';
  filterOffset: number = 0;
  filterProblem: string = '';
  filterStatus: string = '';
  filterUsername: string = '';
  filterVerdict: string = '';

  get filteredRuns(): types.Run[] {
    if (
      !this.filterLanguage &&
      !this.filterProblem &&
      !this.filterStatus &&
      !this.filterUsername &&
      !this.filterVerdict
    ) {
      return this.runs;
    }
    return this.runs.filter(run => {
      if (this.filterVerdict) {
        if (this.filterVerdict == 'NO-AC') {
          if (run.verdict == 'AC') {
            return false;
          }
        } else if (run.verdict != this.filterVerdict) {
          return false;
        }
      }
      if (this.filterLanguage && run.language !== this.filterLanguage) {
        return false;
      }
      if (this.filterProblem && run.alias !== this.filterProblem) {
        return false;
      }
      if (this.filterStatus && run.status !== this.filterStatus) {
        return false;
      }
      if (this.filterUsername && run.username !== this.filterUsername) {
        return false;
      }
      return true;
    });
  }

  initProblemAutocomplete(el: JQuery<HTMLElement>) {
    if (this.problemsetProblems !== null) {
      typeahead.problemContestTypeahead(
        el,
        Object.values(this.problemsetProblems),
        (event: Event, item: { alias: string; title: string }) => {
          this.filterProblem = item.alias;
        },
      );
    } else {
      typeahead.problemTypeahead(el);
    }
  }

  memory(run: types.Run): string {
    if (
      run.status == 'ready' &&
      run.verdict != 'JE' &&
      run.verdict != 'VE' &&
      run.verdict != 'CE'
    ) {
      let prefix = '';
      if (run.verdict == 'MLE') {
        prefix = '>';
      }
      return `${prefix}${(run.memory / (1024 * 1024)).toFixed(2)} MB`;
    } else {
      return '—';
    }
  }

  penalty(run: types.Run): string {
    if (
      run.status == 'ready' &&
      run.verdict != 'JE' &&
      run.verdict != 'VE' &&
      run.verdict != 'CE'
    ) {
      return run.penalty.toFixed(2);
    }
    return '—';
  }

  percentage(run: types.Run): string {
    if (
      run.status == 'ready' &&
      run.verdict != 'JE' &&
      run.verdict != 'VE' &&
      run.verdict != 'CE'
    ) {
      return `${(run.score * 100).toFixed(2)}%`;
    }
    return '—';
  }

  points(run: types.Run): string {
    if (
      run.status == 'ready' &&
      run.verdict != 'JE' &&
      run.verdict != 'VE' &&
      run.verdict != 'CE'
    ) {
      return run.contest_score.toFixed(2);
    }
    return '—';
  }

  runtime(run: types.Run): string {
    if (
      run.status == 'ready' &&
      run.verdict != 'JE' &&
      run.verdict != 'VE' &&
      run.verdict != 'CE'
    ) {
      let prefix = '';
      if (run.verdict == 'TLE') {
        prefix = '>';
      }
      return `${prefix}${(run.runtime / 1000).toFixed(2)} s`;
    }
    return '—';
  }

  showVerdictHelp(ev: Event): void {
    $(<HTMLElement>ev.target).popover('show');
  }

  statusColor(run: types.Run): string {
    if (run.status != 'ready') return '';

    if (run.type == 'disqualified') return '#F00';

    if (run.verdict == 'AC') {
      return '#CF6';
    } else if (run.verdict == 'CE') {
      return '#F90';
    } else if (run.verdict == 'JE' || run.verdict == 'VE') {
      return '#F00';
    } else {
      return '';
    }
  }

  status(run: types.Run): string {
    if (run.type == 'disqualified') return T.wordsDisqualified;

    return run.status == 'ready' ? T[`verdict${run.verdict}`] : run.status;
  }

  statusHelp(run: types.Run): string {
    if (run.status != 'ready' || run.verdict == 'AC') {
      return '';
    }

    if (run.language == 'kj' || run.language == 'kp') {
      if (run.verdict == 'RTE' || run.verdict == 'RE') {
        return T.verdictHelpKarelRTE;
      } else if (run.verdict == 'TLE' || run.verdict == 'TO') {
        return T.verdictHelpKarelTLE;
      }
    }
    if (run.type == 'disqualified') return T.verdictHelpDisqualified;

    return T[`verdictHelp${run.verdict}`];
  }

  @Watch('username')
  onUsernameChanged(newValue: string | null, oldValue: string | null) {
    if (!newValue) {
      this.filterUsername = '';
    } else {
      this.filterUsername = newValue;
    }
  }

  @Watch('filterLanguage')
  onFilterLanguageChanged(newValue: string, oldValue: string) {
    this.$emit('filter-changed');
  }

  @Watch('filterOffset')
  onFilterOffsetChanged(newValue: number, oldValue: number) {
    this.$emit('filter-changed');
  }

  @Watch('filterProblem')
  onFilterProblemChanged(newValue: number, oldValue: number) {
    this.$emit('filter-changed');
  }

  @Watch('filterStatus')
  onFilterStatusChanged(newValue: number, oldValue: number) {
    this.$emit('filter-changed');
  }

  @Watch('filterUsername')
  onFilterUsernameChanged(newValue: number, oldValue: number) {
    this.$emit('filter-changed');
  }

  @Watch('filterVerdict')
  onFilterVerdictChanged(newValue: number, oldValue: number) {
    this.$emit('filter-changed');
  }
}
</script>
