import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import user_Preferences_Edit from './PreferencesEdit.vue';

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
    rb: 'ruby',
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

describe('PreferencesEdit.vue', () => {
  it('Should display user email', () => {
    const wrapper = shallowMount(user_Preferences_Edit, {
      propsData: { profile },
    });
    expect(wrapper.find('[data-email]').text()).toContain('admin@omegaup.com');
  });

  it('Should emit user update preferences', async () => {
    const wrapper = shallowMount(user_Preferences_Edit, {
      propsData: { profile },
    });

    await wrapper
      .find('select[data-locale]')
      .find('option[value="en"]')
      .setSelected();
    await wrapper
      .find('select[data-preferred-language]')
      .find('option[value="rb"]')
      .setSelected();
    await wrapper.find('input[data-is-private]').setChecked();
    await wrapper.find('input[data-hide-problem-tags]').setChecked();

    await wrapper.find('button[type="submit"]').trigger('submit');
    expect(wrapper.emitted('update-user-preferences')).toBeDefined();
    expect(wrapper.emitted('update-user-preferences')).toEqual([
      [
        {
          userPreferences: {
            locale: 'en',
            preferred_language: 'rb',
            is_private: true,
            hide_problem_tags: true,
          },
          localeChanged: true,
        },
      ],
    ]);
  });
});
