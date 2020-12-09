import { mount, shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';
import * as ui from '../../ui';

import arena_RunSubmit from './RunSubmit.vue';

describe('RunSubmit.vue', () => {
  const currentDate = new Date();
  const now = currentDate.getTime();

  it('Should handle disabled button', () => {
    const future = now + 10 * 1000;
    const nextSubmission = new Date(future);
    const wrapper = mount(arena_RunSubmit, {
      propsData: {
        languages: [
          { py2: 'Python 2.7' },
          { py3: 'Python 3.6' },
          { java: 'Java' },
        ],
        nextSubmissionTimestamp: nextSubmission,
        preferredLanguage: 'es',
      },
    });

    const message = ui.formatString(T.arenaRunSubmitWaitBetweenUploads, {
      submissionGap: 10,
    });

    expect(wrapper.find('button[type="submit"]').text()).toBe(message);
    expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBe(
      'disabled',
    );
  });

  it('Should handle enable button', () => {
    const past = now - 1 * 1000;
    const nextSubmission = new Date(past);
    const wrapper = shallowMount(arena_RunSubmit, {
      propsData: {
        languages: [
          { py2: 'Python 2.7' },
          { py3: 'Python 3.6' },
          { java: 'Java' },
        ],
        nextSubmissionTimestamp: nextSubmission,
        preferredLanguage: 'es',
      },
    });

    expect(wrapper.find('button[type="submit"]').text()).toBe(T.wordsSend);
  });
});
