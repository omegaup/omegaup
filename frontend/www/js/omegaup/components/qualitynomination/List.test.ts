import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import * as UI from '../../ui';

import list from './List.vue';
import { Contest } from '../../api';

describe('List.vue', () => {
  it('Should handle list of one nomination', async () => {
    const wrapper = shallowMount(list, {
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
        isAdmin: true,
        myView: false,
      },
    });

    expect(wrapper.findComponent({ name: 'title' }).text()).toContain(
      UI.formatString(T.nominationsRangeHeader, {
        lowCount: 1,
        highCount: 100,
      }),
    );
  });
});
