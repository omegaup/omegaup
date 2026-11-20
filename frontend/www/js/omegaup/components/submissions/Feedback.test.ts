import { types } from '../../api_types';
import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import omegaup_SubmissionFeedback from './Feedback.vue';

const Feedback = omegaup_SubmissionFeedback as ComponentOptions<Vue>;

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
    const wrapper = shallowMount(Feedback, {
      propsData: { feedbackOptions, guid: 'afa569', isAdmin: true },
    });

    expect(
      wrapper.find('div[data-submission-feedback] > pre > code').text(),
    ).toBe(savedFeedbackMessage);
  });

  it('Should handle existing feedback for user', async () => {
    const wrapper = shallowMount(Feedback, {
      propsData: { feedbackOptions, guid: 'afa569', isAdmin: false },
    });

    expect(
      wrapper.find('div[data-submission-feedback] > pre > code').text(),
    ).toBe(savedFeedbackMessage);
  });
});
