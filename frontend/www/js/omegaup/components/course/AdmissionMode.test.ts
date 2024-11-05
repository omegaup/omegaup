import { createLocalVue, mount, shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import Clipboard from 'v-clipboard';

import T from '../../lang';

import course_AdmissionMode from './AdmissionMode.vue';
import { AdmissionMode } from '../common/Publish.vue';

describe('AdmissionMode.vue', () => {
  const localVue = createLocalVue();
  Vue.use(Clipboard);

  it('Should handle admission mode as curator', () => {
    const wrapper = mount(course_AdmissionMode, {
      localVue,
      propsData: {
        admissionModeDescription: T.contestNewFormAdmissionModeDescription,
        courseAlias: 'DP',
        admissionMode: AdmissionMode.Public,
        shouldShowPublicOption: true,
      },
    });

    expect(wrapper.find('select[name="admission-mode"]').text()).toContain(
      T.admissionModePublic,
    );

    expect(
      wrapper.find('div[data-toggle-public-course-list]>label').text(),
    ).toBe(T.courseEditShowInPublicCoursesList);
  });

  it('Should handle admission mode as normal user', () => {
    const wrapper = shallowMount(course_AdmissionMode, {
      localVue,
      propsData: {
        admissionModeDescription: T.contestNewFormAdmissionModeDescription,
        courseAlias: 'DP',
        admissionMode: AdmissionMode.Public,
        shouldShowPublicOption: false,
      },
    });

    expect(
      wrapper.find('div[data-toggle-public-course-list]>label').exists(),
    ).toBeFalsy();
  });
});
