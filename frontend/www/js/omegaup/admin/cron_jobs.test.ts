import T from '../lang';
import {
  CronJobName,
  CronRunStatus,
  jobTitle,
  phaseTitle,
  statusClass,
} from './cron_jobs';

describe('cron_jobs', () => {
  it('translates the registered jobs and the known statuses', () => {
    expect(jobTitle(CronJobName.UpdateRanks)).toBe(
      T.cronControlPlaneJobUpdateRanks,
    );
    expect(statusClass(CronRunStatus.Success)).toBe('badge badge-success');
    expect(statusClass(CronRunStatus.Failure)).toBe('badge badge-danger');
    expect(statusClass(CronRunStatus.Running)).toBe('badge badge-secondary');
  });

  it('reads an unknown job or status from the raw value', () => {
    expect(jobTitle('brand_new_job.py')).toBe('Brand new job');
    expect(statusClass('cancelled')).toBe('badge badge-light');
    expect(statusClass(null)).toBe('');
  });

  it('titles a phase from its raw name', () => {
    expect(phaseTitle('load_runs')).toBe('Load runs');
  });
});
