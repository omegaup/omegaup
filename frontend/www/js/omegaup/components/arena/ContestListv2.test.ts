jest.mock('../../../../third_party/js/diff_match_patch.js');

import { mount } from '@vue/test-utils';
import type { types } from '../../api_types';

import arena_ContestList from './ContestListv2.vue';

describe('ContestListv2.vue', () => {
  const contests: types.ContestListItem[] = [
    {
      admission_mode: 'public',
      alias: 'Contest-1',
      description: 'hello contest 1',
      contest_id: 1,
      finish_time: new Date(`2021-04-30 15:00:00`),
      last_updated: new Date(`2021-04-30 10:00:00`),
      original_finish_time: new Date(`2021-04-30 15:00:00`),
      problemset_id: 1,
      recommended: false,
      rerun_id: 0,
      start_time: new Date(`2021-04-30 12:00:00`),
      title: 'Contest 1',
      window_length: 300,
    },
  ];

  it('Should show the contest list', async () => {
    const wrapper = mount(arena_ContestList, {
      propsData: {
        contests,
      },
    });

    const span = wrapper.find('span');
    expect(span.exists()).toBe(true);

    /*
			Both .parse() are required to compare between objects instead of texts, due to the difference in the JSON format between JavaScript and Vue. 
		*/
    expect(JSON.parse(span.text())).toEqual(
      JSON.parse(JSON.stringify(contests)),
    );
  });
});
