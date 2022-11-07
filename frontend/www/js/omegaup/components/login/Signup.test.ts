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

  it('should handle register and login', async () => {
    const wrapper = shallowMount(login_Signup, {
      propsData: {
        registerParameters,
        isAdmin: true,
      },
    });
  
    await wrapper.find('button[type="register-and-login"]').trigger('click');
    expect(wrapper.emitted('register-and-login')).toEqual([
      [
        {
          username: 'Omegaup',
          email: 'U13@gmail.com',
          parentEmail: 'parentEmail@gmail.com',
          password: '',
          passwordConfirmation: '',
          recaptchaResponse: '',
          birthDate: "946684800",
          privacyPolicyAccepted: true,
        },
      ],
    ]);
});
});

