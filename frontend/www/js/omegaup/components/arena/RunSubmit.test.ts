import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import * as ui from '../../ui';

import course_RunSubmit from './RunSubmit.vue';

describe('RunSubmit.vue', () => {
  it('Should handle disabled button', () => {
    const wrapper = shallowMount(course_RunSubmit, {
      propsData: {
        languages: [
          { py2: 'Python 2.7' },
          { py3: 'Python 3.6' },
          { java: 'Java' },
        ],
        submissionGapSecondsRemaining: 12,
      },
    });

    const message = ui.formatString(T.arenaRunSubmitWaitBetweenUploads, {
      submissionGap: 12,
    });

    expect(wrapper.find('input[type="submit"]').attributes('value')).toBe(
      message,
    );
  });

  it('Should handle enable button', () => {
    const wrapper = shallowMount(course_RunSubmit, {
      propsData: {
        languages: [
          { py2: 'Python 2.7' },
          { py3: 'Python 3.6' },
          { java: 'Java' },
        ],
      },
    });

    expect(wrapper.find('input[type="submit"]').attributes('value')).toBe(
      T.wordsSend,
    );
  });
});
