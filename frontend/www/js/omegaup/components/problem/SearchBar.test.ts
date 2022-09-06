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

    for (const language of Object.entries(languages)) {
      const value = language[1];
      expect(wrapper.find('select').text()).toContain(value);
    }
  });
});
