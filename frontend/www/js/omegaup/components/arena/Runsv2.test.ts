import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import arena_Runsv2 from './Runsv2.vue';

describe('Runsv2.vue', () => {
  it('Should handle empty runs', () => {
    const wrapper = shallowMount(arena_Runsv2, {
      propsData: {
        contestAlias: 'admin',
        globalRuns: true,
        runs: [],
      },
    });

    expect(wrapper.find('.card-header').text()).toBe(T.wordsGlobalSubmissions);
    expect(wrapper.find('table tbody').text()).toBe('');
  });

  it('Should handle runs', async () => {
    const expectedDate = '1/1/2020, 12:00:00 AM';
    const wrapper = shallowMount(arena_Runsv2, {
      propsData: {
        contestAlias: 'admin',
        globalRuns: true,
        runs: [
          {
            alias: 'alias',
            classname: '',
            contest_score: 0,
            country: 'xx',
            guid: '1234',
            language: 'java',
            memory: 1933312,
            penalty: 0,
            run_id: 227,
            runtime: 316,
            score: 0,
            status: 'ready',
            submit_delay: 0,
            time: new Date(expectedDate),
            type: 'normal',
            username: 'username',
            verdict: 'WA',
          },
        ],
        showContest: true,
        showDetails: true,
        showDisqualify: true,
        showPager: true,
        showPoints: false,
        showProblem: true,
        showRejudge: true,
        showUser: true,
        username: null,
      },
    });
    const selectedRun = wrapper.find('td button[data-toggle=popover]');

    expect(selectedRun.attributes('data-content')).toContain(T.verdictWA);
    expect(selectedRun.attributes('data-content')).toContain(T.verdictHelpWA);
  });

  it('Should handle order runs', async () => {
    const baseRunData = {
      alias: 'alias',
      classname: '',
      contest_score: 0,
      country: 'xx',
      language: 'java',
      memory: 1933312,
      penalty: 0,
      run_id: 227,
      runtime: 316,
      score: 0,
      status: 'ready',
      submit_delay: 0,
      type: 'normal',
      username: 'username',
      verdict: 'WA',
    };
    const wrapper = shallowMount(arena_Runsv2, {
      propsData: {
        contestAlias: 'admin',
        globalRuns: true,
        runs: [
          {
            ...baseRunData,
            guid: '122000',
            time: new Date('1/1/2020, 12:20:00 AM'),
          },
          {
            ...baseRunData,
            guid: '121000',
            time: new Date('1/1/2020, 12:10:00 AM'),
          },
          {
            ...baseRunData,
            guid: '120500',
            time: new Date('1/1/2020, 12:05:00 AM'),
          },
          {
            ...baseRunData,
            guid: '120000',
            time: new Date('1/1/2020, 12:00:00 AM'),
          },
          {
            ...baseRunData,
            guid: '121500',
            time: new Date('1/1/2020, 12:15:00 AM'),
          },
        ],
        showContest: true,
        showDetails: true,
        showDisqualify: true,
        showPager: true,
        showPoints: false,
        showProblem: true,
        showRejudge: true,
        showUser: true,
        username: null,
      },
    });
    expect(
      wrapper.findAll('acronym[data-run-guid]').wrappers.map((e) => e.text()),
    ).toEqual(['122000', '121500', '121000', '120500', '120000']);
  });
});
