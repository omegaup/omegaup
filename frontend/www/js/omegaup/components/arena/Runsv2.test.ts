import { mount, createLocalVue } from '@vue/test-utils';
import { types } from '../../api_types';
import T from '../../lang';
import arena_Runs from './Runsv2.vue';

import BootstrapVue, { BTable } from 'bootstrap-vue';
const localVue = createLocalVue();
localVue.use(BootstrapVue);

describe('Runsv2.vue', () => {
  const baseRunData: types.Run = {
    alias: 'alias',
    classname: '',
    contest_score: 0,
    country: 'xx',
    language: 'java',
    memory: 1933312,
    penalty: 0,
    runtime: 316,
    score: 0,
    status: 'ready',
    submit_delay: 0,
    type: 'normal',
    username: 'username',
    verdict: 'AC',
    guid: '119555',
    time: new Date('1/1/2020, 12:30:00 AM'),
  };

  const runs: types.Run[] = [
    {
      ...baseRunData,
      guid: '122000',
      time: new Date('1/1/2020, 12:20:00 AM'),
    },
    {
      ...baseRunData,
      guid: '121000',
      username: 'other_username',
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
      username: 'other_username',
      time: new Date('1/1/2020, 12:00:00 AM'),
    },
    {
      ...baseRunData,
      guid: '121500',
      time: new Date('1/1/2020, 12:15:00 AM'),
    },
  ];

  it('Should handle runs', () => {
    const wrapper = mount(arena_Runs, {
      propsData: {
        runs,
        problemAlias: 'test-problem-1',
      },
      localVue,
    });

    expect(wrapper.find('h5').text()).toBe(T.wordsSubmissions);

    const tableComponent = wrapper.findComponent(BTable);
    expect(tableComponent.exists()).toBe(true);
    expect(tableComponent.findAll('table tbody tr').length).toBe(runs.length);
    expect(tableComponent.find('td.table-success').exists()).toBe(true);
    for (const run of runs) {
      expect(tableComponent.text()).toContain(run.guid);
      expect(tableComponent.text()).toContain(
        `${(run.score * 100).toFixed(2)}%`,
      );
    }
  });
});
