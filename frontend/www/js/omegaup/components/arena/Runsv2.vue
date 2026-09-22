<template>
  <div class="mt-4" data-runs>
    <h5 class="mb-3">{{ T.wordsSubmissions }}</h5>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th
              v-for="field in tableFields"
              :key="field.key"
              :class="[field.class, field.thClass]"
            >
              {{ field.label }}
            </th>
          </tr>
        </thead>
        <tbody>
          <template v-for="row in filteredRuns">
            <tr :key="row.guid">
              <td class="align-middle">
                <button
                  class="btn btn-link btn-sm"
                  type="button"
                  data-run-details-toggle
                  :disabled="expandedGuid !== row.guid && showDetails"
                  @click="toggleDetails(row)"
                >
                  <font-awesome-icon
                    v-if="expandedGuid !== row.guid"
                    icon="chevron-right"
                  />
                  <font-awesome-icon v-else icon="chevron-down" />
                </button>
              </td>
              <td class="text-center align-middle">{{ row.time }}</td>
              <td class="text-center align-middle">
                <acronym :title="row.guid" data-run-guid>
                  <tt>{{ row.guid.substring(0, 8) }}</tt>
                </acronym>
              </td>
              <td
                class="text-center align-middle"
                :class="verdictCellClass(row)"
              >
                <span class="me-1">{{ status(row) }}</span>
                <button
                  v-if="row.status === 'ready' && row.verdict !== 'AC'"
                  class="btn btn-sm"
                  type="button"
                  data-status-help
                  :title="statusHelp(row)"
                  data-bs-toggle="tooltip"
                  data-bs-placement="right"
                >
                  <font-awesome-icon icon="question-circle" />
                </button>
              </td>
              <td class="align-middle text-end">{{ row.percentage }}</td>
              <td class="text-center align-middle">{{ row.language }}</td>
              <td class="align-middle text-end">{{ row.memory }}</td>
              <td class="align-middle text-end">{{ row.runtime }}</td>
              <td class="text-center align-middle"></td>
            </tr>
            <tr v-if="expandedGuid === row.guid" :key="`${row.guid}-details`">
              <td colspan="9">
                {{ currentRunDetails }}
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import * as time from '../../time';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

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
    FontAwesomeIcon,
  },
})
export default class Runs extends Vue {
  @Prop() currentRunDetails!: types.RunDetails | null;
  @Prop({ default: null }) problemAlias!: string | null;
  @Prop() runs!: null | types.Run[];

  T = T;
  time = time;
  expandedGuid: string | null = null;

  get showDetails(): boolean {
    return this.expandedGuid !== null;
  }

  toggleDetails(row: TableRunItem): void {
    if (this.expandedGuid === row.guid) {
      this.expandedGuid = null;
      return;
    }
    this.expandedGuid = row.guid;
    this.$emit('show-run-details', { guid: row.guid });
  }

  verdictCellClass(row: TableRunItem): string {
    const variant = row._cellVariants?.verdict;
    return variant ? `table-${variant}` : '';
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

  get tableFields(): TableField[] {
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
        tdClass: 'text-end',
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
        tdClass: 'text-end',
      },
      {
        label: T.wordsRuntime,
        key: 'runtime',
        class: 'align-middle',
        thClass: 'text-center',
        tdClass: 'text-end',
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

  status(run: types.Run | TableRunItem): string {
    if (run.type == 'disqualified') return T.arenaRunsActionsDisqualified;

    return run.status == 'ready' ? run.verdict : run.status;
  }

  statusHelp(run: types.Run | TableRunItem): string {
    if (run.status != 'ready' || run.verdict == 'AC') {
      return '';
    }

    if (run.language == 'kj' || run.language == 'kp' || run.language == 'rk') {
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
