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
      propsData: {
        profile,
        searchResultSchools: [{ key: 'teams-group', value: 'teams group' }],
      },
    });

    expect(wrapper.findComponent(datePicker).element).toBeDisabled();
    await wrapper
      .findComponent(omegaupRadioSwitch)
      .find('input[value="false"]')
      .setChecked();
    expect(wrapper.findComponent(datePicker).element).toBeEnabled();
  });

  it('Should emit user update schools', async () => {
    const wrapper = mount(userManageSchools, {
      propsData: {
        profile,
        searchResultSchools: [{ key: 1, value: 'escuela' }],
      },
    });

    await wrapper
      .find('select')
      .find('option[value="bachelors"]')
      .setSelected();
    await wrapper
      .findComponent(omegaupRadioSwitch)
      .find('input[value="false"]')
      .setChecked();
    await wrapper.findComponent(datePicker).setValue('2010-10-10');

    await wrapper.find('button[type="submit"]').trigger('submit');
    const emitted = wrapper.emitted('update-user-schools');
    expect(emitted).toBeDefined();
    expect(emitted).toHaveLength(1);
    // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
    expect(emitted![0]).toHaveLength(1);
    // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
    const userSchoolData = emitted![0][0];
    expect(userSchoolData.school_id).toBe(1);
    expect(userSchoolData.school_name).toBe('escuela');
    expect(userSchoolData.scholar_degree).toBe('bachelors');
    const expectedDate = new Date(2010, 9, 10); // October 10, 2010 at midnight local time
    const actualDate = userSchoolData.graduation_date;
    expect(actualDate.getFullYear()).toBe(expectedDate.getFullYear());
    expect(actualDate.getMonth()).toBe(expectedDate.getMonth());
    expect(actualDate.getDate()).toBe(expectedDate.getDate());
  });
});
