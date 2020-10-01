import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import T from '../../lang';
import * as ui from '../../ui';

import common_Publish from './Publishv2.vue';

describe('Publish.vue', () => {
  it('Should handle initial props', async () => {
    const wrapper = shallowMount(common_Publish, {
      propsData: {
        initialAdmissionMode: 'public',
        shouldShowPublicOption: true,
        admissionModeDescription: T.contestNewFormAdmissionModeDescription,
      },
    });

    expect(wrapper.text()).toContain(T.contestNewFormAdmissionMode);
  });
});
