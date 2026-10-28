import { shallowMount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type Vue from 'vue';
import user_PrivacyPolicy from './PrivacyPolicy.vue';
import T from '../../lang';

describe('PrivacyPolicy.vue', () => {
  const defaultProps = {
    policyMarkdown: '# Privacy Policy Content',
    agreed: false,
    saved: false,
  };

  it('Should render policy markdown and agree controls', () => {
    const wrapper = shallowMount(user_PrivacyPolicy as ComponentOptions<Vue>, {
      propsData: defaultProps,
    });

    expect(wrapper.find('h3.card-header').text()).toBe(T.wordsPrivacyPolicy);
    expect(wrapper.find('input[name="agreed"]').exists()).toBe(true);
    expect(wrapper.find('button.btn-primary').text()).toBe(T.wordsSaveChanges);
  });

  it('Should disable submit button when not agreed or already saved', async () => {
    const wrapper = shallowMount(user_PrivacyPolicy as ComponentOptions<Vue>, {
      propsData: defaultProps,
    });

    expect(
      (wrapper.find('button.btn-primary').element as HTMLButtonElement)
        .disabled,
    ).toBe(true);

    await wrapper.setProps({ agreed: true, saved: true });
    expect(
      (wrapper.find('button.btn-primary').element as HTMLButtonElement)
        .disabled,
    ).toBe(true);
  });

  it('Should enable submit button and emit submit when agreed', async () => {
    const wrapper = shallowMount(user_PrivacyPolicy as ComponentOptions<Vue>, {
      propsData: defaultProps,
    });

    const checkbox = wrapper.find('input[name="agreed"]');
    await checkbox.setChecked(true);

    expect(
      (wrapper.find('button.btn-primary').element as HTMLButtonElement)
        .disabled,
    ).toBe(false);

    await wrapper.find('form').trigger('submit');
    expect(wrapper.emitted('submit')).toBeDefined();
    expect(wrapper.emitted('submit')?.length).toBe(1);
  });
});

