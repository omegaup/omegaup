import { mount, shallowMount } from '@vue/test-utils';

import { types } from '../../api_types';
import T from '../../lang';

import contest_Certificates from './Certificates.vue';

describe('Certificates.vue', () => {
  it('Should handle generated certificates', () => {
    const certificatesDetails: types.ContestCertificatesAdminDetails = {
      certificateCutoff: 5,
      certificatesStatus: 'generated',
      isCertificateGenerator: true,
    };
    const wrapper = shallowMount(contest_Certificates, {
      propsData: {
        certificatesDetails,
      },
    });

    expect(wrapper.text()).toContain(T.contestCertificatesGenerate);
    expect(wrapper.text()).toContain(T.contestCertificatesCutoff);
    expect(wrapper.text()).toContain(T.contestCertificatesCutoffHelp);
    expect(wrapper.find('input').attributes().disabled).toBe('disabled');
    expect(
      wrapper.find('button[data-button-generate]').attributes().disabled,
    ).toBe('disabled');
  });

  it('Should generate certificates', async () => {
    const certificatesDetails: types.ContestCertificatesAdminDetails = {
      certificateCutoff: 5,
      certificatesStatus: 'uninitiated',
      isCertificateGenerator: true,
    };
    const wrapper = mount(contest_Certificates, {
      propsData: {
        certificatesDetails,
      },
    });

    expect(wrapper.text()).toContain(T.contestCertificatesGenerate);
    expect(wrapper.text()).toContain(T.contestCertificatesCutoff);
    expect(wrapper.text()).toContain(T.contestCertificatesCutoffHelp);
    expect(wrapper.find('input').attributes().disabled).not.toBe('disabled');
    expect(
      wrapper.find('button[data-button-generate]').attributes().disabled,
    ).not.toBe('disabled');

    await wrapper.find('button[data-button-generate]').trigger('click');
    await wrapper.find('button[data-button-confirm]').trigger('click');
    expect(wrapper.emitted('generate')).toEqual([[5]]);
  });
});
