import { shallowMount } from '@vue/test-utils';

import login_Signup from './Signup.vue';

describe('signup.vue', () => {
  const registerAndLogin = 'register-and-login';
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

  it('should handle register and login', async () => {
    const wrapper = shallowMount(login_Signup, {
      propsData: {
        registerParameters,
        isAdmin: true,
      },
    });
    expect(wrapper.text()).toContain(registerAndLogin);
    expect(wrapper.find('registerAndLogin').trigger('click'));
});
});

