import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import T from '../../lang';

import activity_Feed from './Feed.vue';

const Feed = activity_Feed as ComponentOptions<Vue>;

describe('Feed.vue', () => {
  it('Should handle contest activity report', async () => {
    const wrapper = shallowMount(Feed, {
      propsData: {
        page: 1,
        length: 100,
        alias: 'test_contest',
        type: 'contest',
        pagerItems: [],
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
    const wrapper = shallowMount(Feed, {
      propsData: {
        page: 1,
        length: 100,
        alias: 'test_course',
        type: 'course',
        pagerItems: [],
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
