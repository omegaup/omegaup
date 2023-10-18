import { mount, shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';

import T from '../../lang';

import certificate_Mine from './Mine.vue';

describe('Mine.vue', () => {
  const propsData = {
    certificates: [
      {
        certificate_type: 'contest',
        date: new Date(),
        name: 'Test contest',
        verification_code: 'k2s8OPl620',
      },
      {
        certificate_type: 'course',
        date: new Date(),
        name: 'Test course',
        verification_code: '9soPa69jP3',
      },
      {
        certificate_type: 'coder_of_the_month',
        date: new Date(),
        verification_code: 'dodp874598',
      },
      {
        certificate_type: 'coder_of_the_month_female',
        date: new Date(),
        verification_code: 'gkspa12345',
      },
    ],
    location: 'https://omegaup.com/',
  };

  it('Should handle an empty table', () => {
    const wrapper = shallowMount(certificate_Mine, {
      propsData: {
        certificates: [] as types.CertificateListItem[],
      },
    });

    expect(wrapper.text()).toContain(T.certificateListMineTitle);
    expect(wrapper.find('table').exists()).toBeFalsy();
    expect(wrapper.text()).toContain(T.certificateListMineCertificatesEmpty);
  });

  it('Should handle a table with data', () => {
    const wrapper = mount(certificate_Mine, {
      propsData,
    });

    expect(wrapper.text()).toContain(T.certificateListMineTitle);
    expect(wrapper.find('table').exists()).toBeTruthy();
    expect(wrapper.text()).toContain('Test contest');
    expect(wrapper.text()).toContain('Test course');
    expect(wrapper.text()).toContain(T.certificateListMineCoderOfTheMonth);
    expect(wrapper.text()).toContain(
      T.certificateListMineCoderOfTheMonthFemale,
    );
    expect(wrapper.text()).toContain(T.certificateListMineDownload);
    expect(wrapper.text()).toContain(T.certificateListMineCopyToClipboard);
  });

  it('Should download a file', async () => {
    const clickSpy = jest.spyOn(
      (certificate_Mine as any).options.methods,
      'getDownloadLink',
    );

    const wrapper = mount(certificate_Mine, {
      propsData,
    });

    await wrapper.findAll('a').trigger('click');

    await wrapper.vm.$nextTick();

    expect(clickSpy).toHaveBeenCalledTimes(propsData['certificates'].length);
    propsData['certificates'].forEach((certificate) => {
      expect(clickSpy).toHaveBeenCalledWith(certificate.verification_code);
    });

    clickSpy.mockRestore();
  });
});
