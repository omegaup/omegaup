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
    cat: T.wordsJustOutput,
    '': T.wordsNoSubmissions,
  },
};

describe('Settings.vue', () => {
  it('Should handle problem settings', () => {
    const wrapper = shallowMount(Form, { propsData: { data: props } });

    const optionsWrapper = wrapper
      .find('select[name="languages"]')
      .findAll('option');

    const optionsObject: { [key: string]: string } = {};
    optionsWrapper.wrappers.forEach((option) => {
      const value = option.attributes('value');
      if (typeof value === 'string') {
        optionsObject[value] = option.text();
      }
    });
    expect(props.validLanguages).toEqual(optionsObject);
  });

  it('Should show a collapsed group', async () => {
    const wrapper = mount(Form, { propsData: { data: props } });

    expect(wrapper.find('.limits').classes()).not.toContain('show');
    await wrapper.find('button[type="submit"]').trigger('click');
    setTimeout(() => {
      expect(wrapper.find('.limits').classes()).not.toContain('show');
    }, 3000);
  });
  it('Should open collapsed tabs automatically when submitting without completing the required info', async () => {
    const wrapper = mount(Form, { propsData: { data: props } });
    expect(wrapper.find('.basic-info').classes()).toContain('show');
    expect(wrapper.find('.tags').classes()).toContain('show');
    await wrapper.find('button[data-target=".basic-info"]').trigger('click');
    await wrapper.find('button[data-target=".basic-info"]').trigger('click');
    setTimeout(() => {
      expect(wrapper.find('.basic-info').classes()).not.toContain('show');
      expect(wrapper.find('.tags').classes()).not.toContain('show');
    }, 3000);
    await wrapper.find('button[type="submit"]').trigger('click');
    setTimeout(() => {
      expect(wrapper.find('.basic-info').classes()).toContain('show');
      expect(wrapper.find('.tags').classes()).toContain('show');
    }, 3000);
  });
});
