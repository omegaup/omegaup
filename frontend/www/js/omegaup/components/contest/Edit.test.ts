import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import contest_Edit from './Edit.vue';

describe('Edit.vue', () => {
  it('Should work with empty information', async () => {
    const wrapper = shallowMount(contest_Edit, {
      propsData: {
        admins: [],
        details: {},
        groupAdmins: [],
        groups: [],
        problems: [],
        requests: [],
        users: [],
      },
    });

    expect(wrapper.text()).toContain(T.contestDetailsGoToContest);
  });
});
