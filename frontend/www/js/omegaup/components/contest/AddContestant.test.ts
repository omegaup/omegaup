import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import contest_AddContestant from './AddContestant.vue';

describe('AddContestant.vue', () => {
  it('Should handle empty user list', async () => {
    const wrapper = shallowMount(contest_AddContestant, {
      propsData: {
        contest: {
          admission_mode: 'public',
          alias: 'Test contest',
          window_length: 20,
        },
        initialUsers: [],
      },
    });

    expect(wrapper.text()).toContain(T.addUsersMultipleOrSingleUser);
  });
});
