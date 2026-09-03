<template>
  <div class="card">
    <div class="text-white bg-primary card-header">
      <div class="card-title h4">{{ T.omegaupTitleAdminCrons }}</div>
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
          </tr>
        </thead>
        <tbody>
          <tr v-for="job in scheduledJobs" :key="job.name">
            <td>{{ job.name }}</td>
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
            <td>{{ latestStartedAt(job.name) }}</td>
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
import { Vue, Component, Prop } from 'vue-property-decorator';
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

@Component
export default class Crons extends Vue {
  T = T;
  @Prop({ default: () => [] }) jobs!: types.CronJob[];
  @Prop({ default: () => [] }) runs!: types.CronRun[];

  expandedRunId: number | null = null;

  get scheduledJobs(): (types.CronJob & { humanSchedule: string | null })[] {
    return this.jobs.map((job) => ({
      ...job,
      humanSchedule: describeSchedule(job.schedule),
    }));
  }

  toggle(runId: number): void {
    this.expandedRunId = this.expandedRunId === runId ? null : runId;
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

  formatDate(date: Date | null | undefined): string {
    return date ? time.formatDateTime(date) : '—';
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
