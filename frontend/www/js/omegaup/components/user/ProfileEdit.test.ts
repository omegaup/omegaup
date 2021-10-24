import { mount } from '@vue/test-utils';
import { types } from '../../api_types';
import user_Profile_Edit from './ProfileEdit.vue';
import date_Picker from '../DatePicker.vue';

const profile = {
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
} as types.UserProfileInfo;

const profileEditProps = {
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
  } as types.UserProfileEditDetailsPayload,
};

describe('ProfileEdit.vue', () => {
  it('Should disable states select when no country is selected', () => {
    const wrapper = mount(user_Profile_Edit, {
      propsData: profileEditProps,
    });
    expect(wrapper.find('select[data-states]').element).toBeDisabled();
  });

  it('Should enable states select when country is selected', () => {
    const wrapper = mount(user_Profile_Edit, {
      propsData: profileEditProps,
    });
    wrapper
      .find('select[data-countries]')
      .findAll('option')
      .at(1)
      .setSelected();
    expect(wrapper.find('select[data-countries]').element).toBeEnabled();
  });

  it('Should disable graduation date when no school is specified', () => {
    const wrapper = mount(user_Profile_Edit, {
      propsData: profileEditProps,
    });
    expect(wrapper.findAllComponents(date_Picker).at(1).element).toBeDisabled();
  });

  it('Should enable graduation date when school is specified', () => {
    profileEditProps.profile.school = 'itsur';
    const wrapper = mount(user_Profile_Edit, {
      propsData: profileEditProps,
    });
    expect(wrapper.findAllComponents(date_Picker).at(1).element).toBeEnabled();
  });

  it('Should emit user update', () => {
    const wrapper = mount(user_Profile_Edit, {
      propsData: profileEditProps,
    });
    wrapper.find('button[type="submit"]').trigger('submit');
    expect(wrapper.emitted('update-user')).toBeDefined();

    //argument 'locale_changed' should be false
    expect(wrapper.emitted('update-user')[0][1]).toEqual(false);
  });
});
