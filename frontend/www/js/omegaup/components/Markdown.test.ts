import { shallowMount } from '@vue/test-utils';

import omegaup_Markdown from './Markdown.vue';

describe('Markdown.vue', () => {
  it('Should render markdown contents', () => {
    const wrapper = shallowMount(omegaup_Markdown, {
      propsData: {
        markdown: '_Hello_, **World**!',
      },
    });
    expect(wrapper.html()).toEqual(
      expect.stringContaining('<p><em>Hello</em>, <strong>World</strong>!</p>'),
    );
  });
});
