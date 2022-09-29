import { shallowMount } from '@vue/test-utils';
import T from '../../lang';

import arena_Feedback, { FeedbackStatus } from './Feedback.vue';

describe('Feedback.vue', () => {
  it('Should handle feedback component', async () => {
    const wrapper = shallowMount(arena_Feedback, {
      propsData: {
        feedback: {
          line: 2,
          text: null,
          status: FeedbackStatus.New,
        },
      },
    });

    expect(wrapper.text()).toContain(T.runDetailsNewFeedback);

    const button = wrapper.find('button[data-button-submit]');
    expect(button.attributes().disabled).toBeDefined();
    expect(button.attributes().disabled).toBe('disabled');

    await wrapper.setData({ text: 'some text' });
    expect(button.attributes().disabled).not.toBeDefined();
  });
});
