import T from '../lang';
import { types } from '../api_types';

// Mirrors \OmegaUp\CronJobName.
export enum CronJobName {
  AggregateFeedback = 'aggregate_feedback.py',
  AssignBadges = 'assign_badges.py',
  BuildProblemRecModel = 'build_problem_rec_model.py',
  ProblemHealthCheck = 'problem_health_check.py',
  UpdateRanks = 'update_ranks.py',
}

// Mirrors \OmegaUp\CronRunStatus.
export enum CronRunStatus {
  Running = 'running',
  Success = 'success',
  Failure = 'failure',
}

export const RUN_STATUSES: CronRunStatus[] = Object.values(CronRunStatus);

// Keyed on the enum so a new job is a compile error here, not an untitled row.
const JOB_TITLES: Record<CronJobName, string> = {
  [CronJobName.AggregateFeedback]: T.cronControlPlaneJobAggregateFeedback,
  [CronJobName.AssignBadges]: T.cronControlPlaneJobAssignBadges,
  [CronJobName.BuildProblemRecModel]: T.cronControlPlaneJobBuildProblemRecModel,
  [CronJobName.ProblemHealthCheck]: T.cronControlPlaneJobProblemHealthCheck,
  [CronJobName.UpdateRanks]: T.cronControlPlaneJobUpdateRanks,
};

const STATUS_CLASSES: Record<CronRunStatus, string> = {
  [CronRunStatus.Running]: 'badge badge-secondary',
  [CronRunStatus.Success]: 'badge badge-success',
  [CronRunStatus.Failure]: 'badge badge-danger',
};

function humanize(value: string): string {
  const readable = value.replace(/_/g, ' ');
  return readable.charAt(0).toUpperCase() + readable.slice(1);
}

// Cron_Runs.name is a plain string, so a run can name a job the enum does not
// list. It reads from its script name instead of being hidden.
export function jobTitle(name: string): string {
  return JOB_TITLES[name as CronJobName] ?? humanize(name.replace(/\.py$/, ''));
}

export function phaseTitle(phase: string): string {
  return humanize(phase);
}

// Both figures only see the runs the page was given, the most recent ones.
export function successRate(
  runs: types.CronRun[],
  name: string,
): number | null {
  const finished = runs.filter(
    (run) => run.name === name && run.status !== CronRunStatus.Running,
  );
  if (!finished.length) {
    return null;
  }
  const succeeded = finished.filter(
    (run) => run.status === CronRunStatus.Success,
  ).length;
  return Math.round((100 * succeeded) / finished.length);
}

export function averageDuration(
  runs: types.CronRun[],
  name: string,
): number | null {
  const durations = runs
    .filter((run) => run.name === name)
    .map((run) => run.duration_seconds)
    .filter((seconds): seconds is number => typeof seconds === 'number');
  if (!durations.length) {
    return null;
  }
  return (
    durations.reduce((sum, seconds) => sum + seconds, 0) / durations.length
  );
}

export function statusClass(status?: string | null): string {
  if (!status) {
    return '';
  }
  return STATUS_CLASSES[status as CronRunStatus] ?? 'badge badge-light';
}
