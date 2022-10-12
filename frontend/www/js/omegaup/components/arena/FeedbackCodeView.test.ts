import { mount } from '@vue/test-utils';

import arena_FeedbackCodeView from './FeedbackCodeView.vue';

describe('FeedbackCodeView.vue', () => {
  it('Should handle feedback codeview', async () => {
    const wrapper = mount(arena_FeedbackCodeView, {
      propsData: {
        language: 'java',
        value: 'This \ncode \nhas \nfour lines;',
      },
    });

    expect(wrapper.text()).toContain('four lines;');
  });
});
