import { types } from '../../api_types';
import T from '../../lang';
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

  it('Should handle new feedback for admin', async () => {
    const wrapper = shallowMount(omegaup_SubmissionFeedback, {
      propsData: { feedbackOptions: [], guid: 'afa568', isAdmin: true },
    });

    expect(
      wrapper.find('div[data-submission-feedback] > pre > code').text(),
    ).toBe(T.feedbackNotSentYet);
    expect(wrapper.find('.feedback-section a').text()).toBe(
      T.submissionFeedbackSendButton,
    );

    await wrapper.find('.feedback-section a').trigger('click');

    expect(wrapper.find('.feedback-section .form-group button').text()).toBe(
      T.submissionSendFeedback,
    );
    await wrapper.setData({ feedback: 'New feedback message' });
    await wrapper.find('.feedback-section .form-group button').trigger('click');

    expect(wrapper.emitted('set-feedback')).toEqual([
      [
        {
          feedback: 'New feedback message',
          guid: 'afa568',
          isUpdate: false,
        },
      ],
    ]);
  });

  it('Should handle existing feedback for admin', async () => {
    const wrapper = shallowMount(omegaup_SubmissionFeedback, {
      propsData: { feedbackOptions, guid: 'afa569', isAdmin: true },
    });

    expect(
      wrapper.find('div[data-submission-feedback] > pre > code').text(),
    ).toBe(savedFeedbackMessage);
    expect(wrapper.find('.feedback-section a').text()).toBe(
      T.submissionFeedbackUpdateButton,
    );

    await wrapper.find('.feedback-section a').trigger('click');

    expect(wrapper.find('.feedback-section .form-group button').text()).toBe(
      T.submissionUpdateFeedback,
    );
    await wrapper.setData({ feedback: 'Updated feedback message' });
    await wrapper.find('.feedback-section .form-group button').trigger('click');

    expect(wrapper.emitted('set-feedback')).toEqual([
      [
        {
          feedback: 'Updated feedback message',
          guid: 'afa569',
          isUpdate: true,
        },
      ],
    ]);
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
