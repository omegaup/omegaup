<template>
  <div class="card">
    <div class="text-white bg-primary card-header">
      <div class="card-title h4 mb-0">{{ T.omegaupTitleAdminCrons }}</div>
    </div>
    <div class="card-body">
      <h5>{{ T.cronControlPlaneJobsHeading }}</h5>
      <table class="table table-sm" data-cron-jobs>
        <thead>
          <tr>
            <th>{{ T.cronControlPlaneName }}</th>
            <th>{{ T.cronControlPlaneSchedule }}</th>
            <th>{{ T.cronControlPlaneLastStatus }}</th>
            <th>{{ T.cronControlPlaneLastRun }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="job in jobs" :key="job.name">
            <td>{{ job.name }}</td>
            <td>
              <code v-if="job.schedule">{{ job.schedule }}</code>
              <span v-else>—</span>
            </td>
            <td>
              <span :class="statusClass(latestStatus(job.name))">{{
                latestStatus(job.name) || '—'
              }}</span>
            </td>
            <td>{{ latestStartedAt(job.name) }}</td>
            <td class="text-right">
              <button
                class="btn btn-sm btn-outline-primary"
                type="button"
                data-cron-rerun
                @click="rerun(job.name)"
              >
                {{ T.cronControlPlaneRerun }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <h5 class="mt-4">{{ T.cronControlPlaneRunsHeading }}</h5>
      <table class="table table-sm table-hover" data-cron-runs>
        <thead>
          <tr>
            <th></th>
            <th>{{ T.cronControlPlaneName }}</th>
            <th>{{ T.cronControlPlaneStatus }}</th>
            <th>{{ T.cronControlPlaneStarted }}</th>
            <th>{{ T.cronControlPlaneDuration }}</th>
            <th>{{ T.cronControlPlaneRows }}</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="run in runs">
            <tr
              :key="run.run_id"
              class="cron-run-row"
              :class="{ 'table-active': expandedRunId === run.run_id }"
              role="button"
              @click="toggle(run.run_id)"
            >
              <td>
                <span
                  class="cron-caret"
                  :class="{ 'cron-caret--open': expandedRunId === run.run_id }"
                  >▸</span
                >
              </td>
              <td>{{ run.name }}</td>
              <td>
                <span :class="statusClass(run.status)">{{ run.status }}</span>
              </td>
              <td>{{ formatDate(run.started_at) }}</td>
              <td>{{ formatDuration(run.duration_seconds) }}</td>
              <td>{{ formatRows(run.rows_affected) }}</td>
            </tr>
            <tr
              v-if="expandedRunId === run.run_id"
              :key="`detail-${run.run_id}`"
              class="cron-run-detail"
            >
              <td></td>
              <td colspan="5">
                <div v-if="run.error_text" class="text-danger mb-2">
                  {{ run.error_text }}
                </div>
                <table
                  v-if="run.phases.length"
                  class="table table-sm table-borderless mb-0"
                  data-cron-phases
                >
                  <tbody>
                    <tr v-for="(phase, index) in run.phases" :key="index">
                      <td>{{ phase.phase }}</td>
                      <td>
                        <span :class="statusClass(phase.status)">{{
                          phase.status
                        }}</span>
                      </td>
                      <td>{{ phase.duration.toFixed(3) }}s</td>
                    </tr>
                  </tbody>
                </table>
                <span v-else>{{ T.cronControlPlaneNoPhases }}</span>
              </td>
            </tr>
          </template>
        </tbody>
      </table>

      <h5 class="mt-4">{{ T.cronControlPlaneModelHeading }}</h5>
      <table
        v-if="recommendationModelRuns.length"
        class="table table-sm table-hover"
        data-cron-model-runs
      >
        <thead>
          <tr>
            <th>{{ T.cronControlPlaneStarted }}</th>
            <th>
              {{ T.cronControlPlaneModelScore }}
              <font-awesome-icon
                v-b-tooltip.hover
                class="text-muted"
                :icon="['fas', 'info-circle']"
                :title="T.cronControlPlaneModelScoreInfo"
              />
            </th>
            <th>{{ T.cronControlPlaneModelDataset }}</th>
            <th>{{ T.cronControlPlaneModelPublished }}</th>
            <th>{{ T.cronControlPlaneModelSkipReason }}</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(modelRun, index) in recommendationModelRuns"
            :key="`model-run-${index}`"
          >
            <td>{{ formatDate(modelRun.created_at) }}</td>
            <td>{{ modelRun.map_score.toFixed(4) }}</td>
            <td>{{ modelRun.dataset_size }}</td>
            <td>
              <span :class="publishedClass(modelRun.published)">{{
                modelRun.published ? '✓' : '✗'
              }}</span>
            </td>
            <td>{{ modelRun.skip_reason || '—' }}</td>
          </tr>
        </tbody>
      </table>
      <span v-else>{{ T.cronControlPlaneModelNoRuns }}</span>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { VBTooltipPlugin } from 'bootstrap-vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faInfoCircle } from '@fortawesome/free-solid-svg-icons';
import T from '../../lang';
import { types } from '../../api_types';

library.add(faInfoCircle);
Vue.use(VBTooltipPlugin);

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
  },
})
export default class Crons extends Vue {
  T = T;
  @Prop({ default: () => [] }) jobs!: types.CronJob[];
  @Prop({ default: () => [] }) runs!: types.CronRun[];
  @Prop({ default: () => [] })
  recommendationModelRuns!: types.RecommendationModelRun[];

  publishedClass(published: boolean): string {
    return published ? 'badge badge-success' : 'badge badge-secondary';
  }

  expandedRunId: number | null = null;

  toggle(runId: number): void {
    this.expandedRunId = this.expandedRunId === runId ? null : runId;
  }

  rerun(name: string): void {
    this.$emit('rerun', name);
  }

  statusClass(status: string | null): string {
    const classes: Record<string, string> = {
      success: 'badge badge-success',
      failure: 'badge badge-danger',
      running: 'badge badge-secondary',
    };
    if (!status) {
      return '';
    }
    return classes[status] || 'badge badge-light';
  }

  latestRun(name: string): types.CronRun | undefined {
    return this.runs.find((run) => run.name === name);
  }

  latestStatus(name: string): string | null {
    return this.latestRun(name)?.status ?? null;
  }

  latestStartedAt(name: string): string {
    const run = this.latestRun(name);
    return run ? this.formatDate(run.started_at) : '—';
  }

  formatDate(date: Date | null | undefined): string {
    return date ? new Date(date).toLocaleString() : '—';
  }

  formatDuration(seconds: number | null | undefined): string {
    return typeof seconds === 'number' ? `${seconds.toFixed(2)}s` : '—';
  }

  formatRows(rows: number | null | undefined): string {
    return typeof rows === 'number' ? String(rows) : '—';
  }
}
</script>

<style lang="scss" scoped>
.cron-run-row {
  cursor: pointer;
}

.cron-caret {
  display: inline-block;
  transition: transform 0.2s ease;
}

.cron-caret--open {
  transform: rotate(90deg);
}

.cron-run-detail > td {
  border-top: 0;
  animation: cron-expand 0.2s ease;
}

@keyframes cron-expand {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
