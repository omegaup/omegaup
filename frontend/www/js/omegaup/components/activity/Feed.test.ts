import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import activity_Feed from './Feed.vue';

describe('Feed.vue', () => {
  it('Should handle contest activity report', async () => {
    const wrapper = shallowMount(activity_Feed, {
      propsData: {
        alias: 'test_contest',
        type: 'contest',
        report: [
          {
            classname: 'user-rank-unranked',
            event: { name: 'open' },
            ip: 0,
            time: new Date(),
            username: 'user_1',
          },
          {
            classname: 'user-rank-unranked',
            event: { name: 'submit', problem: 'sumas' },
            ip: 0,
            time: new Date(),
            username: 'user_2',
          },
        ],
      },
    });

    expect(wrapper.text()).toContain(T.activityReportSummaryContest);
    expect(wrapper.text()).not.toContain(T.activityReportSummaryCourse);
  });

  it('Should handle course activity report', async () => {
    const wrapper = shallowMount(activity_Feed, {
      propsData: {
        alias: 'test_course',
        type: 'course',
        report: [
          {
            classname: 'user-rank-unranked',
            event: { name: 'open' },
            ip: 0,
            time: new Date(),
            username: 'user_3',
          },
          {
            classname: 'user-rank-unranked',
            event: { name: 'submit', problem: 'sumas' },
            ip: 0,
            time: new Date(),
            username: 'user_4',
          },
        ],
      },
    });

    expect(wrapper.text()).not.toContain(T.activityReportSummaryContest);
    expect(wrapper.text()).toContain(T.activityReportSummaryCourse);
  });
});
