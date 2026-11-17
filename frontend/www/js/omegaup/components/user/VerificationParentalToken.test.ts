import { shallowMount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type Vue from 'vue';
import { BIconCheckCircleFill, BIconXCircleFill } from 'bootstrap-vue';
import user_VerificationParentalToken from './VerificationParentalToken.vue';
import T from '../../lang';

describe('VerificationParentalToken.vue', () => {
  it('Should render success icon when token is verified', () => {
    const wrapper = shallowMount(
      user_VerificationParentalToken as ComponentOptions<Vue>,
      {
        propsData: {
          message: 'Token verified successfully',
          hasParentalVerificationToken: true,
        },
      },
    );

    expect(wrapper.find('h1.card-header').text()).toBe(
      T.omegaupTitleParentalVerificationToken,
    );
    expect(wrapper.findComponent(BIconCheckCircleFill).exists()).toBe(true);
    expect(wrapper.findComponent(BIconXCircleFill).exists()).toBe(false);
    expect(wrapper.find('a').text()).toBe(
      T.verificationParentalTokenGoToMyDependents,
    );
  });

  it('Should render danger icon when token is not verified', () => {
    const wrapper = shallowMount(
      user_VerificationParentalToken as ComponentOptions<Vue>,
      {
        propsData: {
          message: 'Invalid token',
          hasParentalVerificationToken: false,
        },
      },
    );

    expect(wrapper.findComponent(BIconCheckCircleFill).exists()).toBe(false);
    expect(wrapper.findComponent(BIconXCircleFill).exists()).toBe(true);
  });
});
