import { mount, shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';

import T from '../../lang';

import Form from './Form.vue';

const props: types.ProblemFormPayload = {
  title: 'title',
  alias: 'title',
  validator: 'token',
  emailClarifications: false,
  source: 'title',
  visibility: 0,
  statusError: '',
  allowUserAddTags: true,
  showDiff: 'none',
  timeLimit: 1000,
  validatorTimeLimit: 1000,
  overallWallTimeLimit: '',
  extraWallTime: 0,
  outputLimit: 10240,
  inputLimit: 10240,
  memoryLimit: 32768,
  levelTags: [],
  visibilityStatuses: {},
  languages:
    'c11-clang,c11-gcc,cpp11-clang,cpp11-gcc,cpp17-clang,cpp17-gcc,cs,hs,java,lua,pas,py2,py3,rb',
  tags: [
    {
      name: 'problemLevelBasicKarel',
    },
  ],
  problem_level: '',
  publicTags: ['problemTagInputAndOutput'],

  validatorTypes: {
    'token-caseless': 'Token por token, ignorando mayúsculas/minúsculas',
    'token-numeric': 'Tokens numéricos con tolerancia de 1e-9',
    token: 'Token por Token',
    literal: 'Interpretar salida estándar como puntaje',
    custom: 'Validador personalizado (validator.$lang$)',
  },
  validLanguages: {
    'c11-clang,c11-gcc,cpp11-clang,cpp11-gcc,cpp17-clang,cpp17-gcc,cs,hs,java,lua,pas,py2,py3,rb':
      'C, C++, C++11, C#, Haskell, Java, Pascal, Python, Ruby, Lua',
    'kj,kp': 'Karel',
    cat: 'Solo Salida',
    '': 'Lectura (Sin envíos)',
  },
};

describe('Settings.vue', () => {
  it('Should handle problem settings', () => {
    const wrapper = shallowMount(Form, { propsData: { data: props } });

    expect(
      wrapper.find('select[name="languages"]').findAll('option').at(0).text(),
    ).toContain('C, C++, C++11, C#, Haskell, Java, Pascal, Python, Ruby, Lua');
    expect(
      wrapper.find('select[name="languages"]').findAll('option').at(1).text(),
    ).toContain('Karel');
    expect(
      wrapper.find('select[name="languages"]').findAll('option').at(2).text(),
    ).toContain(T.wordsJustOutput);
    expect(
      wrapper.find('select[name="languages"]').findAll('option').at(3).text(),
    ).toContain(T.wordsNoSubmissions);
  });

  it('Should show a collapsed group', async () => {
    const wrapper = mount(Form, { propsData: { data: props } });

    expect(wrapper.find('.limits').classes()).not.toContain('show');
    await wrapper.find('button[type="submit"]').trigger('click');
    setTimeout(() => {
      expect(wrapper.find('.limits').classes()).not.toContain('show');
    }, 3000);
  });
});
