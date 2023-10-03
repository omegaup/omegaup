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
        name: 'Contest',
        verification_code: 'k2s8OPl620',
      },
      {
        certificate_type: 'course',
        date: new Date(),
        name: 'Course',
        verification_code: '9soPa69jP3',
      },
    ],
  };

  it('Should handle an empty table', async () => {
    const wrapper = shallowMount(certificate_Mine, {
      propsData: {
        certificates: [] as types.CertificateListItem[],
      },
    });

    expect(wrapper.text()).toContain(T.certificateListMineTitle);
    expect(wrapper.find('table').exists()).toBeFalsy();
    expect(wrapper.text()).toContain(T.certificateListMineCertificatesEmpty);
  });

  it('Should handle a table with data', async () => {
    const wrapper = mount(certificate_Mine, {
      propsData,
    });

    expect(wrapper.text()).toContain(T.certificateListMineTitle);
    expect(wrapper.find('table').exists()).toBeTruthy();
    expect(wrapper.text()).toContain('Contest');
    expect(wrapper.text()).toContain('Course');
    expect(wrapper.text()).toContain(T.certificateListMineDownload);
    expect(wrapper.text()).toContain(T.certificateListMineCopyToClipboard);
  });

  it('Should handle download pdf certificates', async () => {
    const wrapper = mount(certificate_Mine, {
      propsData,
    });

    await wrapper.findAll('button').trigger('click');
    expect(wrapper.emitted('download-pdf-certificate')).toBeDefined();
    expect(wrapper.emitted('download-pdf-certificate')).toHaveLength(2);
    expect(wrapper.emitted('download-pdf-certificate')).toEqual([
      [
        {
          verificationCode: 'k2s8OPl620',
          name: 'Contest',
        },
      ],
      [
        {
          verificationCode: '9soPa69jP3',
          name: 'Course',
        },
      ],
    ]);
  });
});
