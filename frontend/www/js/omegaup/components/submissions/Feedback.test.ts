import { types } from '../../api_types';
import { shallowMount } from '@vue/test-utils';

import omegaup_SubmissionFeedback from './Feedback.vue';
describe('Feedback.vue', () => {
  const savedFeedbackMessage = 'This is a saved feedback message';
  const feedbackOptions: types.SubmissionFeedback[] = [
    {
      author: 'omegaUp',
      author_classname: 'user-rank-unranked',
      date: new Date(),
      feedback: savedFeedbackMessage,
      feedback_thread: [],
      submission_feedback_id: 1,
    },
  ];

  it('Should handle existing feedback for admin', async () => {
    const wrapper = shallowMount(omegaup_SubmissionFeedback, {
      propsData: { feedbackOptions, guid: 'afa569', isAdmin: true },
    });

    expect(
      wrapper.find('div[data-submission-feedback] > pre > code').text(),
    ).toBe(savedFeedbackMessage);
    expect(
      wrapper.find('[data-submission-feedback-review-hint]').exists(),
    ).toBe(true);
    expect(wrapper.find('[data-run-leave-feedback-button]').exists()).toBe(
      false,
    );
  });

  it('Should handle existing feedback for user', async () => {
    const wrapper = shallowMount(omegaup_SubmissionFeedback, {
      propsData: { feedbackOptions, guid: 'afa569', isAdmin: false },
    });

    expect(
      wrapper.find('div[data-submission-feedback] > pre > code').text(),
    ).toBe(savedFeedbackMessage);
  });
});
