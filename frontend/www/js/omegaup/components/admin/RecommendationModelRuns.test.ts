import { mount } from '@vue/test-utils';
import T from '../../lang';
import * as time from '../../time';

import RecommendationModelRuns from './RecommendationModelRuns.vue';
import { types } from '../../api_types';

const createdAt = new Date(Date.UTC(2026, 0, 2, 3, 4, 5));

const modelRuns: types.RecommendationModelRun[] = [
  {
    model_run_id: 2,
    created_at: createdAt,
    map_score: 0.3419,
    dataset_size: 12345,
    rng_seed: 42,
    published: true,
  },
  {
    model_run_id: 1,
    created_at: createdAt,
    map_score: 0.0312,
    dataset_size: 12345,
    published: false,
    skip_reason: 'MAP score 0.0312 below minimum 0.0500',
  },
];

const asPercent = (score: number): string =>
  score.toLocaleString(T.locale, {
    style: 'percent',
    maximumFractionDigits: 1,
  });

describe('RecommendationModelRuns.vue', () => {
  it('Should show one row per training run with its score and dataset size', () => {
    const wrapper = mount(RecommendationModelRuns, {
      propsData: { modelRuns },
    });
    const rows = wrapper.findAll('[data-cron-model-runs] tbody tr');

    expect(rows).toHaveLength(2);
    const cells = rows.at(0).findAll('td');
    expect(cells.at(0).text()).toBe(time.formatDateTime(createdAt));
    expect(cells.at(1).find('code').text()).toBe('0.3419');
    expect(cells.at(1).find('small').text()).toBe(asPercent(0.3419));
    expect(cells.at(2).text()).toBe((12345).toLocaleString(T.locale));
    expect(cells.at(2).attributes('title')).toBe('12345');
    expect(cells.at(4).text()).toBe(T.cronControlPlaneModelPublishedYes);
    expect(cells.at(4).find('.badge-success').exists()).toBe(true);
    expect(cells.at(5).text()).toBe('—');
  });

  it('Should show why a model was held back, as the job recorded it', () => {
    const wrapper = mount(RecommendationModelRuns, {
      propsData: { modelRuns },
    });
    const cells = wrapper
      .findAll('[data-cron-model-runs] tbody tr')
      .at(1)
      .findAll('td');

    expect(cells.at(4).text()).toBe(T.cronControlPlaneModelPublishedNo);
    expect(cells.at(4).find('.badge-secondary').exists()).toBe(true);
    expect(cells.at(5).text()).toBe('MAP score 0.0312 below minimum 0.0500');
  });

  it('Should show the seed a run fixed, and say so when it fixed none', () => {
    const wrapper = mount(RecommendationModelRuns, {
      propsData: { modelRuns },
    });
    const rows = wrapper.findAll('[data-cron-model-runs] tbody tr');

    expect(rows.at(0).findAll('td').at(3).text()).toBe('42');
    expect(rows.at(1).findAll('td').at(3).text()).toBe(
      T.cronControlPlaneModelSeedUnset,
    );
  });

  it('Should show a placeholder when no model has been trained yet', () => {
    const wrapper = mount(RecommendationModelRuns);

    expect(wrapper.find('[data-cron-model-runs]').exists()).toBe(false);
    expect(wrapper.text()).toContain(T.cronControlPlaneModelNoRuns);
  });
});
