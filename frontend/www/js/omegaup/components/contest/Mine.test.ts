import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import contest_Mine from './Mine.vue';

describe('Mine.vue', () => {
  it('Should handle empty list of contests', async () => {
    const wrapper = shallowMount(contest_Mine, {
      propsData: {
        contests: [],
        privateContestsAlert: false,
      },
    });

    expect(wrapper.text()).toContain(T.wordsMyContests);
  });
});
