import { shallowMount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type { types } from '../../api_types';
import T from '../../lang';
import user_ManageApiTokens from './ManageApiTokens.vue';

const sampleTokens: types.ApiToken[] = [
  {
    name: 'token-1',
    timestamp: new Date('2023-01-01T00:00:00Z'),
    last_used: new Date('2023-01-02T00:00:00Z'),
    rate_limit: {
      limit: 1000,
      remaining: 950,
      reset: new Date('2023-01-03T00:00:00Z'),
    },
  },
];

describe('ManageApiTokens.vue', () => {
  it('Should display empty message when no tokens exist', () => {
    const wrapper = shallowMount(
      (user_ManageApiTokens as unknown) as ComponentOptions<Vue>,
      {
        propsData: {
          apiTokens: [],
        },
      },
    );

    expect(wrapper.find('.empty-category').exists()).toBe(true);
    expect(wrapper.find('.empty-category').text()).toBe(
      T.profileApiTokensEmpty,
    );
  });

  it('Should render tokens table when tokens exist', () => {
    const wrapper = shallowMount(
      (user_ManageApiTokens as unknown) as ComponentOptions<Vue>,
      {
        propsData: {
          apiTokens: sampleTokens,
        },
      },
    );

    expect(wrapper.find('table').exists()).toBe(true);
    expect(wrapper.text()).toContain('token-1');
  });

  it('Should emit create-api-token on form submit and clear input', async () => {
    const wrapper = shallowMount(
      (user_ManageApiTokens as unknown) as ComponentOptions<Vue>,
      {
        propsData: {
          apiTokens: sampleTokens,
        },
      },
    );

    const input = wrapper.find('input.username-input');
    await input.setValue('my-new-token');
    await wrapper.find('form').trigger('submit.prevent');

    expect(wrapper.emitted('create-api-token')).toBeTruthy();
    expect(wrapper.emitted('create-api-token')?.[0]).toEqual(['my-new-token']);
    expect((wrapper.vm as any).tokenName).toBeNull();
  });

  it('Should emit revoke-api-token on button click', async () => {
    const wrapper = shallowMount(
      (user_ManageApiTokens as unknown) as ComponentOptions<Vue>,
      {
        propsData: {
          apiTokens: sampleTokens,
        },
      },
    );

    const revokeBtn = wrapper.find('button.btn-secondary');
    expect(revokeBtn.exists()).toBe(true);
    await revokeBtn.trigger('click');

    expect(wrapper.emitted('revoke-api-token')).toBeTruthy();
    expect(wrapper.emitted('revoke-api-token')?.[0]).toEqual(['token-1']);
  });
});
