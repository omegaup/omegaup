import { shallowMount } from '@vue/test-utils';

import arena_FeedbackCodeView from './FeedbackCodeView.vue';

describe('FeedbackCodeView.vue', () => {
  it('Should handle feedback codeview when it is enabled', async () => {
    const wrapper = shallowMount(arena_FeedbackCodeView, {
      propsData: {
        language: 'java',
        value: 'This \ncode \nhas \nfour lines;',
        linesPerChunk: [1, 2, 3, 4],
        enableFeedback: true,
      },
    });

    expect(wrapper.text()).toContain(1);
    expect(wrapper.text()).toContain(2);
    expect(wrapper.text()).toContain(3);
    expect(wrapper.text()).toContain(4);
    expect(wrapper.text()).not.toContain(5);

    await wrapper.find('button[data-button-line="2"]').trigger('click');

    expect(wrapper.emitted('show-feedback-form')).toEqual([[2]]);
  });

  it('Should hide lines when feedback is disabled', async () => {
    const wrapper = shallowMount(arena_FeedbackCodeView, {
      propsData: {
        language: 'java',
        value: 'This \ncode \nhas \nfour lines;',
        linesPerChunk: [1, 2, 3, 4],
      },
    });

    expect(wrapper.text()).not.toContain(1);
    expect(wrapper.text()).not.toContain(2);
    expect(wrapper.text()).not.toContain(3);
    expect(wrapper.text()).not.toContain(4);
    expect(wrapper.text()).not.toContain(5);
  });
});
