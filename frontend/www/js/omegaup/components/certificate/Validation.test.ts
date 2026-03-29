import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import T from '../../lang';

import certificate_Validation from './Validation.vue';

// defineComponent() is typed for Vue 2.7/3 interop; @vue/test-utils@1 expects
// ComponentOptions<Vue>. Runtime is correct — assertion removed with test-utils@2.
const Validation = certificate_Validation as unknown as ComponentOptions<Vue>;

describe('Validation.vue', () => {
  it('Should handle an invalid certificate', () => {
    const wrapper = shallowMount(Validation, {
      propsData: {
        verificationCode: 'ps9Atr691a',
        isValid: false,
      },
    });

    expect(wrapper.text()).toContain(T.certificateValidationEnteredCode);
    expect(wrapper.text()).toContain('ps9Atr691a');
    expect(wrapper.text()).toContain(T.certificateValidationStatus);
    expect(wrapper.text()).toContain(
      T.certificateValidationInvalid.toUpperCase(),
    );
    expect(wrapper.text()).toContain(T.certificateValidationNotFound1);
    expect(wrapper.text()).toContain(T.certificateValidationNotFound2);
    expect(wrapper.find('object').exists()).toBeFalsy();
  });

  it('Should handle a valid certificate', () => {
    const wrapper = shallowMount(Validation, {
      propsData: {
        verificationCode: 'ps9Atr691a',
        isValid: true,
        certificate: '',
      },
    });

    expect(wrapper.text()).toContain(T.certificateValidationEnteredCode);
    expect(wrapper.text()).toContain('ps9Atr691a');
    expect(wrapper.text()).toContain(T.certificateValidationCertifyValidity);
    expect(wrapper.find('object').exists()).toBeTruthy();
  });
});
