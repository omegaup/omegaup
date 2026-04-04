import { shallowMount } from '@vue/test-utils';
import T from '../../lang';

import arena_Feedback, {
  FeedbackStatus,
  ArenaCourseFeedback,
} from './Feedback.vue';

const feedback: ArenaCourseFeedback = {
  lineNumber: 2,
  text: null,
  status: FeedbackStatus.New,
};

describe('Feedback.vue', () => {
  it('Should handle feedback component when form is sumbitted', async () => {
    const wrapper = shallowMount(arena_Feedback, {
      propsData: {
        feedback,
      },
    });

    expect(wrapper.text()).toContain(T.runDetailsNewFeedback);

    const button = wrapper.find('button[data-button-submit]');
    expect(button.attributes().disabled).toBeDefined();
    expect(button.attributes().disabled).toBe('disabled');

    await wrapper.setData({
      currentFeedback: {
        lineNumber: 2,
        text: 'some text',
        status: FeedbackStatus.New,
      },
    });
    expect(wrapper.emitted('submit')).not.toBeDefined();
    expect(button.attributes().disabled).not.toBeDefined();

    await button.trigger('click');

    expect(wrapper.emitted('submit')).toEqual([
      [
        {
          lineNumber: 2,
          status: FeedbackStatus.InProgress,
          text: 'some text',
        },
      ],
    ]);
  });

  it('Should handle feedback component when form is cancelled', async () => {
    const wrapper = shallowMount(arena_Feedback, {
      propsData: {
        feedback,
      },
    });

    expect(wrapper.text()).toContain(T.runDetailsNewFeedback);

    const button = wrapper.find('button[data-button-cancel]');

    await wrapper.setData({
      currentFeedback: {
        lineNumber: 2,
        text: 'some text',
        status: FeedbackStatus.New,
      },
    });

    await button.trigger('click');

    expect(wrapper.emitted('cancel')).toEqual([[]]);
  });
});
