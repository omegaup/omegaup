import T from '../lang';
import {
  ProblemHealthCheckType,
  ProblemHealthSeverity,
  checkTypeLabel,
  severityClass,
  severityLabel,
} from './problem_health';

describe('problem_health', () => {
  it('translates the known check types and severities', () => {
    expect(checkTypeLabel(ProblemHealthCheckType.NoLanguages)).toBe(
      T.problemHealthCheckNoLanguages,
    );
    expect(severityLabel(ProblemHealthSeverity.Error)).toBe(
      T.problemHealthSeverityError,
    );
    expect(severityClass(ProblemHealthSeverity.Error)).toBe(
      'badge badge-danger',
    );
    expect(severityClass(ProblemHealthSeverity.Warning)).toBe(
      'badge badge-warning',
    );
  });

  it('falls back to the raw value for unknown ones', () => {
    expect(checkTypeLabel('brand_new_check')).toBe('brand_new_check');
    expect(severityLabel('info')).toBe('info');
    expect(severityClass('info')).toBe('badge badge-light');
  });
});
