import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import common_Requests from './Requests.vue';

describe('Publish.vue', () => {
  it('Should handle initital props', async () => {
    const wrapper = shallowMount(common_Requests, {
      propsData: {
        data: [
          {
            accepted: false,
            admin: {
              username: 'test_user',
            },
            country: null,
            last_update: new Date(),
            request_time: new Date(),
            username: 'test_user',
          },
        ],
        textAddParticipant: T.contestAdduserAddContestant,
      },
    });

    expect(wrapper.text()).toContain(T.contestAdduserAddContestant);
  });
});
