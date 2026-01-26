jest.mock('../../../../third_party/js/diff_match_patch.js');

import { mount, shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';

import T from '../../lang';

import arena_Runs, { DisqualificationType } from './Runs.vue';

describe('Runs.vue', () => {
  it('Should handle empty runs', () => {
    const wrapper = shallowMount(arena_Runs, {
      propsData: {
        contestAlias: 'admin',
        runs: [],
      },
    });

    expect(wrapper.find('.card-header').text()).toBe(T.wordsGlobalSubmissions);
    expect(wrapper.find('table tbody').text()).toBe('');
  });

  it('Should handle runs', async () => {
    const expectedDate = '1/1/2020, 12:00:00 AM';
    const wrapper = shallowMount(arena_Runs, {
      propsData: {
        contestAlias: 'admin',
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

  const baseRunData: types.Run = {
    alias: 'alias',
    classname: '',
    contest_score: 0,
    country: 'xx',
    execution: 'EXECUTION_FINISHED',
    language: 'java',
    memory: 1933312,
    output: 'OUTPUT_INCORRECT',
    penalty: 0,
    runtime: 316,
    score: 0,
    status: 'ready',
    status_memory: 'MEMORY_AVAILABLE',
    status_runtime: 'RUNTIME_AVAILABLE',
    submit_delay: 0,
    type: 'normal',
    username: 'username',
    verdict: 'WA',
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

  it('Should handle order runs', async () => {
    const wrapper = shallowMount(arena_Runs, {
      propsData: {
        contestAlias: 'admin',
        runs,
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

  const filtersMapping: { filter: string; value: string }[] = [
    { filter: 'verdict', value: 'AC' },
    { filter: 'status', value: 'new' },
    { filter: 'language', value: 'py3' },
  ];

  describe.each(filtersMapping)(`A filter:`, (filter) => {
    it(`whose name is ${filter.filter} should have gotten the value ${filter.value}`, async () => {
      const wrapper = shallowMount(arena_Runs, {
        propsData: {
          contestAlias: 'admin',
          runs,
          showPager: true,
        },
      });
      await wrapper
        .find(`select[data-select-${filter.filter}]`)
        .find(`option[value="${filter.value}"]`)
        .setSelected();

      expect(wrapper.emitted('filter-changed')).toEqual([[filter]]);
    });
  });

  it('Should handle username filter', async () => {
    const wrapper = shallowMount(arena_Runs, {
      propsData: {
        contestAlias: 'contest',
        runs,
        showPager: true,
        showUser: true,
      },
    });

    await wrapper.setData({
      filterUsername: { key: 'other_username', value: 'other username' },
    });
    expect(wrapper.emitted('filter-changed')).toEqual([
      [{ filter: 'username', value: 'other_username' }],
    ]);
  });

  it('Should handle problem filter', async () => {
    const wrapper = shallowMount(arena_Runs, {
      propsData: {
        contestAlias: 'contest',
        runs,
        showPager: true,
        showProblem: true,
      },
    });

    await wrapper.setData({
      filterProblem: { key: 'other_problem', value: 'other problem' },
    });
    expect(wrapper.emitted('filter-changed')).toEqual([
      [{ filter: 'problem', value: 'other_problem' }],
    ]);
  });

  it('Should handle the new submission button', async () => {
    const wrapper = shallowMount(arena_Runs, {
      propsData: {
        problemAlias: 'alias',
        runs,
        showDetails: true,
        isContestFinished: false,
        useNewSubmissionButton: true,
      },
    });

    expect(wrapper.find('tfoot button').text()).toBe(T.wordsNewSubmissions);
  });

  it('Should handle diqualify by guid button for run actions', async () => {
    runs.push({
      ...baseRunData,
      guid: '122600',
      time: new Date('1/3/2020, 12:25:00 AM'),
      type: 'disqualified',
    });
    const wrapper = shallowMount(arena_Runs, {
      propsData: {
        contestAlias: 'admin',
        problemAlias: 'alias',
        runs,
        showDetails: true,
        showDisqualify: true,
        showRejudge: true,
        isContestFinished: false,
        useNewSubmissionButton: true,
        inContest: true,
      },
    });
    expect(wrapper.find('[data-actions="120000"]').text()).toContain(
      T.arenaRunsActionsDisqualifyByGUID,
    );
    expect(wrapper.find('[data-actions="120000"]').text()).not.toContain(
      T.arenaRunsActionsRequalify,
    );
    await wrapper.find('[data-actions="120000"]').trigger('click');
    await wrapper
      .find('[data-actions-disqualify-by-guid="120000"]')
      .trigger('click');
    expect(wrapper.emitted('disqualify')).toEqual([
      [
        {
          disqualificationType: DisqualificationType.ByGUID,
          run: {
            ...baseRunData,
            guid: '120000',
            username: 'other_username',
            time: new Date('1/1/2020, 12:00:00 AM'),
          },
        },
      ],
    ]);

    expect(wrapper.find('[data-actions="122600"]').text()).not.toContain(
      T.arenaRunsActionsDisqualifyByGUID,
    );
    expect(wrapper.find('[data-actions="122600"]').text()).toContain(
      T.arenaRunsActionsRequalify,
    );
    await wrapper.find('[data-actions="122600"]').trigger('click');
    await wrapper.find('[data-actions-requalify="122600"]').trigger('click');
    expect(wrapper.emitted('requalify')).toEqual([
      [
        {
          ...baseRunData,
          guid: '122600',
          time: new Date('1/3/2020, 12:25:00 AM'),
          type: 'disqualified',
        },
      ],
    ]);
  });

  it('Should handle disqualify in batch buttons for run actions', async () => {
    const wrapper = shallowMount(arena_Runs, {
      propsData: {
        contestAlias: 'admin',
        problemAlias: 'alias',
        runs,
        showDetails: true,
        showDisqualify: true,
        showRejudge: true,
        isContestFinished: false,
        useNewSubmissionButton: true,
        inContest: true,
      },
    });
    await wrapper
      .find('[data-actions-disqualify-by-user="121500"]')
      .trigger('click');
    expect(wrapper.emitted('disqualify')).toEqual([
      [
        {
          disqualificationType: DisqualificationType.ByUser,
          run: {
            ...baseRunData,
            guid: '121500',
            username: 'username',
            time: new Date('1/1/2020, 12:15:00 AM'),
          },
        },
      ],
    ]);
  });

  it('Should handle filterUsername when username changes', async () => {
    const wrapper = shallowMount(arena_Runs, {
      propsData: {
        contestAlias: 'admin',
        runs,
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

    await wrapper.setProps({ username: 'username' });
    expect(wrapper.vm.filterUsername?.key).toBe('username');

    await wrapper.setProps({ username: null });
    expect(wrapper.vm.filterUsername).toBeFalsy();
  });

  const usernamesToBeFiltered = ['username', 'other_username'];
  describe.each(usernamesToBeFiltered)(`A user:`, (username) => {
    it(`whose username is ${username} should be filtered when they are selected.`, async () => {
      const wrapper = mount(arena_Runs, {
        propsData: {
          contestAlias: 'admin',
          runs,
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

      expect(wrapper.findAll('table tbody tr').length).toBe(runs.length);

      await wrapper
        .findAll(`td[data-username="${username}"]`)
        .at(1)
        .find(`a[title="${username}"]`)
        .trigger('click');

      const filteredRuns = runs.filter((run) => run.username == username);

      expect(wrapper.findAll('table tbody tr').length).toBe(
        filteredRuns.length,
      );

      await wrapper.find('[data-remove-all-filters]').trigger('click');

      // Now all runs should appear
      expect(wrapper.findAll('table tbody tr').length).toBe(runs.length);

      await wrapper
        .findAll(`td[data-username="${username}"]`)
        .at(1)
        .find(`a[title="${username}"]`)
        .trigger('click');

      await wrapper.find('[data-remove-filter]').trigger('click');

      // Now all runs should appear
      expect(wrapper.findAll('table tbody tr').length).toBe(runs.length);
      expect(wrapper.vm.filterUsername).toBeFalsy();
    });
  });
});
