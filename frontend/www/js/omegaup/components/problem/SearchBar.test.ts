import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import problem_SearchBar from './SearchBar.vue';

describe('SearchBar.vue', () => {
  it('Should handle empty initial values', async () => {
    const languages: { [key: string]: string } = {
      all: T.wordsAll,
      en: T.wordsEnglish,
      es: T.wordsSpanish,
      pt: T.wordsPortuguese,
    };
    const wrapper = shallowMount(problem_SearchBar, {
      propsData: {
        columns: ['title', 'quality', 'difficulty'],
        initialColumn: '',
        initialMode: '',
        initialLanguage: '',
        initialKeyword: '',
        languages: Object.keys(languages),
        modes: ['asc', 'desc'],
        tags: [],
      },
    });

    expect(wrapper.text()).toContain(T.wordsFilterByLanguage);

    for (const [key, language] of Object.entries(languages)) {
      expect(wrapper.find(`select>option[value="${key}"]`).text()).toBe(
        language,
      );
    }
  });

  it('Should handle the "only quality seal problems" checkbox', async () => {
    const wrapper = shallowMount(problem_SearchBar, {
      propsData: {
        tags: [],
        keyword: '',
        language: '',
        languages: ['all', 'en', 'es', 'pt'],
        onlyQualitySeal: false,
        searchResultProblems: [],
      },
    });

    const checkboxOnlyQualitySeal = wrapper.find(
      'input[name="only_quality_seal"]',
    );
    expect(
      (checkboxOnlyQualitySeal.element as HTMLInputElement).checked,
    ).toBeFalsy();
    await checkboxOnlyQualitySeal.setChecked();
    expect(
      (checkboxOnlyQualitySeal.element as HTMLInputElement).checked,
    ).toBeTruthy();
    await wrapper.find('input[type="submit"]').trigger('click');
  });
});
