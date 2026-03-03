import { mount } from '@vue/test-utils';
import { dao, types } from '../../api_types';
import user_Basic_Information_Edit from './BasicInformationEdit.vue';
import date_Picker from '../DatePicker.vue';
import T from '../../lang';

const profile: types.UserProfileInfo = {
  name: 'omegaUp admin',
  classname: 'user-rank-unranked',
  email: 'admin@omegaup.com',
  username: 'omegaup',
  verified: true,
  hide_problem_tags: false,
  is_private: false,
  preferred_language: 'py2',
  programming_languages: {
    py2: 'python2',
  },
  rankinfo: {
    name: 'Test',
    problems_solved: 2,
    rank: 1,
  },
  is_own_profile: true,
  birth_date: new Date('1999-09-09'),
  locale: 'es',
  gender: 'decline',
};

const basicInformationEditProps: {
  profile: types.UserProfileInfo;
  data: types.UserProfileDetailsPayload;
  countries: dao.Countries[];
} = {
  profile,
  data: {
    countries: [
      {
        country_id: 'MX',
        name: 'México',
      },
      {
        country_id: 'CA',
        name: 'Canada',
      },
    ],
    programmingLanguages: { py2: 'Python 2', py3: 'Python 3' },
    profile: profile,
    identities: [],
  },
  countries: [
    {
      country_id: 'MX',
      name: 'México',
    },
    {
      country_id: 'CA',
      name: 'Canada',
    },
  ],
};

describe('BasicInformationEdit.vue', () => {
  it('Should disable states select when no country is selected', () => {
    const wrapper = mount(user_Basic_Information_Edit, {
      propsData: basicInformationEditProps,
    });
    expect(wrapper.find('select[data-states]').element).toBeDisabled();
  });

  it('Should enable states select when country is selected', async () => {
    const wrapper = mount(user_Basic_Information_Edit, {
      propsData: basicInformationEditProps,
    });
    await wrapper
      .find('select[data-countries]')
      .find('option[value="CA"]')
      .setSelected();
    expect(wrapper.find('select[data-countries]').element).toBeEnabled();
  });

  it('Should emit user update basic information', async () => {
    const wrapper = mount(user_Basic_Information_Edit, {
      propsData: basicInformationEditProps,
    });

    await wrapper.find('input[data-username]').setValue('omegaup_modified');
    await wrapper.find('input[data-name]').setValue('omegaUp admin modified');
    await wrapper.findComponent(date_Picker).setValue('2001-01-01');
    await wrapper
      .find('select[data-gender]')
      .find('option[value="other"]')
      .setSelected();
    await wrapper
      .find('select[data-countries]')
      .find('option[value="CA"]')
      .setSelected();
    await wrapper
      .find('select[data-states]')
      .find('option[value="AB"]')
      .setSelected();

    await wrapper.find('button[type="submit"]').trigger('submit');
    expect(wrapper.emitted('update-user-basic-information')).toBeDefined();
    expect(wrapper.emitted('update-user-basic-information')).toEqual([
      [
        {
          username: 'omegaup_modified',
          name: 'omegaUp admin modified',
          gender: 'other',
          country_id: 'CA',
          state_id: 'AB',
          birth_date: new Date('2001-01-01'),
        },
      ],
    ]);
  });

  it('Should show message error when a long name is given', async () => {
    const wrapper = mount(user_Basic_Information_Edit, {
      propsData: basicInformationEditProps,
    });

    await wrapper
      .find('input[data-name]')
      .setValue(
        'A name that exceeds the allowed limit of characters for this field',
      );

    await wrapper.find('button[type="submit"]').trigger('submit');

    expect(
      wrapper.emitted('update-user-basic-information-error'),
    ).toBeDefined();
    expect(wrapper.emitted('update-user-basic-information-error')).toEqual([
      [
        {
          description: T.userEditNameTooLong,
        },
      ],
    ]);
  });

  it('Should show error when username is invalid', async () => {
    const wrapper = mount(user_Basic_Information_Edit, {
      propsData: basicInformationEditProps,
    });

    // Test invalid characters
    await wrapper.find('input[data-username]').setValue('invalid@username');
    await wrapper.find('button[type="submit"]').trigger('submit');
    expect(wrapper.emitted('update-user-basic-information-error')).toEqual([
      [{ description: T.parameterInvalidAlias }],
    ]);
    expect(wrapper.find('input[data-username]').classes()).toContain(
      'is-invalid',
    );

    // Test too short username
    await wrapper.find('input[data-username]').setValue('a');
    expect(wrapper.find('input[data-username]').classes()).toContain(
      'is-invalid',
    );

    // Test empty username
    await wrapper.find('input[data-username]').setValue('');
    expect(wrapper.find('input[data-username]').classes()).toContain(
      'is-invalid',
    );
  });
});
