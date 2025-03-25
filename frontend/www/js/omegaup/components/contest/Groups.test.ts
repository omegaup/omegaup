import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import contest_Groups from './Groups.vue';

describe('Groups.vue', () => {
  it('Should handle empty groups', async () => {
    const wrapper = shallowMount(contest_Groups, {
      propsData: {
        groups: [],
      },
    });

    expect(wrapper.text()).toContain(T.wordsGroup);
  });
});
