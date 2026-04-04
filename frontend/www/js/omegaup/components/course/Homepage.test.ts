import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import course_Homepage from './Homepage.vue';

describe('Homepage.vue', () => {
  it('Should display the right text', async () => {
    const wrapper = shallowMount(course_Homepage, {});
    expect(wrapper.text()).toContain(T.courseHomepageTitle);
  });
});
