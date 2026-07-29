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

  it('Should emit rerun with the job name when the button is clicked', async () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    await wrapper.find('[data-cron-rerun]').trigger('click');

    const emitted = wrapper.emitted('rerun');
    expect(emitted).toBeTruthy();
    expect(emitted?.[0]).toEqual(['update_ranks.py']);
  });
});
