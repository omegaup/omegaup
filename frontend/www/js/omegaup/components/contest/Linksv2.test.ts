import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import contest_Links from './Linksv2.vue';

describe('Linksv2.vue', () => {
  it('Should display the links', async () => {
    const wrapper = shallowMount(contest_Links, {
      propsData: {
        data: {
          admission_mode: 'registration',
          alias: 'testalias',
          description: 'Test contest description',
        },
      },
    });

    expect(wrapper.text()).toContain(T.wordsSubmissions);
    expect(wrapper.text()).toContain(T.profileStatistics);
    expect(wrapper.text()).toContain(T.activityReport);
    expect(wrapper.text()).toContain(T.contestPrintableVersion);
    expect(wrapper.text()).toContain(T.contestScoreboardLink);
    expect(wrapper.text()).toContain(T.contestScoreboardAdminLink);
  });
});
