import { mount, shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';

import T from '../../lang';
import * as ui from '../../ui';

import certificate_Mine from './Mine.vue';

interface Mine {
  options: {
    methods: {
      getVerificationLink: (verificationCode: string) => string;
      getDownloadLink: (verificationCode: string) => string;
    };
  };
}

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
        name: null,
        verification_code: 'dodp874598',
      },
      {
        certificate_type: 'coder_of_the_month_female',
        date: new Date(),
        name: null,
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
    expect(wrapper.find('table tbody').text()).toContain(
      ui.formatString(T.certificateListMineContest, {
        contest_title: 'Test contest',
      }),
    );
    expect(wrapper.find('table tbody').text()).toContain(
      ui.formatString(T.certificateListMineCourse, {
        course_name: 'Test course',
      }),
    );
    expect(wrapper.find('table tbody').text()).toContain(
      T.certificateListMineCoderOfTheMonth,
    );
    expect(wrapper.find('table tbody').text()).toContain(
      T.certificateListMineCoderOfTheMonthFemale,
    );
  });

  it('Should copy the verification code', () => {
    const defineSpy = jest.spyOn(
      (certificate_Mine as unknown as Mine).options.methods,
      'getVerificationLink',
    );

    shallowMount(certificate_Mine, {
      propsData,
    });

    expect(defineSpy).toHaveBeenCalledTimes(
      propsData['certificates'].length * 2,
    );
    propsData['certificates'].forEach((certificate) => {
      expect(defineSpy).toHaveBeenCalledWith(certificate.verification_code);
    });

    defineSpy.mockRestore();
  });

  it('Should download a file', async () => {
    const clickSpy = jest.spyOn(
      (certificate_Mine as unknown as Mine).options.methods,
      'getDownloadLink',
    );

    const wrapper = mount(certificate_Mine, {
      propsData,
    });

    await wrapper.findAll('a[download-file]').trigger('click');

    await wrapper.vm.$nextTick();

    expect(clickSpy).toHaveBeenCalledTimes(propsData['certificates'].length);
    propsData['certificates'].forEach((certificate) => {
      expect(clickSpy).toHaveBeenCalledWith(certificate.verification_code);
    });

    clickSpy.mockRestore();
  });
});
