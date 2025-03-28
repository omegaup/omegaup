<template>
  <div class="mt-4" data-runs>
    <h5 class="mb-3">{{ T.wordsSubmissions }}</h5>
    <b-table :fields="tableFields" :items="filteredRuns" striped responsive>
      <template #cell(index)="row">
        <b-button
          :disabled="!row.detailsShowing && showDetails"
          variant="link"
          size="sm"
          @click="toggleDetails(row)"
        >
          <b-icon-chevron-right v-if="!row.detailsShowing" />
          <b-icon-chevron-down v-else />
        </b-button>
      </template>

      <template #row-details>
        {{ currentRunDetails }}
      </template>

      <template #cell(guid)="data">
        <acronym :title="data.value" data-run-guid>
          <tt>{{ data.value.substring(0, 8) }}</tt>
        </acronym>
      </template>

      <template #cell(verdict)="data">
        <span class="mr-1">{{ status(data.item) }}</span>
        <b-button
          v-if="data.item.status === 'ready' && data.item.verdict !== 'AC'"
          v-b-tooltip.right="statusHelp(data.item)"
          size="sm"
          ><b-icon-question-circle-fill></b-icon-question-circle-fill>
        </b-button>
      </template>

      <!-- TODO: Add the new submission button -->
    </b-table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import * as time from '../../time';

import {
  BootstrapVue,
  BIconChevronRight,
  BIconChevronDown,
  BIconQuestionCircleFill,
} from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
Vue.use(BootstrapVue);

export enum PopupDisplayed {
  None,
  RunSubmit,
  RunDetails,
  Promotion,
  Demotion,
  Reviewer,
}

interface TableField {
  key: string;
  label: string;
  class?: string;
  thClass?: string;
  tdClass?: string;
}

interface TableRunItem {
  guid: string;
  time: string;
  language: string;
  verdict: string;
  runtime: string;
  memory: string;
  percentage: string;
  status: string;
  type?: string;
  _cellVariants?: {
    [key: string]: string;
  };
}

@Component({
  components: {
    BIconChevronRight,
    BIconChevronDown,
    BIconQuestionCircleFill,
  },
})
export default class Runs extends Vue {
  @Prop() currentRunDetails!: types.RunDetails | null;
  @Prop({ default: null }) problemAlias!: string | null;
  @Prop() runs!: null | types.Run[];

  T = T;
  time = time;
  showDetails = false;

  toggleDetails(row: { toggleDetails: () => void; item: TableRunItem }): void {
    this.showDetails = !this.showDetails;
    if (this.showDetails) {
      this.$emit('show-run-details', { guid: row.item.guid });
    }
    row.toggleDetails();
  }

  get filteredRuns(): TableRunItem[] {
    return this.sortedRuns.map((run) => {
      return {
        time: time.formatDateLocalHHMM(run.time),
        guid: run.guid,
        language: run.language,
        memory: this.memory(run),
        percentage: this.percentage(run),
        runtime: this.runtime(run),
        verdict: run.verdict,
        status: run.status,
        type: run.type,
        _cellVariants: {
          verdict: this.statusClass(run),
        },
      };
    });
  }

  get tableFields(): (string | TableField)[] {
    return [
      {
        label: '',
        key: 'index',
        class: 'align-middle',
      },
      {
        label: T.wordsTime,
        key: 'time',
        class: 'text-center align-middle',
      },
      {
        label: T.runGUID,
        key: 'guid',
        class: 'text-center align-middle',
      },
      // TODO: Add the participant, contest and problem...
      {
        label: T.wordsStatus,
        key: 'verdict',
        class: 'text-center align-middle',
      },
      // TODO: Add the points and penalty...
      {
        label: T.wordsPercentage,
        key: 'percentage',
        class: 'align-middle',
        thClass: 'text-center',
        tdClass: 'text-right',
      },
      {
        label: T.wordsLanguage,
        key: 'language',
        class: 'text-center align-middle',
      },
      {
        label: T.wordsMemory,
        key: 'memory',
        class: 'align-middle',
        thClass: 'text-center',
        tdClass: 'text-right',
      },
      {
        label: T.wordsRuntime,
        key: 'runtime',
        class: 'align-middle',
        thClass: 'text-center',
        tdClass: 'text-right',
      },
      {
        label: T.wordsActions,
        key: 'actions',
        class: 'text-center align-middle',
      },
    ];
  }

  get sortedRuns(): types.Run[] {
    if (!this.runs) {
      return [];
    }
    return this.runs
      .slice()
      .sort((a, b) => b.time.getTime() - a.time.getTime());
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

  statusClass(run: types.Run): string {
    if (run.status != 'ready') return '';
    if (run.type == 'disqualified') return 'danger';
    if (run.verdict == 'AC') {
      return 'success';
    }
    if (run.verdict == 'PA') {
      return 'info';
    }
    if (run.verdict == 'WA') {
      return 'danger';
    }
    if (run.verdict == 'TLE') {
      return 'warning';
    }
    if (run.verdict == 'OLE') {
      return 'warning';
    }
    if (run.verdict == 'MLE') {
      return 'warning';
    }
    if (run.verdict == 'RTE') {
      return 'warning';
    }
    if (run.verdict == 'RFE') {
      return 'warning';
    }
    if (run.verdict == 'CE') {
      return 'warning';
    }
    if (run.verdict == 'JE' || run.verdict == 'VE') {
      return 'danger';
    }
    return '';
  }

  status(run: types.Run): string {
    if (run.type == 'disqualified') return T.arenaRunsActionsDisqualified;

    return run.status == 'ready' ? run.verdict : run.status;
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
    const verdict = T[`verdict${run.verdict}`];
    const verdictHelp = T[`verdictHelp${run.verdict}`];

    return `${verdict}: ${verdictHelp}`;
  }
}
</script>
