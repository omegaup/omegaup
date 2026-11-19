import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import T from '../../lang';

import activity_SubmissionsList from './SubmissionsList.vue';

const SubmissionsList = activity_SubmissionsList as ComponentOptions<Vue>;

describe('SubmissionsList.vue', () => {
  it('Should group solved and unsolved problems', () => {
    const wrapper = shallowMount(SubmissionsList, {
      propsData: {
        solvedProblems: {
          user_1: [
            { alias: 'sumas', title: 'Sumas' },
            { alias: 'restas', title: 'Restas' },
            { alias: 'mult', title: 'Multiplicacion' },
            { alias: 'div', title: 'Division' },
          ],
        },
        unsolvedProblems: {
          user_1: [{ alias: 'hard', title: 'Hard problem' }],
        },
      },
    });

    expect(wrapper.text()).toContain(T.profileSolvedProblems);
    expect(wrapper.text()).toContain(T.profileUnsolvedProblems);
    expect(wrapper.text()).toContain('Sumas');
    expect(wrapper.text()).toContain('Hard problem');
    expect(wrapper.findAll('table').length).toBe(2);
  });
});
