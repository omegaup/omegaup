import { shallowMount } from '@vue/test-utils';
import T from '../../lang';

import arena_Feedback, {
  FeedbackStatus,
  ArenaCourseFeedback,
} from './Feedback.vue';

const feedback: ArenaCourseFeedback = {
  line: 2,
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

    await wrapper.setData({ text: 'some text' });
    expect(button.attributes().disabled).not.toBeDefined();

    await button.trigger('click');

    expect(wrapper.emitted('submit')).toEqual([
      [
        {
          line: 2,
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

    await wrapper.setData({ text: 'some text' });

    await button.trigger('click');

    expect(wrapper.emitted('cancel')).toEqual([
      [
        {
          line: 2,
          status: FeedbackStatus.New,
          text: null,
        },
      ],
    ]);
  });
});
