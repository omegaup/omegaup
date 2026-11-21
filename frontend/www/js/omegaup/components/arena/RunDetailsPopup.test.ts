import { shallowMount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type { types } from '../../api_types';
import T from '../../lang';
import arena_RunDetailsPopup from './RunDetailsPopup.vue';

describe('RunDetailsPopup.vue', () => {
  const baseData: types.RunDetails & { groups?: types.RunDetailsGroup[] } = {
    admin: false,
    alias: 'problem_test',
    cases: {
      case1: {
        in: '1 2',
        out: '3',
        contestantOutput: '3',
      },
    },
    feedback: [],
    guid: 'abcdef1234567890',
    source: 'print(3)',
    language: 'py3',
    show_diff: 'all',
    source_name: 'Main.py',
    source_url: '/api/run/download/run_alias/abcdef1234567890/source/',
    groups: [
      {
        group: 'group1',
        score: 100,
        contest_score: 100,
        max_score: 100,
        cases: [
          {
            name: 'case1',
            verdict: 'AC',
            score: 100,
            contest_score: 100,
            max_score: 100,
            meta: {
              memory: 1000,
              sys_time: 0,
              time: 100,
              verdict: 'AC',
              wall_time: 100,
            },
          },
        ],
      },
    ],
  };

  it('Should render run details correctly', () => {
    const wrapper = shallowMount(
      arena_RunDetailsPopup as ComponentOptions<Vue>,
      {
        propsData: {
          data: baseData,
        },
      },
    );

    expect(wrapper.find('[data-run-guid]').text()).toBe('abcdef12');
    expect(wrapper.find('.sourcecode').text()).toBe(T.wordsDownloadCode);
    expect(wrapper.find('.sourcecode').attributes('download')).toBe('Main.py');
    expect(wrapper.find('.output').exists()).toBe(false);
  });

  it('Should render admin download links when admin is true', () => {
    const wrapper = shallowMount(
      arena_RunDetailsPopup as ComponentOptions<Vue>,
      {
        propsData: {
          data: {
            ...baseData,
            admin: true,
          },
        },
      },
    );

    expect(wrapper.find('.output').exists()).toBe(true);
    expect(wrapper.find('.details').exists()).toBe(true);
  });

  it('Should render compile error when present', () => {
    const wrapper = shallowMount(
      arena_RunDetailsPopup as ComponentOptions<Vue>,
      {
        propsData: {
          data: {
            ...baseData,
            compile_error: 'SyntaxError: unexpected EOF',
          },
        },
      },
    );

    expect(wrapper.find('.compile_error').exists()).toBe(true);
    expect(wrapper.find('.compile_error code').text()).toContain(
      'SyntaxError: unexpected EOF',
    );
  });

  it('Should toggle group visibility when toggle is clicked', async () => {
    const wrapper = shallowMount(
      arena_RunDetailsPopup as ComponentOptions<Vue>,
      {
        propsData: {
          data: baseData,
        },
      },
    );

    const vm = wrapper.vm as any;
    expect(vm.groupVisible.group1).toBeFalsy();
    vm.toggle('group1');
    expect(vm.groupVisible.group1).toBe(true);
    vm.toggle('group1');
    expect(vm.groupVisible.group1).toBe(false);
  });
});
