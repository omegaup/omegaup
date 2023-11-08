import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import certificate_Validation from './Validation.vue';

describe('Validation.vue', () => {
  it('Should handle an invalid certificate', async () => {
    const wrapper = shallowMount(certificate_Validation, {
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

  it('Should handle a valid certificate', async () => {
    const wrapper = shallowMount(certificate_Validation, {
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
