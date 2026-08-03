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

describe('Crons.vue', () => {
  it('Should render the jobs and runs tables', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    expect(wrapper.find('[data-cron-jobs]').exists()).toBe(true);
    expect(wrapper.find('[data-cron-runs]').exists()).toBe(true);
    expect(wrapper.text()).toContain('Update rankings');
    expect(wrapper.find('.badge-success').exists()).toBe(true);
    expect(wrapper.find('.badge-danger').exists()).toBe(true);
  });

  it('Should show a health card per job with its success rate', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    expect(wrapper.find('[data-cron-health]').exists()).toBe(true);
    expect(wrapper.find('[data-cron-health]').text()).toContain('100%');
  });

  it('Should filter runs by status', async () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    expect(wrapper.findAll('[data-cron-runs] tbody tr').length).toBe(2);
    await wrapper.find('[data-cron-filter-status]').setValue('failure');
    const rows = wrapper.findAll('[data-cron-runs] tbody tr');
    expect(rows.length).toBe(1);
    expect(rows.at(0).text()).toContain('Award badges');
  });

  it('Should show phase detail when a run is expanded', async () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    expect(wrapper.find('[data-cron-phases]').exists()).toBe(false);
    await wrapper.findAll('[data-cron-runs] tbody tr').at(0).trigger('click');
    expect(wrapper.find('[data-cron-phases]').exists()).toBe(true);
    expect(wrapper.find('[data-cron-phases]').text()).toContain(
      'Update users stats',
    );
  });

  it('Should keep the script name on hover while showing a readable one', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    const cell = wrapper.find('[data-cron-jobs] tbody tr td span');
    expect(cell.text()).toBe('Update rankings');
    expect(cell.attributes('title')).toBe('update_ranks.py');
  });

  it('Should show the schedule in words with the expression on hover', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    const schedule = wrapper.findAll('[data-cron-jobs] tbody tr td').at(1);
    expect(schedule.text()).toContain('08:19');
    expect(schedule.find('span').attributes('title')).toBe('19 8 * * *');
  });

  it('Should fall back to the raw schedule when it is not a simple one', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    expect((wrapper.vm as any).humanSchedule('*/5 * * * *')).toBe(
      '*/5 * * * *',
    );
    expect((wrapper.vm as any).humanSchedule('0 4 * * 0')).toContain('Sunday');
  });

  it('Should show an empty state when there are no runs', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs: [] } });

    expect(wrapper.text()).toContain(T.cronControlPlaneNoRuns);
  });

  it('Should emit rerun with the job name when the button is clicked', async () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    await wrapper.find('[data-cron-rerun]').trigger('click');

    const emitted = wrapper.emitted('rerun');
    expect(emitted).toBeTruthy();
    expect(emitted?.[0]).toEqual(['update_ranks.py']);
  });
});
