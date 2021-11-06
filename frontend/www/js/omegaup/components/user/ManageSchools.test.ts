import { mount } from '@vue/test-utils';
import { types } from '../../api_types';
import userManageSchools from './ManageSchools.vue';
import omegaupRadioSwitch from '../RadioSwitch.vue';
import datePicker from '../DatePicker.vue';

const profile: types.UserProfileInfo = {
  classname: 'user-rank-unranked',
  verified: true,
  hide_problem_tags: false,
  is_private: false,
  programming_languages: {
    py2: 'python2',
    rb: 'ruby',
  },
  rankinfo: {
    name: 'Test',
    problems_solved: 2,
    rank: 1,
  },
  is_own_profile: true,
  school: 'escuela',
  school_id: 1,
  scholar_degree: '',
};

describe('ManageSchools.vue', () => {
  it('Should enable graduation date', async () => {
    const wrapper = mount(userManageSchools, {
      propsData: { profile },
    });

    expect(wrapper.findComponent(datePicker).element).toBeDisabled();
    await wrapper
      .findComponent(omegaupRadioSwitch)
      .findAll('input')
      .at(1)
      .setChecked();
    expect(wrapper.findComponent(datePicker).element).toBeEnabled();
  });

  it('Should emit user update schools', async () => {
    const wrapper = mount(userManageSchools, {
      propsData: { profile },
    });

    await wrapper.find('select').findAll('option').at(8).setSelected();
    await wrapper
      .findComponent(omegaupRadioSwitch)
      .findAll('input')
      .at(1)
      .setChecked();
    await wrapper.findComponent(datePicker).setValue('2010-10-10');

    await wrapper.find('button[type="submit"]').trigger('submit');
    expect(wrapper.emitted('update-user-schools')).toBeDefined();
    expect(wrapper.emitted('update-user-schools')?.[0]).toEqual([
      {
        graduationDate: new Date('2010-10-10'),
        schoolId: 1,
        schoolName: 'escuela',
        scholarDegree: 'bachelors',
      },
    ]);
  });
});
