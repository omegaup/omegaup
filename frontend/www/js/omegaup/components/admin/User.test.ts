import { shallowMount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type Vue from 'vue';
import type { types } from '../../api_types';
import type { omegaup } from '../../omegaup';

import AdminUser from './User.vue';

describe('User.vue', () => {
  const defaultProps = {
    emails: ['admin@omegaup.com', 'backup@omegaup.com'],
    username: 'test_admin',
    verified: false,
    experiments: ['beta_feature'],
    systemExperiments: [
      {
        name: 'beta_feature',
        hash: 'hash1',
        config: false,
      },
      {
        name: 'stable_feature',
        hash: 'hash2',
        config: true,
      },
    ] as omegaup.Experiment[],
    roles: ['Admin'],
    roleNames: [{ name: 'Admin' }, { name: 'Reviewer' }] as types.UserRole[],
  };

  it('Should render username, emails, and roles correctly', () => {
    const wrapper = shallowMount(AdminUser as ComponentOptions<Vue>, {
      propsData: defaultProps,
    });

    expect(wrapper.text()).toContain('test_admin');
    expect(wrapper.text()).toContain('admin@omegaup.com');
    expect(wrapper.text()).toContain('backup@omegaup.com');
    expect(wrapper.text()).toContain('Admin');
    expect(wrapper.text()).toContain('Reviewer');
    expect(wrapper.text()).toContain('beta_feature');
  });

  it('Should emit verify-user event when verify button is clicked', async () => {
    const wrapper = shallowMount(AdminUser as ComponentOptions<Vue>, {
      propsData: defaultProps,
    });

    const verifyButton = wrapper.find('button.btn');
    await verifyButton.trigger('click');

    expect(wrapper.emitted('verify-user')).toBeTruthy();
  });

  it('Should disable verify button when user is already verified', () => {
    const wrapper = shallowMount(AdminUser as ComponentOptions<Vue>, {
      propsData: {
        ...defaultProps,
        verified: true,
      },
    });

    const verifyButton = wrapper.find('button.btn');
    expect(verifyButton.attributes('disabled')).toBe('disabled');
  });

  it('Should emit change-role event when role checkbox is toggled', async () => {
    const wrapper = shallowMount(AdminUser as ComponentOptions<Vue>, {
      propsData: defaultProps,
    });

    // Checkboxes: 2 roles, 2 experiments
    // Reviewer checkbox is index 1
    const reviewerCheckbox = wrapper
      .findAll('table tbody tr td input[type="checkbox"]')
      .at(1);
    const inputElement = reviewerCheckbox.element as HTMLInputElement;
    inputElement.checked = true;
    await reviewerCheckbox.trigger('change');

    expect(wrapper.emitted('change-role')).toBeTruthy();
    expect(wrapper.emitted('change-role')?.[0]?.[0]).toEqual({
      value: defaultProps.roleNames[1],
      selected: true,
    });
  });

  it('Should emit change-experiment event when experiment checkbox is toggled', async () => {
    const wrapper = shallowMount(AdminUser as ComponentOptions<Vue>, {
      propsData: defaultProps,
    });

    // Experiments are in the second table; beta_feature checkbox is index 2
    const expCheckbox = wrapper
      .findAll('table tbody tr td input[type="checkbox"]')
      .at(2);
    const inputElement = expCheckbox.element as HTMLInputElement;
    inputElement.checked = false;
    await expCheckbox.trigger('change');

    expect(wrapper.emitted('change-experiment')).toBeTruthy();
    expect(wrapper.emitted('change-experiment')?.[0]?.[0]).toEqual({
      value: defaultProps.systemExperiments[0],
      selected: false,
    });
  });
});
