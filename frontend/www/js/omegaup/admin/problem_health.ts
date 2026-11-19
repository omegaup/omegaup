import T from '../lang';

// Mirrors \OmegaUp\ProblemHealthCheckType.
export enum ProblemHealthCheckType {
  JudgeErrors = 'judge_errors',
  NoLanguages = 'no_languages',
  NeverSolved = 'never_solved',
  DeprecatedPublic = 'deprecated_public',
}

// Mirrors \OmegaUp\ProblemHealthSeverity.
export enum ProblemHealthSeverity {
  Error = 'error',
  Warning = 'warning',
}

// Keying on the enums makes a new check or severity a compile error here
// instead of an untranslated cell on the dashboard.
const CHECK_TYPE_LABELS: Record<ProblemHealthCheckType, string> = {
  [ProblemHealthCheckType.JudgeErrors]: T.problemHealthCheckJudgeErrors,
  [ProblemHealthCheckType.NoLanguages]: T.problemHealthCheckNoLanguages,
  [ProblemHealthCheckType.NeverSolved]: T.problemHealthCheckNeverSolved,
  [ProblemHealthCheckType.DeprecatedPublic]:
    T.problemHealthCheckDeprecatedPublic,
};

const SEVERITY_LABELS: Record<ProblemHealthSeverity, string> = {
  [ProblemHealthSeverity.Error]: T.problemHealthSeverityError,
  [ProblemHealthSeverity.Warning]: T.problemHealthSeverityWarning,
};

const SEVERITY_CLASSES: Record<ProblemHealthSeverity, string> = {
  [ProblemHealthSeverity.Error]: 'badge badge-danger',
  [ProblemHealthSeverity.Warning]: 'badge badge-warning',
};

// The api sends the raw column value, so a check this build does not know
// about is shown as it came instead of being hidden.
export function checkTypeLabel(checkType: string): string {
  return CHECK_TYPE_LABELS[checkType as ProblemHealthCheckType] ?? checkType;
}

export function severityLabel(severity: string): string {
  return SEVERITY_LABELS[severity as ProblemHealthSeverity] ?? severity;
}

export function severityClass(severity: string): string {
  return (
    SEVERITY_CLASSES[severity as ProblemHealthSeverity] ?? 'badge badge-light'
  );
}
