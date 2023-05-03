import { mount, shallowMount } from '@vue/test-utils';
import T from '../../lang';

import arena_FeedbackThread from './FeedbackThread.vue';
import { FeedbackStatus, ArenaCourseFeedback } from './Feedback.vue';

const feedback: ArenaCourseFeedback = {
  lineNumber: 2,
  text: null,
  status: FeedbackStatus.New,
};

describe('FeedbackThread.vue', () => {
  it('Should handle feedback thread component when form is sumbitted', async () => {
    const wrapper = mount(arena_FeedbackThread, {
      propsData: {
        feedback,
      },
    });

    const input = wrapper.find('input[type="text"]');
    expect(input.attributes().placeholder).toBe(
      T.runDetailsFeedbackThreadPlaceholder,
    );

    await input.trigger('click');

    expect(wrapper.html()).not.toContain('input');

    const addFeedbackButton = wrapper.find('button[data-button-submit]');
    expect(addFeedbackButton.attributes().disabled).toBe('disabled');

    await wrapper.find('textarea').setValue('some text');

    expect(wrapper.emitted('submit-feedback-thread')).not.toBeDefined();
    expect(addFeedbackButton.attributes().disabled).not.toBeDefined();

    await addFeedbackButton.trigger('click');

    expect(wrapper.emitted('submit-feedback-thread')).toEqual([
      [
        {
          lineNumber: 2,
          status: FeedbackStatus.New,
          text: 'some text',
        },
      ],
    ]);

    expect(wrapper.text()).toContain('some text');
  });

  it('Should handle feedback thread component when form is cancelled', async () => {
    const wrapper = shallowMount(arena_FeedbackThread, {
      propsData: {
        feedback,
      },
    });

    const input = wrapper.find('input[type="text"]');
    expect(input.attributes().placeholder).toBe(
      T.runDetailsFeedbackThreadPlaceholder,
    );

    await input.trigger('click');

    expect(wrapper.html()).not.toContain('input');

    const cancelButton = wrapper.find('button[data-button-cancel]');

    await cancelButton.trigger('click');

    const newInput = wrapper.find('input[type="text"]');
    expect(newInput.attributes().placeholder).toBe(
      T.runDetailsFeedbackThreadPlaceholder,
    );
  });
});
