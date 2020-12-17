import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import group_Identities from './Identities.vue';

describe('Identities.vue', () => {
  it('Should handle identities view', () => {
    const wrapper = shallowMount(group_Identities, {
      propsData: {
        groupAlias: 'Hello',
      },
    });

    expect(wrapper.text()).toContain(T.groupCreateIdentities);
  });
});
