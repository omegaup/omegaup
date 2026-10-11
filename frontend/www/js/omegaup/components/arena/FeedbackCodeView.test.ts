import { mount } from '@vue/test-utils';

import arena_FeedbackCodeView from './FeedbackCodeView.vue';
import { ArenaCourseFeedback, FeedbackStatus } from './Feedback.vue';

describe('FeedbackCodeView.vue', () => {
  const source = 'This \ncode \nhas \nfour lines;';

  it('Should handle feedback codeview with empty comments list', async () => {
    const wrapper = mount(arena_FeedbackCodeView, {
      propsData: {
        language: 'java',
        readonly: false,
        value: source,
      },
    });

    expect(wrapper.text()).toContain('four lines;');
    expect(wrapper.find('[data-pending-comments]').text()).toContain('0');
    expect(
      wrapper.find('[data-button-finish-review]').attributes().disabled,
    ).not.toBeDefined();

    await wrapper.find('[data-button-finish-review]').trigger('click');
    expect(
      wrapper.find('[data-button-submit-review]').attributes().disabled,
    ).toBe('disabled');
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
        value: source,
        feedbackMap,
        readonly: false,
      },
    });

    expect(wrapper.text()).toContain('four lines;');
    expect(wrapper.find('[data-pending-comments]').text()).toContain('1');
    expect(
      wrapper.find('[data-button-finish-review]').attributes().disabled,
    ).not.toBeDefined();
  });

  it('Should submit pending comments and the review summary', async () => {
    const feedbackMap: Map<number, ArenaCourseFeedback> = new Map();
    feedbackMap.set(0, {
      lineNumber: 0,
      text: 'rename this',
      status: FeedbackStatus.InProgress,
    });
    feedbackMap.set(2, {
      lineNumber: 2,
      text: 'already saved',
      status: FeedbackStatus.Saved,
    });
    const wrapper = mount(arena_FeedbackCodeView, {
      propsData: {
        language: 'java',
        value: source,
        feedbackMap,
        readonly: false,
      },
    });

    await wrapper.find('[data-button-finish-review]').trigger('click');
    await wrapper.find('[data-review-summary]').setValue('Looks close.');
    expect(
      wrapper.find('[data-button-submit-review]').attributes().disabled,
    ).not.toBeDefined();
    await wrapper.find('[data-button-submit-review]').trigger('click');

    expect(wrapper.emitted('save-feedback-list')).toEqual([
      [
        {
          feedbackList: [{ lineNumber: 0, feedback: 'rename this' }],
          generalFeedback: 'Looks close.',
        },
      ],
    ]);
    expect(wrapper.find('[data-review-summary]').exists()).toBe(false);
  });

  it('Should allow submitting a summary without line comments', async () => {
    const wrapper = mount(arena_FeedbackCodeView, {
      propsData: {
        language: 'java',
        readonly: false,
        value: source,
      },
    });

    await wrapper.find('[data-button-finish-review]').trigger('click');
    await wrapper.find('[data-review-summary]').setValue('Nice work.');
    expect(
      wrapper.find('[data-button-submit-review]').attributes().disabled,
    ).not.toBeDefined();
    await wrapper.find('[data-button-submit-review]').trigger('click');

    expect(wrapper.emitted('save-feedback-list')).toEqual([
      [
        {
          feedbackList: [],
          generalFeedback: 'Nice work.',
        },
      ],
    ]);
  });
});
