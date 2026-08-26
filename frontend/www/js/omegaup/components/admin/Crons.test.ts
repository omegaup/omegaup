import { mount } from '@vue/test-utils';
import T from '../../lang';
import * as time from '../../time';

import Crons from './Crons.vue';
import { types } from '../../api_types';

const startedAt = new Date(Date.UTC(2026, 0, 2, 3, 4, 5));

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
    started_at: startedAt,
    finished_at: startedAt,
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
    started_at: startedAt,
    phases: [],
    error_text: 'boom',
  },
];

describe('Crons.vue', () => {
  it('Should show each job with the status of its latest run', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });
    const cells = wrapper.findAll('[data-cron-jobs] tbody tr td');

    expect(cells.at(0).text()).toBe('update_ranks.py');
    expect(cells.at(1).text()).toBe('19 8 * * *');
    expect(cells.at(2).text()).toBe('success');
    expect(cells.at(2).find('.badge-success').exists()).toBe(true);
    expect(cells.at(3).text()).toBe(time.formatDateTime(startedAt));
  });

  it('Should show one row per run with its status and totals', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });
    const rows = wrapper.findAll('.cron-run-row');

    expect(rows).toHaveLength(2);
    expect(rows.at(0).findAll('td').at(1).text()).toBe('update_ranks.py');
    expect(rows.at(0).find('.badge-success').text()).toBe('success');
    expect(rows.at(0).findAll('td').at(3).text()).toBe(
      time.formatDateTime(startedAt),
    );
    expect(rows.at(0).findAll('td').at(4).text()).toBe('0.19s');
    expect(rows.at(0).findAll('td').at(5).text()).toBe('5');
    expect(rows.at(1).find('.badge-danger').text()).toBe('failure');
  });

  it('Should show phase detail when a run is expanded', async () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    expect(wrapper.find('[data-cron-phases]').exists()).toBe(false);
    await wrapper.findAll('.cron-run-row').at(0).trigger('click');

    const phase = wrapper.findAll('[data-cron-phases] tbody tr td');
    expect(phase.at(0).text()).toBe('update_users_stats');
    expect(phase.at(1).text()).toBe('success');
    expect(phase.at(2).text()).toBe('0.05s');
  });

  it('Should collapse an expanded run when it is clicked again', async () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });
    const row = wrapper.findAll('.cron-run-row').at(0);

    await row.trigger('click');
    expect(wrapper.find('[data-cron-phases]').exists()).toBe(true);
    await row.trigger('click');
    expect(wrapper.find('[data-cron-phases]').exists()).toBe(false);
  });

  it('Should show the error output of a run that recorded no phases', async () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    await wrapper.findAll('.cron-run-row').at(1).trigger('click');

    const detail = wrapper.find('.cron-run-detail');
    expect(detail.find('.text-danger').text()).toBe('boom');
    expect(detail.find('[data-cron-phases]').exists()).toBe(false);
    expect(detail.text()).toContain(T.cronControlPlaneNoPhases);
  });

  it('Should badge a status it does not recognize', () => {
    const wrapper = mount(Crons, {
      propsData: { jobs, runs: [{ ...runs[0], status: 'queued' }] },
    });

    expect(wrapper.find('.cron-run-row .badge-light').text()).toBe('queued');
  });

  it('Should dash out the values a run did not record', () => {
    const wrapper = mount(Crons, {
      propsData: {
        jobs: [{ ...jobs[0], schedule: null }],
        runs: [
          {
            ...runs[0],
            started_at: null,
            duration_seconds: null,
            rows_affected: null,
          },
        ],
      },
    });

    expect(wrapper.findAll('[data-cron-jobs] tbody tr td').at(1).text()).toBe(
      '—',
    );
    const cells = wrapper.findAll('.cron-run-row td');
    expect(cells.at(3).text()).toBe('—');
    expect(cells.at(4).text()).toBe('—');
    expect(cells.at(5).text()).toBe('—');
  });

  it('Should keep zero as a real value instead of a dash', () => {
    const wrapper = mount(Crons, {
      propsData: {
        jobs,
        runs: [{ ...runs[0], duration_seconds: 0, rows_affected: 0 }],
      },
    });

    const cells = wrapper.findAll('.cron-run-row td');
    expect(cells.at(4).text()).toBe('0.00s');
    expect(cells.at(5).text()).toBe('0');
  });

  it('Should report no status for a job that has never run', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs: [] } });
    const cells = wrapper.findAll('[data-cron-jobs] tbody tr td');

    expect(cells.at(2).text()).toBe('—');
    expect(cells.at(3).text()).toBe('—');
  });

  it('Should take the newest run as the status of a job', () => {
    const wrapper = mount(Crons, {
      propsData: {
        jobs,
        runs: [
          { ...runs[0], run_id: 4, status: 'running' },
          { ...runs[0], run_id: 3, status: 'success' },
        ],
      },
    });

    expect(wrapper.findAll('[data-cron-jobs] tbody tr td').at(2).text()).toBe(
      'running',
    );
  });
});
