import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import group_Identitiesv2 from './Identitiesv2.vue';

describe('Identitiesv2.vue', () => {
  it('Should handle identities view', () => {
    const wrapper = shallowMount(group_Identitiesv2, {
      propsData: {
        groupAlias: 'Hello',
      },
    });

    expect(wrapper.text()).toContain(T.groupCreateIdentities);
  });
});
