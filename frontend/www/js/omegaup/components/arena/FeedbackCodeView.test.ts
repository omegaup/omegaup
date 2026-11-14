import { mount } from '@vue/test-utils';

import arena_FeedbackCodeView from './FeedbackCodeView.vue';
import { ArenaCourseFeedback, FeedbackStatus } from './Feedback.vue';

describe('FeedbackCodeView.vue', () => {
  it('Should handle feedback codeview with empty comments list', async () => {
    const wrapper = mount(arena_FeedbackCodeView, {
      propsData: {
        language: 'java',
        readonly: false,
        value: 'This \ncode \nhas \nfour lines;',
      },
    });

    expect(wrapper.text()).toContain('four lines;');
    expect(wrapper.find('button').attributes().disabled).toBe('disabled');
  });

  it('Should handle feedback codeview with comments', async () => {
    const feedbackMap: Map<number, ArenaCourseFeedback> = new Map();
    feedbackMap.set(0, {
      lineNumber: 0,
      text: 'some text',
      status: FeedbackStatus.New,
    });
    const wrapper = mount(arena_FeedbackCodeView, {
      propsData: {
        language: 'java',
        value: 'This \ncode \nhas \nfour lines;',
        feedbackMap,
        readonly: false,
      },
    });

    expect(wrapper.text()).toContain('four lines;');
    expect(wrapper.find('button').attributes().disabled).not.toBeDefined();
  });

  it('Should only make the gutter editable when it is not readonly', () => {
    const readonlyWrapper = mount(arena_FeedbackCodeView, {
      propsData: {
        language: 'java',
        value: 'This \ncode \nhas \nfour lines;',
      },
    });
    expect(
      readonlyWrapper.find('[data-feedback-code-mirror]').classes(),
    ).not.toContain('editable');

    const editableWrapper = mount(arena_FeedbackCodeView, {
      propsData: {
        language: 'java',
        value: 'This \ncode \nhas \nfour lines;',
        readonly: false,
      },
    });
    expect(
      editableWrapper.find('[data-feedback-code-mirror]').classes(),
    ).toContain('editable');
  });
});
