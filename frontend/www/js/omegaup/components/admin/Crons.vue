<template>
  <div class="card">
    <div class="text-white bg-primary card-header">
      <div class="card-title h4">{{ T.omegaupTitleAdminCrons }}</div>
    </div>
    <div class="card-body">
      <div class="row mb-4" data-cron-health>
        <div v-for="job in jobs" :key="job.name" class="col-sm-6 col-lg-4 mb-2">
          <div class="card h-100 cron-health-card">
            <div class="card-body">
              <h6 class="card-title text-truncate mb-0">
                {{ jobTitle(job.name) }}
              </h6>
              <code class="small d-block text-truncate mb-2">{{
                job.name
              }}</code>
              <span :class="statusClass(latestStatus(job.name))">{{
                latestStatus(job.name) || '—'
              }}</span>
              <dl class="row small mb-0 mt-2">
                <dt class="col-7 font-weight-normal">
                  {{ T.cronControlPlaneSuccessRate }}
                  <font-awesome-icon
                    v-b-tooltip.hover
                    class="text-muted"
                    :icon="['fas', 'info-circle']"
                    :title="T.cronControlPlaneSuccessRateInfo"
                  />
                </dt>
                <dd class="col-5 mb-0 text-right">
                  {{ successRate(job.name) }}
                </dd>
                <dt class="col-7 font-weight-normal">
                  {{ T.cronControlPlaneAvgDuration }}
                  <font-awesome-icon
                    v-b-tooltip.hover
                    class="text-muted"
                    :icon="['fas', 'info-circle']"
                    :title="T.cronControlPlaneAvgDurationInfo"
                  />
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
          <tr v-if="!jobs.length">
            <td colspan="5" class="text-muted">
              {{ T.cronControlPlaneNoJobs }}
            </td>
          </tr>
          <tr v-for="job in scheduledJobs" :key="job.name">
            <td>
              <code>{{ job.name }}</code>
              <small class="d-block text-muted">{{ jobTitle(job.name) }}</small>
              <small v-if="job.description" class="d-block text-muted">
                {{ job.description }}
              </small>
            </td>
            <td>
              <template v-if="job.schedule">
                <code>{{ job.schedule }}</code>
                <small v-if="job.humanSchedule" class="d-block text-muted">{{
                  job.humanSchedule
                }}</small>
              </template>
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
              <div
                class="custom-control custom-switch d-inline-block mr-2 align-middle"
              >
                <input
                  :id="`cron-enabled-${job.name}`"
                  class="custom-control-input"
                  type="checkbox"
                  :checked="job.enabled"
                  data-cron-enabled
                  @change="
                    setEnabled(job.name, $event.target.checked, $event.target)
                  "
                />
                <label
                  class="custom-control-label"
                  :for="`cron-enabled-${job.name}`"
                  >{{ T.cronControlPlaneEnabled }}</label
                >
              </div>
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
      <div class="form-inline mb-2" data-cron-filters>
        <select
          v-model="filterJob"
          class="form-control form-control-sm mr-2"
          data-cron-filter-job
        >
          <option value="">{{ T.cronControlPlaneAllJobs }}</option>
          <option v-for="job in jobs" :key="job.name" :value="job.name">
            {{ jobTitle(job.name) }}
          </option>
        </select>
        <select
          v-model="filterStatus"
          class="form-control form-control-sm"
          data-cron-filter-status
        >
          <option value="">{{ T.cronControlPlaneAllStatuses }}</option>
          <option value="success">success</option>
          <option value="failure">failure</option>
          <option value="running">running</option>
        </select>
      </div>
      <table class="table table-sm table-hover" data-cron-runs>
        <thead>
          <tr>
            <th></th>
            <th>{{ T.cronControlPlaneName }}</th>
            <th>{{ T.cronControlPlaneStatus }}</th>
            <th>{{ T.cronControlPlaneStarted }}</th>
            <th>
              {{ T.cronControlPlaneDuration }}
              <font-awesome-icon
                v-b-tooltip.hover
                class="text-muted"
                :icon="['fas', 'info-circle']"
                :title="T.cronControlPlaneDurationInfo"
              />
            </th>
            <th>
              {{ T.cronControlPlaneRows }}
              <font-awesome-icon
                v-b-tooltip.hover
                class="text-muted"
                :icon="['fas', 'info-circle']"
                :title="T.cronControlPlaneRowsInfo"
              />
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!filteredRuns.length">
            <td colspan="6" class="text-muted">
              {{ T.cronControlPlaneNoRuns }}
            </td>
          </tr>
          <template v-for="run in filteredRuns">
            <tr
              :key="run.run_id"
              class="cron-run-row"
              :class="{ 'table-active': expandedRunId === run.run_id }"
              @click="toggle(run.run_id)"
            >
              <td>
                <span
                  class="cron-caret"
                  :class="{ 'cron-caret--open': expandedRunId === run.run_id }"
                  >▸</span
                >
              </td>
              <td>
                <code>{{ run.name }}</code>
                <small class="d-block text-muted">{{
                  jobTitle(run.name)
                }}</small>
              </td>
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
                <div v-if="run.hostname" class="small text-muted mb-2">
                  {{ T.cronControlPlaneHost }}: {{ run.hostname }}
                  <font-awesome-icon
                    v-b-tooltip.hover
                    :icon="['fas', 'info-circle']"
                    :title="T.cronControlPlaneHostInfo"
                  />
                </div>
                <table
                  v-if="run.phases.length"
                  class="table table-sm table-borderless mb-0"
                  data-cron-phases
                >
                  <thead>
                    <tr>
                      <th>
                        {{ T.cronControlPlaneStep }}
                        <font-awesome-icon
                          v-b-tooltip.hover
                          class="text-muted"
                          :icon="['fas', 'info-circle']"
                          :title="T.cronControlPlaneStepsInfo"
                        />
                      </th>
                      <th>{{ T.cronControlPlaneStatus }}</th>
                      <th>{{ T.cronControlPlaneDuration }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(phase, index) in run.phases" :key="index">
                      <td>
                        <code>{{ phase.phase }}</code>
                        <small class="d-block text-muted">{{
                          phaseTitle(phase.phase)
                        }}</small>
                      </td>
                      <td>
                        <span :class="statusClass(phase.status)">{{
                          phase.status
                        }}</span>
                      </td>
                      <td>{{ formatDuration(phase.duration) }}</td>
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
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { VBTooltipPlugin } from 'bootstrap-vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faInfoCircle } from '@fortawesome/free-solid-svg-icons';
import T from '../../lang';
import * as time from '../../time';
import * as ui from '../../ui';
import { types } from '../../api_types';

const CRON_FIELD_COUNT = 5;

function numericField(field: string, min: number, max: number): number | null {
  if (!/^\d+$/.test(field)) {
    return null;
  }
  const value = Number(field);
  return value >= min && value <= max ? value : null;
}

function formatTime(hour: number, minute: number): string {
  return new Date(2024, 0, 1, hour, minute).toLocaleTimeString(T.locale, {
    hour: '2-digit',
    minute: '2-digit',
  });
}

function formatWeekday(dayOfWeek: number): string {
  // Both 2024-01-07 and 2024-01-14 were Sundays, which is how cron's 0 and 7
  // both land on it.
  return new Date(Date.UTC(2024, 0, 7 + dayOfWeek)).toLocaleDateString(
    T.locale,
    { weekday: 'long', timeZone: 'UTC' },
  );
}

// Describes the schedules the registry actually uses, and gives up on the rest
// rather than guessing, in which case only the expression itself is shown.
export function describeSchedule(schedule?: string | null): string | null {
  if (!schedule) {
    return null;
  }
  const fields = schedule.trim().split(/\s+/);
  if (fields.length !== CRON_FIELD_COUNT) {
    return null;
  }
  const [
    minuteField,
    hourField,
    dayOfMonthField,
    monthField,
    dayOfWeekField,
  ] = fields;
  if (monthField !== '*') {
    return null;
  }
  const everyMinutes = /^\*\/(\d+)$/.exec(minuteField);
  if (
    everyMinutes &&
    hourField === '*' &&
    dayOfMonthField === '*' &&
    dayOfWeekField === '*'
  ) {
    return ui.formatString(T.cronControlPlaneScheduleEveryMinutes, {
      minutes: everyMinutes[1],
    });
  }
  const minute = numericField(minuteField, 0, 59);
  if (minute === null) {
    return null;
  }
  if (hourField === '*' && dayOfMonthField === '*' && dayOfWeekField === '*') {
    return ui.formatString(T.cronControlPlaneScheduleHourly, {
      minute: String(minute).padStart(2, '0'),
    });
  }
  const hour = numericField(hourField, 0, 23);
  if (hour === null) {
    return null;
  }
  const timeOfDay = formatTime(hour, minute);
  if (dayOfMonthField === '*' && dayOfWeekField === '*') {
    return ui.formatString(T.cronControlPlaneScheduleDaily, {
      time: timeOfDay,
    });
  }
  const dayOfWeek = numericField(dayOfWeekField, 0, 7);
  if (dayOfMonthField === '*' && dayOfWeek !== null) {
    return ui.formatString(T.cronControlPlaneScheduleWeekly, {
      weekday: formatWeekday(dayOfWeek),
      time: timeOfDay,
    });
  }
  const dayOfMonth = numericField(dayOfMonthField, 1, 31);
  if (dayOfMonth !== null && dayOfWeekField === '*') {
    return ui.formatString(T.cronControlPlaneScheduleMonthly, {
      dayOfMonth: String(dayOfMonth),
      time: timeOfDay,
    });
  }
  return null;
}

library.add(faInfoCircle);
Vue.use(VBTooltipPlugin);

const JOB_TITLES: Record<string, string> = {
  'update_ranks.py': T.cronControlPlaneJobUpdateRanks,
  'assign_badges.py': T.cronControlPlaneJobAssignBadges,
  'aggregate_feedback.py': T.cronControlPlaneJobAggregateFeedback,
  'build_problem_rec_model.py': T.cronControlPlaneJobBuildProblemRecModel,
  'plagiarism_detector.py': T.cronControlPlaneJobPlagiarismDetector,
  'problem_health_check.py': T.cronControlPlaneJobProblemHealthCheck,
};

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
  },
})
export default class Crons extends Vue {
  T = T;
  @Prop({ default: () => [] }) jobs!: types.CronJob[];
  @Prop({ default: () => [] }) runs!: types.CronRun[];

  expandedRunId: number | null = null;
  filterJob = '';
  filterStatus = '';

  get filteredRuns(): types.CronRun[] {
    return this.runs.filter(
      (run) =>
        (!this.filterJob || run.name === this.filterJob) &&
        (!this.filterStatus || run.status === this.filterStatus),
    );
  }

  @Watch('jobs')
  onJobsChanged(jobs: types.CronJob[]): void {
    // A refresh can drop the job the filter names, leaving it silently on.
    if (this.filterJob && !jobs.some((job) => job.name === this.filterJob)) {
      this.filterJob = '';
    }
  }

  get scheduledJobs(): (types.CronJob & { humanSchedule: string | null })[] {
    return this.jobs.map((job) => ({
      ...job,
      humanSchedule: describeSchedule(job.schedule),
    }));
  }

  jobTitle(name: string): string {
    if (JOB_TITLES[name]) {
      return JOB_TITLES[name];
    }
    const readable = name.replace(/\.py$/, '').replace(/_/g, ' ');
    return readable.charAt(0).toUpperCase() + readable.slice(1);
  }

  phaseTitle(phase: string): string {
    const readable = phase.replace(/_/g, ' ');
    return readable.charAt(0).toUpperCase() + readable.slice(1);
  }

  toggle(runId: number): void {
    this.expandedRunId = this.expandedRunId === runId ? null : runId;
  }

  rerun(name: string): void {
    this.$emit('rerun', name);
  }

  setEnabled(name: string, enabled: boolean, input: HTMLInputElement): void {
    // The switch shows job.enabled, so put it back and let the reply move it.
    input.checked = !enabled;
    this.$emit('set-enabled', { name, enabled });
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

  // runs arrive newest first.
  latestRun(name: string): types.CronRun | undefined {
    return this.runs.find((run) => run.name === name);
  }

  latestStatus(name: string): string | null {
    return this.latestRun(name)?.status ?? null;
  }

  latestStartedAt(name: string): string {
    return this.formatDate(this.latestRun(name)?.started_at);
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
    return date ? time.formatDateTime(date) : '—';
  }

  formatRelative(date: Date | null | undefined): string {
    return date ? time.formatFutureDateRelative(new Date(date)) : '—';
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
@import '../../../../sass/main.scss';

.cron-health-card {
  border-top: 3px solid $omegaup-primary--accent;
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
