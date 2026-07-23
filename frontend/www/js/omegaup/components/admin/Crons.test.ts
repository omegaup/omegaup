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

const recommendationModelRuns: types.RecommendationModelRun[] = [
  {
    created_at: new Date(),
    map_score: 0.3419,
    dataset_size: 700,
    published: true,
  },
  {
    created_at: new Date(),
    map_score: 0.2151,
    dataset_size: 700,
    published: false,
    skip_reason: 'MAP score 0.2151 below minimum 0.3000',
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

  it('Should render the recommendation model quality table', () => {
    const wrapper = mount(Crons, {
      propsData: { jobs, runs, recommendationModelRuns },
    });

    expect(wrapper.find('[data-cron-model-runs]').exists()).toBe(true);
    const text = wrapper.find('[data-cron-model-runs]').text();
    expect(text).toContain('0.3419');
    expect(text).toContain('700');
    expect(text).toContain('MAP score 0.2151 below minimum 0.3000');
  });

  it('Should show a placeholder when no model runs were recorded', () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    expect(wrapper.find('[data-cron-model-runs]').exists()).toBe(false);
    expect(wrapper.text()).toContain(T.cronControlPlaneModelNoRuns);
  });

  it('Should emit rerun with the job name when the button is clicked', async () => {
    const wrapper = mount(Crons, { propsData: { jobs, runs } });

    await wrapper.find('[data-cron-rerun]').trigger('click');

    const emitted = wrapper.emitted('rerun');
    expect(emitted).toBeTruthy();
    expect(emitted?.[0]).toEqual(['update_ranks.py']);
  });
});
