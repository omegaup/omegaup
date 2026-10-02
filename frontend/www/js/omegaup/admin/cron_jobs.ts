import T from '../lang';

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

// Keyed on the registry enum so a new job is a compile error here instead of
// an untitled row. Cron_Runs.name is a plain string, so a title for a job the
// registry does not list yet is still allowed.
const JOB_TITLES: Record<CronJobName, string> & Record<string, string> = {
  [CronJobName.AggregateFeedback]: T.cronControlPlaneJobAggregateFeedback,
  [CronJobName.AssignBadges]: T.cronControlPlaneJobAssignBadges,
  [CronJobName.BuildProblemRecModel]: T.cronControlPlaneJobBuildProblemRecModel,
  [CronJobName.ProblemHealthCheck]: T.cronControlPlaneJobProblemHealthCheck,
  [CronJobName.UpdateRanks]: T.cronControlPlaneJobUpdateRanks,
  'plagiarism_detector.py': T.cronControlPlaneJobPlagiarismDetector,
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

// A job with no title of its own reads from its script name instead of being
// hidden, and the same goes for a phase the dashboard has never seen.
export function jobTitle(name: string): string {
  return JOB_TITLES[name] ?? humanize(name.replace(/\.py$/, ''));
}

export function phaseTitle(phase: string): string {
  return humanize(phase);
}

export function statusClass(status?: string | null): string {
  if (!status) {
    return '';
  }
  return STATUS_CLASSES[status as CronRunStatus] ?? 'badge badge-light';
}
