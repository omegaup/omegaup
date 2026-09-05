import { mount } from '@vue/test-utils';
import T from '../../lang';
import * as time from '../../time';
import * as ui from '../../ui';

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

const timeAt = (hour: number, minute: number): string =>
  new Date(2024, 0, 1, hour, minute).toLocaleTimeString(T.locale, {
    hour: '2-digit',
    minute: '2-digit',
  });

const weekdayName = (dayOfWeek: number): string =>
  new Date(Date.UTC(2024, 0, 7 + dayOfWeek)).toLocaleDateString(T.locale, {
    weekday: 'long',
    timeZone: 'UTC',
  });

const describedSchedule = (schedule: string | null): string | null => {
  const wrapper = mount(Crons, {
    propsData: { jobs: [{ ...jobs[0], schedule }], runs: [] },
  });
  const cell = wrapper.findAll('[data-cron-jobs] tbody tr td').at(1);
  return cell.find('small').exists() ? cell.find('small').text() : null;
};

describe('Crons.vue', () => {
  it('Should show each job with the status of its latest run', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });
    const cells = wrapper.findAll('[data-cron-jobs] tbody tr td');

    expect(cells.at(0).text()).toContain('Update rankings');
    expect(cells.at(1).find('code').text()).toBe('19 8 * * *');
    expect(cells.at(1).text()).toContain('08:19');
    expect(cells.at(2).text()).toBe('success');
    expect(cells.at(2).find('.badge-success').exists()).toBe(true);
    expect(cells.at(3).find('span').attributes('title')).toBe(
      time.formatDateTime(startedAt),
    );
  });

  it('Should show one row per run with its status and totals', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });
    const rows = wrapper.findAll('.cron-run-row');

    expect(rows).toHaveLength(2);
    expect(rows.at(0).findAll('td').at(1).text()).toContain('update_ranks.py');
    expect(rows.at(0).findAll('td').at(1).text()).toContain('Update rankings');
    expect(rows.at(0).find('.badge-success').text()).toBe('success');
    expect(
      rows.at(0).findAll('td').at(3).find('span').attributes('title'),
    ).toBe(time.formatDateTime(startedAt));
    expect(rows.at(0).findAll('td').at(4).text()).toBe('0.19s');
    expect(rows.at(0).findAll('td').at(5).text()).toBe('5');
    expect(rows.at(1).find('.badge-danger').text()).toBe('failure');
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
    await wrapper.findAll('.cron-run-row').at(0).trigger('click');

    const phase = wrapper.findAll('[data-cron-phases] tbody tr td');
    expect(phase.at(0).find('code').text()).toBe('update_users_stats');
    expect(phase.at(0).text()).toContain('Update users stats');
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

  it('Should describe a daily schedule next to the expression', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });
    const cell = wrapper.findAll('[data-cron-jobs] tbody tr td').at(1);

    expect(cell.find('code').text()).toBe('19 8 * * *');
    expect(cell.find('small').text()).toBe(
      ui.formatString(T.cronControlPlaneScheduleDaily, { time: timeAt(8, 19) }),
    );
  });

  it('Should describe weekly, monthly, hourly and interval schedules', () => {
    expect(describedSchedule('30 4 * * 0')).toBe(
      ui.formatString(T.cronControlPlaneScheduleWeekly, {
        weekday: weekdayName(0),
        time: timeAt(4, 30),
      }),
    );
    expect(describedSchedule('0 5 1 * *')).toBe(
      ui.formatString(T.cronControlPlaneScheduleMonthly, {
        dayOfMonth: '1',
        time: timeAt(5, 0),
      }),
    );
    expect(describedSchedule('7 * * * *')).toBe(
      ui.formatString(T.cronControlPlaneScheduleHourly, { minute: '07' }),
    );
    expect(describedSchedule('*/15 * * * *')).toBe(
      ui.formatString(T.cronControlPlaneScheduleEveryMinutes, {
        minutes: '15',
      }),
    );
  });

  it('Should read day 7 as Sunday, the same as day 0', () => {
    expect(describedSchedule('30 4 * * 7')).toBe(
      describedSchedule('30 4 * * 0'),
    );
  });

  it('Should show only the expression for a schedule it cannot describe', () => {
    expect(describedSchedule('19 8 * * 1-5')).toBeNull();
    expect(describedSchedule('19 8 * 3 *')).toBeNull();
    expect(describedSchedule('19 8 * *')).toBeNull();
    expect(describedSchedule('99 8 * * *')).toBeNull();
    expect(describedSchedule('19 44 * * *')).toBeNull();
    expect(describedSchedule('@daily')).toBeNull();
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

  it('Should show the script name next to a readable one', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    const cell = wrapper.find('[data-cron-jobs] tbody tr td');
    expect(cell.find('code').text()).toBe('update_ranks.py');
    expect(cell.find('small').text()).toBe('Update rankings');
  });

  it('Should show an empty state when there are no runs', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs: [] } });

    expect(wrapper.text()).toContain(T.cronControlPlaneNoRuns);
  });

  it('Should emit set-enabled when the switch is toggled', async () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    const toggle = wrapper.find('[data-cron-enabled]');
    expect((toggle.element as HTMLInputElement).checked).toBe(true);
    await toggle.setChecked(false);

    const emitted = wrapper.emitted('set-enabled');
    expect(emitted).toBeTruthy();
    expect(emitted?.[0]).toEqual([{ name: 'update_ranks.py', enabled: false }]);
  });

  it('Should leave the switch showing the job until the server agrees', async () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    const toggle = wrapper.find('[data-cron-enabled]');
    await toggle.setChecked(false);

    // The click asked for false, but jobs still says true, so it stays true.
    expect((toggle.element as HTMLInputElement).checked).toBe(true);
    expect(wrapper.emitted('set-enabled')?.[0]).toEqual([
      { name: 'update_ranks.py', enabled: false },
    ]);
  });

  it('Should emit rerun with the job name when the button is clicked', async () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    await wrapper.find('[data-cron-rerun]').trigger('click');

    const emitted = wrapper.emitted('rerun');
    expect(emitted).toBeTruthy();
    expect(emitted?.[0]).toEqual(['update_ranks.py']);
  });
});
