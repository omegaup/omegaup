import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import common_Publish from './Publish.vue';

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
