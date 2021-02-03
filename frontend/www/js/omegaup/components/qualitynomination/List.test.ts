import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import T from '../../lang';
import qualitynomination_List from './List.vue';
import type { types } from '../../api_types';

describe('List.vue', () => {
  it('Should handle list of nominations', async () => {
    const wrapper = shallowMount(qualitynomination_List, {
      propsData: {
        nominations: [
          {
            author: {
              name: 'nombre',
              username: 'user',
            },
            contents: {
              original: '',
              rationale: 'N/A',
              reason: 'poorly-described',
            },
            nomination: 'demotion',
            nominator: {
              name: 'nominador',
              username: 'user_nominator',
            },
            problem: {
              alias: 'problem',
              title: 'problem',
            },
            qualitynomination_id: 1,
            status: 'open',
            time: new Date('2020-06-03 23:46:10'),
            votes: [],
          } as types.NominationListItem,
        ] as types.NominationListItem[],
        pagerItems: [
          {
            class: 'disabled',
            label: '1',
            page: 1,
          },
        ],
        pages: 1,
        length: 100,
        isAdmin: true,
        myView: false,
      },
    });

    expect(wrapper.find('[data-name="reason"]').text()).toContain(
      T.wordsReason,
    );
  });

  it('Should handle my list of nominations', async () => {
    const wrapper = shallowMount(qualitynomination_List, {
      propsData: {
        nominations: [
          {
            author: {
              name: 'nombre',
              username: 'user',
            },
            contents: {
              original: '',
              rationale: 'N/A',
              reason: 'poorly-described',
            },
            nomination: 'demotion',
            nominator: {
              name: 'nominador',
              username: 'user_nominator',
            },
            problem: {
              alias: 'problem',
              title: 'problem',
            },
            qualitynomination_id: 1,
            status: 'open',
            time: new Date('2020-06-03 23:46:10'),
            votes: [],
          },
        ],
        pagerItems: [
          {
            class: 'disabled',
            label: '1',
            page: 1,
          },
        ],
        pages: 1,
        length: 100,
        isAdmin: false,
        myView: true,
      },
    });

    expect(wrapper.find('[data-name="reason"]').exists()).toBe(false);
  });
});
