import { createLocalVue, shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';
import Clipboard from 'v-clipboard';

import T from '../../lang';

import course_AdmissionMode from './AdmissionMode.vue';

describe('AdmissionMode.vue', () => {
  const localVue = createLocalVue();
  Vue.use(Clipboard);

  it('Should handle admission mode as curator', () => {
    const wrapper = shallowMount(course_AdmissionMode, {
      localVue,
      propsData: {
        admissionModeDescription: T.contestNewFormAdmissionModeDescription,
        courseAlias: 'DP',
        initialAdmissionMode: 'private',
        shouldShowPublicOption: true,
      },
    });

    expect(wrapper.find('select[name="admission-mode"]').text()).toContain(
      T.admissionModePublic,
    );
  });

  it('Should handle admission mode as normal user', () => {
    const wrapper = shallowMount(course_AdmissionMode, {
      localVue,
      propsData: {
        admissionModeDescription: T.contestNewFormAdmissionModeDescription,
        courseAlias: 'DP',
        initialAdmissionMode: 'private',
        shouldShowPublicOption: false,
      },
    });

    expect(wrapper.find('select[name="admission-mode"]').text()).not.toContain(
      T.admissionModePublic,
    );
  });
});
