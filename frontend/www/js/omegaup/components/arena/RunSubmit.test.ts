import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import * as ui from '../../ui';

import arena_RunSubmit from './RunSubmit.vue';

describe('RunSubmit.vue', () => {
  it('Should handle disabled button', () => {
    const now = new Date();
    const future = now.getTime() + 10 * 1000;
    const nextSubmission = new Date(future);
    const wrapper = shallowMount(arena_RunSubmit, {
      propsData: {
        languages: [
          { py2: 'Python 2.7' },
          { py3: 'Python 3.6' },
          { java: 'Java' },
        ],
        nextSubmissionTimestamp: nextSubmission,
      },
    });

    expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBe(
      'disabled',
    );
  });

  it('Should handle enable button', () => {
    const wrapper = shallowMount(arena_RunSubmit, {
      propsData: {
        languages: [
          { py2: 'Python 2.7' },
          { py3: 'Python 3.6' },
          { java: 'Java' },
        ],
        nextSubmissionTimestamp: new Date(),
      },
    });

    expect(wrapper.find('button[type="submit"]').text()).toBe(T.wordsSend);
  });
});
