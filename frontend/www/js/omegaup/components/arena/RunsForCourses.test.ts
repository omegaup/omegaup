jest.mock('../../../../third_party/js/diff_match_patch.js');

import { mount, shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';

import T from '../../lang';

import arena_RunsForCourses, {
  NumericOutputStatus,
} from './RunsForCourses.vue';
import { DisqualificationType } from './Runs.vue';

describe('RunsForCourses.vue', () => {
  it('Should handle empty runs', () => {
    const wrapper = shallowMount(arena_RunsForCourses, {
      propsData: {
        contestAlias: 'admin',
        runs: [],
      },
    });

    expect(wrapper.find('.card-header').text()).toBe(T.latestSubmissionsTitle);
    expect(wrapper.find('table tbody').text()).toBe('');
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
    {
      ...baseRunData,
      guid: '122500',
      time: new Date('1/1/2020, 12:30:00 AM'),
      execution: 'EXECUTION_INTERRUPTED',
      output: 'OUTPUT_INTERRUPTED',
      verdict: 'JE',
      status_memory: 'MEMORY_NOT_AVAILABLE',
      status_runtime: 'RUNTIME_NOT_AVAILABLE',
    },
    {
      ...baseRunData,
      guid: '123000',
      time: new Date('1/1/2020, 12:40:00 AM'),
      execution: 'EXECUTION_INTERRUPTED',
      output: 'OUTPUT_INTERRUPTED',
      verdict: 'VE',
      status_memory: 'MEMORY_NOT_AVAILABLE',
      status_runtime: 'RUNTIME_NOT_AVAILABLE',
    },
    {
      ...baseRunData,
      guid: '123500',
      time: new Date('1/1/2020, 12:50:00 AM'),
      execution: 'EXECUTION_INTERRUPTED',
      output: 'OUTPUT_INTERRUPTED',
      verdict: 'CE',
      status_memory: 'MEMORY_NOT_AVAILABLE',
      status_runtime: 'RUNTIME_NOT_AVAILABLE',
    },
    {
      ...baseRunData,
      guid: '124000',
      time: new Date('1/1/2020, 12:55:00 AM'),
      type: 'disqualified',
    },
    {
      ...baseRunData,
      guid: '124500',
      time: new Date('1/1/2020, 1:00:00 AM'),
      output: 'OUTPUT_CORRECT',
      score: 1,
      contest_score: 1,
      verdict: 'AC',
    },
  ];

  it('Should handle order runs', async () => {
    const wrapper = shallowMount(arena_RunsForCourses, {
      propsData: {
        contestAlias: 'admin',
        runs,
        showContest: true,
        showDetails: true,
        showDisqualify: true,
        showFilters: true,
        showPoints: false,
        showProblem: true,
        showRejudge: true,
        showUser: true,
        username: null,
        itemsPerPage: 100,
      },
    });
    expect(
      wrapper.findAll('acronym[data-run-guid]').wrappers.map((e) => e.text()),
    ).toEqual([
      '124500',
      '124000',
      '123500',
      '123000',
      '122500',
      '122000',
      '121500',
      '121000',
      '120500',
      '120000',
    ]);
  });

  const filtersMapping: { filter: string; value: string }[] = [
    { filter: 'execution', value: 'EXECUTION_FINISHED' },
    { filter: 'output', value: 'OUTPUT_CORRECT' },
    { filter: 'language', value: 'py3' },
  ];

  describe.each(filtersMapping)(`A filter:`, (filter) => {
    it(`whose name is ${filter.filter} should have gotten the value ${filter.value}`, async () => {
      const wrapper = shallowMount(arena_RunsForCourses, {
        propsData: {
          contestAlias: 'admin',
          runs,
          showFilters: true,
        },
      });
      await wrapper
        .find(`select[data-select-${filter.filter}]`)
        .find(`option[value="${filter.value}"]`)
        .setSelected();

      expect(wrapper.emitted('filter-changed')).toEqual([[filter]]);
    });
  });

  it('Should handle run percentage color', async () => {
    const wrapper = mount(arena_RunsForCourses, {
      propsData: {
        runs,
        itemsPerPage: 100,
      },
    });

    expect(
      wrapper.findAll('td[data-run-percentage]').wrappers.map((e) =>
        e
          .classes()
          .filter((c) => c !== 'numeric')
          .join(''),
      ),
    ).toEqual([
      'status-ac',
      'status-disqualified',
      'status-ce',
      'status-je-ve',
      'status-je-ve',
      '',
      '',
      '',
      '',
      '',
    ]);
  });

  it('Should handle paginator in user view', async () => {
    const wrapper = mount(arena_RunsForCourses, {
      propsData: {
        runs,
        itemsPerPage: 2,
      },
    });

    const paginationComponent = wrapper.findComponent({ name: 'BPagination' });
    expect(paginationComponent.exists()).toBe(true);
    expect(paginationComponent.vm.$data.localNumberOfPages).toBe(5);
    expect(paginationComponent.vm.$data.currentPage).toBe(1);
  });

  it('Should handle paginator in admin view', async () => {
    const wrapper = mount(arena_RunsForCourses, {
      propsData: {
        contestAlias: 'contest',
        runs,
        showFilters: true,
        showUser: true,
        itemsPerPage: 1,
      },
    });

    const paginationComponent = wrapper.findComponent({ name: 'BPagination' });

    expect(paginationComponent.exists()).toBe(true);

    expect(paginationComponent.vm.$data.localNumberOfPages).toBe(10);
    expect(paginationComponent.vm.$data.currentPage).toBe(1);

    const pageSlotContent = wrapper.find('[data-page]').text();

    expect(pageSlotContent).toContain('1 - 10');
  });

  it('Should handle paginator in admin view with no runs', async () => {
    const wrapper = mount(arena_RunsForCourses, {
      propsData: {
        contestAlias: 'contest',
        showFilters: true,
        showUser: true,
        itemsPerPage: 1,
      },
    });

    const pageSlotContent = wrapper.find('[data-page]').text();
    expect(pageSlotContent).toContain('1 - 1');
  });

  it('Should handle execution filter', async () => {
    const wrapper = mount(arena_RunsForCourses, {
      propsData: {
        contestAlias: 'contest',
        runs,
        showFilters: true,
        showUser: true,
        itemsPerPage: 1,
      },
    });

    const paginationComponent = wrapper.findComponent({ name: 'BPagination' });

    expect(paginationComponent.exists()).toBe(true);

    await wrapper.setData({ filterExecution: 'EXECUTION_INTERRUPTED' });
    expect(wrapper.emitted('filter-changed')).toEqual([
      [{ filter: 'execution', value: 'EXECUTION_INTERRUPTED' }],
    ]);

    expect(paginationComponent.vm.$data.localNumberOfPages).toBe(3);
    expect(paginationComponent.vm.$data.currentPage).toBe(1);

    const pageSlotContent = wrapper.find('[data-page]').text();

    expect(pageSlotContent).toContain('1 - 3');
  });

  it('Should handle output filter', async () => {
    const wrapper = mount(arena_RunsForCourses, {
      propsData: {
        contestAlias: 'contest',
        runs,
        showFilters: true,
        showUser: true,
        itemsPerPage: 1,
      },
    });

    const paginationComponent = wrapper.findComponent({ name: 'BPagination' });

    expect(paginationComponent.exists()).toBe(true);

    await wrapper.setData({ filterOutput: 'OUTPUT_CORRECT' });
    expect(wrapper.emitted('filter-changed')).toEqual([
      [{ filter: 'output', value: 'OUTPUT_CORRECT' }],
    ]);

    expect(paginationComponent.vm.$data.localNumberOfPages).toBe(1);
    expect(paginationComponent.vm.$data.currentPage).toBe(1);

    const pageSlotContent = wrapper.find('[data-page]').text();

    expect(pageSlotContent).toContain('1 - 1');
  });

  it('Should handle username filter', async () => {
    const wrapper = shallowMount(arena_RunsForCourses, {
      propsData: {
        contestAlias: 'contest',
        runs,
        showFilters: true,
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
    const wrapper = shallowMount(arena_RunsForCourses, {
      propsData: {
        contestAlias: 'contest',
        runs,
        showFilters: true,
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
    const wrapper = shallowMount(arena_RunsForCourses, {
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

  it('Should handle the right buttons for run actions', async () => {
    runs.push({
      ...baseRunData,
      guid: '122600',
      time: new Date('1/3/2020, 12:25:00 AM'),
      type: 'disqualified',
    });
    const wrapper = shallowMount(arena_RunsForCourses, {
      propsData: {
        contestAlias: 'admin',
        problemAlias: 'alias',
        runs,
        showDetails: true,
        showDisqualify: true,
        showRejudge: true,
        isContestFinished: false,
        useNewSubmissionButton: true,
        itemsPerPage: 100,
      },
    });
    expect(wrapper.find('[data-actions="120000"]').text()).toContain(
      T.arenaRunsActionsDisqualifyByGUID,
    );
    expect(wrapper.find('[data-actions="120000"]').text()).not.toContain(
      T.arenaRunsActionsRequalify,
    );
    await wrapper.find('[data-actions="120000"]').trigger('click');
    await wrapper.find('[data-actions-disqualify="120000"]').trigger('click');
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

  it('Should handle filterUsername when username changes', async () => {
    const wrapper = shallowMount(arena_RunsForCourses, {
      propsData: {
        contestAlias: 'admin',
        runs,
        showContest: true,
        showDetails: true,
        showDisqualify: true,
        showFilters: true,
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
      const wrapper = mount(arena_RunsForCourses, {
        propsData: {
          contestAlias: 'admin',
          runs,
          showContest: true,
          showDetails: true,
          showDisqualify: true,
          showFilters: true,
          showPoints: false,
          showProblem: true,
          showRejudge: true,
          showUser: true,
          username: null,
          itemsPerPage: 100,
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

  it('Should apply run-loading class and spinner icon when run status is not ready', () => {
    const runsWithLoading: types.Run[] = [
      {
        ...runs[0],
        guid: 'loading_run_course',
        status: 'new',
      },
      ...runs,
    ];
    const wrapper = mount(arena_RunsForCourses, {
      propsData: {
        contestAlias: 'admin',
        runs: runsWithLoading,
        createdGuid: 'loading_run_course',
      },
    });

    const loadingRows = wrapper.findAll('tbody tr.run-loading');
    expect(loadingRows.length).toBe(1);

    expect(loadingRows.at(0).find('svg.fa-spinner').exists()).toBe(true);
    expect(loadingRows.at(0).text()).toContain('new');
    expect(loadingRows.at(0).classes()).not.toContain('selected');
  });

  it('Should display correct execution, output, and icon upon evaluation when status becomes ready', () => {
    const evaluatedRunAC: types.Run = {
      ...runs[0],
      guid: 'evaluated_run_ac',
      time: new Date('2024-01-01'),
      status: 'ready',
      verdict: 'AC',
      score: 1,
      execution: 'EXECUTION_FINISHED',
      output: 'OUTPUT_CORRECT',
    };
    const wrapper = mount(arena_RunsForCourses, {
      propsData: {
        contestAlias: 'admin',
        runs: [evaluatedRunAC],
        createdGuid: 'evaluated_run_ac',
      },
    });

    const row = wrapper.find('tbody tr');
    expect(row.classes()).not.toContain('run-loading');
    expect(row.classes()).toContain('selected');
    expect(row.find('svg.fa-spinner').exists()).toBe(false);

    expect(wrapper.vm.execution(evaluatedRunAC)).toBe(T.runDetailsFinished);
    expect(wrapper.vm.output(evaluatedRunAC)).toBe(T.runDetailsCorrect);
    expect(wrapper.vm.outputIconColorStatus(evaluatedRunAC)).toBe(
      NumericOutputStatus.Correct,
    );
    expect(wrapper.vm.percentage(evaluatedRunAC)).toBe('100.00%');
    expect(row.find('svg.fa-check-circle').exists()).toBe(true);

    const evaluatedRunWA: types.Run = {
      ...runs[0],
      guid: 'evaluated_run_wa',
      time: new Date('2024-01-01'),
      status: 'ready',
      verdict: 'WA',
      score: 0,
      execution: 'EXECUTION_FINISHED',
      output: 'OUTPUT_INCORRECT',
    };
    expect(wrapper.vm.execution(evaluatedRunWA)).toBe(T.runDetailsFinished);
    expect(wrapper.vm.output(evaluatedRunWA)).toBe(T.runDetailsIncorrect);
    expect(wrapper.vm.outputIconColorStatus(evaluatedRunWA)).toBe(
      NumericOutputStatus.Incorrect,
    );

    const evaluatedRunCE: types.Run = {
      ...runs[0],
      guid: 'evaluated_run_ce',
      time: new Date('2024-01-01'),
      status: 'ready',
      verdict: 'CE',
      score: 0,
      execution: 'EXECUTION_COMPILATION_ERROR',
      output: 'OUTPUT_INTERRUPTED',
    };
    expect(wrapper.vm.execution(evaluatedRunCE)).toBe(
      T.runDetailsCompilationError,
    );
    expect(wrapper.vm.output(evaluatedRunCE)).toBe(T.runDetailsInterrupted);
    expect(wrapper.vm.outputIconColorStatus(evaluatedRunCE)).toBe(
      NumericOutputStatus.Interrupted,
    );

    const evaluatedRunTLE: types.Run = {
      ...runs[0],
      guid: 'evaluated_run_tle',
      time: new Date('2024-01-01'),
      status: 'ready',
      verdict: 'TLE',
      score: 0,
      execution: 'EXECUTION_INTERRUPTED',
      output: 'OUTPUT_INTERRUPTED',
    };
    expect(wrapper.vm.execution(evaluatedRunTLE)).toBe(T.runDetailsInterrupted);
    expect(wrapper.vm.output(evaluatedRunTLE)).toBe(T.runDetailsInterrupted);
    expect(wrapper.vm.outputIconColorStatus(evaluatedRunTLE)).toBe(
      NumericOutputStatus.Interrupted,
    );
  });
});
