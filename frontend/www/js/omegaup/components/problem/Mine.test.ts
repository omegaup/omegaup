import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import common_EmptyState from '../common/EmptyState.vue';

import problem_Mine from './Mine.vue';

describe('Mine.vue', () => {
  it('Should handle empty list of problems', async () => {
    const wrapper = shallowMount(problem_Mine, {
      propsData: {
        isSysadmin: false,
        problems: [],
        pagerItems: [
          {
            class: 'disabled',
            label: '1',
            page: 1,
          },
        ],
        privateProblemsAlert: false,
      },
    });

    expect(wrapper.text()).toContain(T.myproblemsListMyProblems);
    expect(wrapper.findComponent(common_EmptyState).exists()).toBe(true);
    expect(wrapper.find('table').exists()).toBe(false);
    expect(wrapper.find('input.typeahead').exists()).toBe(false);
    expect(wrapper.find('[data-visibility-action]').exists()).toBe(false);
  });

  it('Should render controls and table when problems exist', async () => {
    const wrapper = shallowMount(problem_Mine, {
      propsData: {
        isSysadmin: false,
        problems: [
          {
            alias: 'test-problem',
            title: 'Test Problem',
            quality_seal: false,
            visibility: 1,
            tags: [],
            problem_id: 1,
          },
        ],
        pagerItems: [
          {
            class: 'disabled',
            label: '1',
            page: 1,
          },
        ],
        privateProblemsAlert: false,
        visibilityStatuses: {
          public: 1,
          private: 0,
        },
      },
    });

    expect(wrapper.text()).toContain(T.myproblemsListMyProblems);
    expect(wrapper.findComponent(common_EmptyState).exists()).toBe(false);
    expect(wrapper.find('table').exists()).toBe(true);
    expect(wrapper.find('input.typeahead').exists()).toBe(true);
    expect(wrapper.find('[data-visibility-action]').exists()).toBe(true);
  });
});
