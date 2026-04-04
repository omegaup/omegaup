import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

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
  });
});
