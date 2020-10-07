import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';
import { types } from '../../api_types';

import arena_RunDetails from './RunDetails.vue';

describe('RunDetails.vue', () => {
  it('Should handle run details', () => {
    const wrapper = shallowMount(arena_RunDetails, {
      propsData: {
        data: <types.RunDetails>{
          admin: false,
          alias: 'sumas',
          cases: {},
          details: {
            compile_meta: {
              Main: {
                memory: 12091392,
                sys_time: 0.029124,
                time: 0.174746,
                verdict: 'OK',
                wall_time: 0.51659,
              },
            },
            contest_score: 5,
            groups: [],
            judged_by: 'localhost',
            max_score: 100,
            memory: 10407936,
            score: 0.05,
            time: 0.31891,
            verdict: 'PA',
            wall_time: 0.699709,
          },
          feedback: 'none',
          groups: [],
          guid: '80bbe93bc01c1d47ff9fb396dfaff741',
          judged_by: '',
          language: 'py3',
          logs: '',
          show_diff: 'none',
          source: 'print(3)',
          source_link: false,
          source_name: 'Main.py3',
          source_url: 'blob:http://localhost:8001/url',
        },
      },
    });

    expect(wrapper.text()).toContain(T.wordsSource);
    expect(wrapper.text()).toContain(T.wordsDownload);
  });
});
