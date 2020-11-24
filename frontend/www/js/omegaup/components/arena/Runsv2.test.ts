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
    const expectedDateProblem1 = '1/1/2020, 12:20:00 AM';
    const expectedDateProblem2 = '1/1/2020, 12:10:00 AM';
    const expectedDateProblem3 = '1/1/2020, 12:05:00 AM';
    const expectedDateProblem4 = '1/1/2020, 12:00:00 AM';
    const expectedDateProblem5 = '1/1/2020, 12:15:00 AM';
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
            guid: '122000',
            language: 'java',
            memory: 1933312,
            penalty: 0,
            run_id: 227,
            runtime: 316,
            score: 0,
            status: 'ready',
            submit_delay: 0,
            time: new Date(expectedDateProblem1),
            type: 'normal',
            username: 'username',
            verdict: 'WA',
          },
          {
            alias: 'alias',
            classname: '',
            contest_score: 0,
            country: 'xx',
            guid: '121000',
            language: 'java',
            memory: 1933312,
            penalty: 0,
            run_id: 228,
            runtime: 316,
            score: 100,
            status: 'ready',
            submit_delay: 0,
            time: new Date(expectedDateProblem2),
            type: 'normal',
            username: 'username',
            verdict: 'AC',
          },
          {
            alias: 'alias',
            classname: '',
            contest_score: 0,
            country: 'xx',
            guid: '120500',
            language: 'java',
            memory: 1933312,
            penalty: 0,
            run_id: 229,
            runtime: 316,
            score: 100,
            status: 'ready',
            submit_delay: 0,
            time: new Date(expectedDateProblem3),
            type: 'normal',
            username: 'username',
            verdict: 'AC',
          },
          {
            alias: 'alias',
            classname: '',
            contest_score: 0,
            country: 'xx',
            guid: '120000',
            language: 'java',
            memory: 1933312,
            penalty: 0,
            run_id: 230,
            runtime: 316,
            score: 100,
            status: 'ready',
            submit_delay: 0,
            time: new Date(expectedDateProblem4),
            type: 'normal',
            username: 'username',
            verdict: 'AC',
          },
          {
            alias: 'alias',
            classname: '',
            contest_score: 0,
            country: 'xx',
            guid: '121500',
            language: 'java',
            memory: 1933312,
            penalty: 0,
            run_id: 231,
            runtime: 316,
            score: 100,
            status: 'ready',
            submit_delay: 0,
            time: new Date(expectedDateProblem5),
            type: 'normal',
            username: 'username',
            verdict: 'AC',
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
      wrapper.find('tbody').findAll('td[name="guid"]').at(0).text(),
    ).toContain('122000');
    expect(
      wrapper.find('tbody').findAll('td[name="guid"]').at(1).text(),
    ).toContain('121500');
    expect(
      wrapper.find('tbody').findAll('td[name="guid"]').at(2).text(),
    ).toContain('121000');
    expect(
      wrapper.find('tbody').findAll('td[name="guid"]').at(3).text(),
    ).toContain('120500');
    expect(
      wrapper.find('tbody').findAll('td[name="guid"]').at(4).text(),
    ).toContain('120000');
  });
});
