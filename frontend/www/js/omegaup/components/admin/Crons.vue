<template>
  <div class="card">
    <div class="text-white card-header cron-card-header">
      <div class="card-title h4 mb-0">{{ T.omegaupTitleAdminCrons }}</div>
    </div>
    <div class="card-body">
      <div class="row mb-4" data-cron-health>
        <div v-for="job in jobs" :key="job.name" class="col-sm-6 col-lg-4 mb-2">
          <div class="card h-100">
            <div class="card-body">
              <h6 class="card-title text-truncate" :title="job.name">
                {{ job.name }}
              </h6>
              <span :class="statusClass(latestStatus(job.name))">{{
                latestStatus(job.name) || '—'
              }}</span>
              <dl class="row small mb-0 mt-2">
                <dt class="col-7 font-weight-normal">
                  {{ T.cronControlPlaneSuccessRate }}
                </dt>
                <dd class="col-5 mb-0 text-right">
                  {{ successRate(job.name) }}
                </dd>
                <dt class="col-7 font-weight-normal">
                  {{ T.cronControlPlaneAvgDuration }}
                </dt>
                <dd class="col-5 mb-0 text-right">
                  {{ avgDuration(job.name) }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

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
            <td>
              <span :title="latestStartedAt(job.name)">{{
                latestStartedAtRelative(job.name)
              }}</span>
            </td>
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
              <td>
                <span :title="formatDate(run.started_at)">{{
                  formatRelative(run.started_at)
                }}</span>
              </td>
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
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import { formatFutureDateRelative } from '../../time';

@Component
export default class Crons extends Vue {
  T = T;
  @Prop({ default: () => [] }) jobs!: types.CronJob[];
  @Prop({ default: () => [] }) runs!: types.CronRun[];

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

  latestStartedAtRelative(name: string): string {
    const run = this.latestRun(name);
    return run ? this.formatRelative(run.started_at) : '—';
  }

  runsForJob(name: string): types.CronRun[] {
    return this.runs.filter((run) => run.name === name);
  }

  successRate(name: string): string {
    const finished = this.runsForJob(name).filter(
      (run) => run.status !== 'running',
    );
    if (!finished.length) {
      return '—';
    }
    const succeeded = finished.filter((run) => run.status === 'success').length;
    return `${Math.round((100 * succeeded) / finished.length)}%`;
  }

  avgDuration(name: string): string {
    const durations = this.runsForJob(name)
      .map((run) => run.duration_seconds)
      .filter((seconds): seconds is number => typeof seconds === 'number');
    if (!durations.length) {
      return '—';
    }
    const total = durations.reduce((sum, seconds) => sum + seconds, 0);
    return `${(total / durations.length).toFixed(2)}s`;
  }

  formatDate(date: Date | null | undefined): string {
    return date ? new Date(date).toLocaleString() : '—';
  }

  formatRelative(date: Date | null | undefined): string {
    return date ? formatFutureDateRelative(new Date(date)) : '—';
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
.cron-card-header {
  background-color: var(--header-primary-color);
}

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
