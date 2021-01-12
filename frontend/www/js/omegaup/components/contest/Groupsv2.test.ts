import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import contest_Groups from './Groupsv2.vue';

describe('Groupsv2.vue', () => {
  it('Should handle empty groups', async () => {
    const wrapper = shallowMount(contest_Groups, {
      propsData: {
        groups: [],
      },
    });

    expect(wrapper.text()).toContain(T.wordsGroup);
  });
});
