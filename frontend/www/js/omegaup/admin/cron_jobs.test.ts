import T from '../lang';
import {
  averageDuration,
  CronJobName,
  CronRunStatus,
  jobTitle,
  phaseTitle,
  RUN_STATUSES,
  statusClass,
  successRate,
} from './cron_jobs';
import { types } from '../api_types';

const run = (status: string, seconds?: number): types.CronRun => ({
  run_id: 1,
  name: CronJobName.UpdateRanks,
  status,
  duration_seconds: seconds,
  phases: [],
});

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

  it('lists the statuses a run can be in', () => {
    expect(RUN_STATUSES).toEqual(['running', 'success', 'failure']);
  });

  it('rates only the finished runs of the job it is asked about', () => {
    const runs = [
      run(CronRunStatus.Success),
      run(CronRunStatus.Failure),
      run(CronRunStatus.Failure),
      run(CronRunStatus.Running),
      { ...run(CronRunStatus.Success), name: CronJobName.AssignBadges },
    ];

    expect(successRate(runs, CronJobName.UpdateRanks)).toBe(33);
    expect(successRate(runs, CronJobName.AssignBadges)).toBe(100);
    expect(successRate(runs, CronJobName.AggregateFeedback)).toBeNull();
    expect(
      successRate([run(CronRunStatus.Running)], CronJobName.UpdateRanks),
    ).toBeNull();
  });

  it('averages only the runs that recorded a duration', () => {
    const runs = [
      run(CronRunStatus.Success, 1),
      run(CronRunStatus.Failure, 2),
      run(CronRunStatus.Running),
    ];

    expect(averageDuration(runs, CronJobName.UpdateRanks)).toBe(1.5);
    expect(averageDuration(runs, CronJobName.AssignBadges)).toBeNull();
  });

  it('titles a phase from its raw name', () => {
    expect(phaseTitle('load_runs')).toBe('Load runs');
  });
});
