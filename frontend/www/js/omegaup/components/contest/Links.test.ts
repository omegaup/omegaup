import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import contest_Links from './Links.vue';

describe('Links.vue', () => {
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
    expect(wrapper.text()).toContain(T.contestScoreboardDownloadCsvFile);
  });
});
