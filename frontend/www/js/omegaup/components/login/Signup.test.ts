import { shallowMount } from '@vue/test-utils';

import login_Signup from './Signup.vue';

describe('signup.vue', () => {
  const registerParameters = {
    username: 'Omegaup',
    email: 'U13@gmail.com',
    parentEmail: 'parentEmail@gmail.com',
    password: '',
    passwordConfirmation: '',
    recaptchaResponse: '',
    birthDate: "946684800",
    privacyPolicyAccepted: true,
    
  };

  it('Should handle contest clarification', async () => {
    const wrapper = shallowMount(login_Signup, {
      propsData: {
        registerParameters,
        isAdmin: true,
      },
    });
    expect(wrapper.text()).toContain(registerParameters.birthDate);
    expect(wrapper.find('registerAndLogin').trigger('click'));
});
});

