import { createLocalVue, shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import Clipboard from 'v-clipboard';

import T from '../../lang';

import course_GenerateLinkClone from './GenerateLinkClone.vue';

describe('GenerateLinkClone.vue', () => {
  const localVue = createLocalVue();
  Vue.use(Clipboard);

  it('Should handle a link with valid token', async () => {
    const wrapper = shallowMount(course_GenerateLinkClone, {
      localVue,
      propsData: {
        admissionModeDescription: T.contestNewFormAdmissionModeDescription,
        alias: 'DP',
        token: 'v2.local.fak3T0k3n',
      },
    });
    expect(wrapper.text()).toContain(T.courseCloneGenerateLinkDescription);
  });
});
