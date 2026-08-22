import { mount } from '@vue/test-utils';

import Crons from './Crons.vue';
import { types } from '../../api_types';
import T from '../../lang';

const jobs: types.CronJob[] = [
  {
    name: 'update_ranks.py',
    description: 'Recomputes rankings',
    schedule: '19 8 * * *',
    enabled: true,
  },
];

const runs: types.CronRun[] = [
  {
    run_id: 1,
    name: 'update_ranks.py',
    status: 'success',
    started_at: new Date(),
    finished_at: new Date(),
    duration_seconds: 0.19,
    rows_affected: 5,
    phases: [
      { phase: 'update_users_stats', status: 'success', duration: 0.05 },
    ],
  },
  {
    run_id: 2,
    name: 'assign_badges.py',
    status: 'failure',
    started_at: new Date(),
    phases: [],
    error_text: 'boom',
  },
];

const problemHealthFindings: types.ProblemHealthFinding[] = [
  {
    problem_id: 5,
    alias: 'prob-verify-b',
    title: 'Broken problem',
    check_type: 'no_languages',
    severity: 'error',
    detail: 'the problem is public but has no enabled language',
    first_detected_at: new Date(),
  },
  {
    problem_id: 4,
    alias: 'prob-verify-a',
    title: 'Hard problem',
    check_type: 'never_solved',
    severity: 'warning',
    detail: '30 submissions and no accepted solution yet',
    first_detected_at: new Date(),
  },
];

describe('Crons.vue', () => {
  it('Should render the jobs and runs tables', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    expect(wrapper.find('[data-cron-jobs]').exists()).toBe(true);
    expect(wrapper.find('[data-cron-runs]').exists()).toBe(true);
    expect(wrapper.text()).toContain('update_ranks.py');
    expect(wrapper.find('.badge-success').exists()).toBe(true);
    expect(wrapper.find('.badge-danger').exists()).toBe(true);
  });

  it('Should show phase detail when a run is expanded', async () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    expect(wrapper.find('[data-cron-phases]').exists()).toBe(false);
    await wrapper.findAll('[data-cron-runs] tbody tr').at(0).trigger('click');
    expect(wrapper.find('[data-cron-phases]').exists()).toBe(true);
    expect(wrapper.find('[data-cron-phases]').text()).toContain(
      'update_users_stats',
    );
  });

  it('Should list the problems that need attention', () => {
    const wrapper = mount(Crons, {
      propsData: { jobs, runs, problemHealthFindings },
    });

    const table = wrapper.find('[data-problem-health]');
    expect(table.exists()).toBe(true);
    expect(table.text()).toContain('Broken problem');
    expect(table.text()).toContain('no_languages');
    expect(table.text()).toContain(
      '30 submissions and no accepted solution yet',
    );
    expect(table.find('.badge-danger').exists()).toBe(true);
  });

  it('Should show a placeholder when no problem needs attention', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    expect(wrapper.find('[data-problem-health]').exists()).toBe(false);
    expect(wrapper.text()).toContain(T.problemHealthNoFindings);
  });

  it('Should collapse an expanded run when it is clicked again', async () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });
    const row = wrapper.findAll('[data-cron-runs] tbody tr').at(0);

    await row.trigger('click');
    expect(wrapper.find('[data-cron-phases]').exists()).toBe(true);
    await row.trigger('click');
    expect(wrapper.find('[data-cron-phases]').exists()).toBe(false);
  });

  it('Should style each run status and fall back for unknown ones', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });
    const vm = wrapper.vm as any;

    expect(vm.statusClass('success')).toBe('badge badge-success');
    expect(vm.statusClass('failure')).toBe('badge badge-danger');
    expect(vm.statusClass('running')).toBe('badge badge-secondary');
    expect(vm.statusClass('something-else')).toBe('badge badge-light');
    expect(vm.statusClass(null)).toBe('');
  });

  it('Should report no status for a job that has never run', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs: [] } });
    const vm = wrapper.vm as any;

    expect(vm.latestRun('update_ranks.py')).toBeUndefined();
    expect(vm.latestStatus('update_ranks.py')).toBeNull();
    expect(vm.latestStartedAt('update_ranks.py')).toBe('—');
  });

  it('Should show a dash instead of empty values', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });
    const vm = wrapper.vm as any;

    expect(vm.formatDate(null)).toBe('—');
    expect(vm.formatDuration(null)).toBe('—');
    expect(vm.formatDuration(undefined)).toBe('—');
    expect(vm.formatRows(null)).toBe('—');
    expect(vm.formatRows(undefined)).toBe('—');
    // Zero is a real value, not an empty one.
    expect(vm.formatDuration(0)).toBe('0.00s');
    expect(vm.formatRows(0)).toBe('0');
  });
});
