import { shallowMount, createLocalVue } from '@vue/test-utils';
import { types } from '../../api_types';
import T from '../../lang';
import problem_Details from './Detailsv2.vue';

import BootstrapVue, { BTab } from 'bootstrap-vue';

const localVue = createLocalVue();
localVue.use(BootstrapVue);

describe('Detailsv2.vue', () => {
  const problem: types.ProblemDetails = {
    accepted: 4,
    accepts_submissions: true,
    alias: 'test',
    allow_user_add_tags: false,
    commit: '123',
    creation_date: new Date(),
    email_clarifications: true,
    input_limit: 10240,
    karel_problem: false,
    languages: ['py2', 'py3'],
    limits: {
      input_limit: '10 KiB',
      memory_limit: '32 MiB',
      overall_wall_time_limit: '1s',
      time_limit: '1s',
    },
    nominationStatus: {
      alreadyReviewed: false,
      canNominateProblem: false,
      dismissed: false,
      dismissedBeforeAc: false,
      language: 'py2',
      nominated: false,
      nominatedBeforeAc: false,
      solved: true,
      tried: true,
    },
    order: 'sum',
    points: 100,
    problem_id: 1,
    quality_seal: true,
    score: 100,
    settings: {
      cases: {
        statement_001: {
          in: '6\n2 3 2 3 2 4',
          out: '10',
          weight: 1,
        },
      },
      limits: {
        ExtraWallTime: '0s',
        MemoryLimit: 33554432,
        OutputLimit: 10240,
        OverallWallTimeLimit: '1s',
        TimeLimit: '1s',
      },
      validator: {
        name: 'token-numeric',
        tolerance: 1e-9,
      },
    },
    show_diff: 'none',
    statement: {
      images: {},
      sources: {},
      language: 'es',
      markdown: '# test',
    },
    submissions: 5,
    title: '',
    version: '123',
    visibility: 1,
    visits: 5,
  };

  it('Should show the tabs', () => {
    const wrapper = shallowMount(problem_Details, {
      propsData: {
        problem,
        user: {
          loggedIn: true,
          admin: true,
          reviewer: true,
        },
        languages: ['py2', 'py3'],
      },
      localVue,
    });

    const tabs = wrapper.findAllComponents(BTab);
    const expectedTabs = [T.wordsProblem, T.wordsRuns, T.wordsClarifications];
    expect(expectedTabs.length).toBe(tabs.length);
    for (let i = 0; i < expectedTabs.length; i++) {
      expect(tabs.at(i).attributes('title')).toBe(expectedTabs[i]);
    }
  });

  it('Should show the problem tab details', () => {
    const wrapper = shallowMount(problem_Details, {
      propsData: {
        problem,
        user: {
          loggedIn: true,
          admin: true,
          reviewer: true,
        },
        languages: ['py2', 'py3'],
      },
      localVue,
    });

    const problemTab = wrapper.findComponent(BTab);
    expect(problemTab.text()).toContain(problem.title);
  });
});
