import { shallowMount } from '@vue/test-utils';

import { types } from '../../api_types';

import group_Scoreboards from './Scoreboards.vue';

describe('Scoreboards.vue', () => {
  it('Should handle edit scoreboard view with one scoreboard', () => {
    const wrapper = shallowMount(group_Scoreboards, {
      propsData: {
        groupAlias: 'Hello',
        scoreboards: [
          {
            alias: 'hello',
            create_time: '',
            description: 'Hello omegaUp',
            name: 'Hello omegaUp',
          },
        ] as types.GroupScoreboard[],
      },
    });

    expect(wrapper.find('tbody').text()).toBe('Hello omegaUp');
  });
});
