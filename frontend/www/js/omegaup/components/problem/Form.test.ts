import { shallowMount } from '@vue/test-utils';
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
    'c11-gcc,c11-clang,cpp11-gcc,cpp11-clang,cpp17-gcc,cpp17-clang,cpp20-gcc,cpp20-clang,java,kt,py2,py3,rb,cs,pas,hs,lua,go,rs,js',
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
    'c11-gcc,c11-clang,cpp11-gcc,cpp11-clang,cpp17-gcc,cpp17-clang,cpp20-gcc,cpp20-clang,java,kt,py2,py3,rb,cs,pas,hs,lua,go,rs,js':
      'C, C++, C#, Java, Kotlin, Python, Ruby, Pascal, Haskell, Lua, Go, Rust, JavaScript',
    'kj,kp': 'Karel',
    cat: T.wordsJustOutput,
    '': T.wordsNoSubmissions,
  },
};

// TODO: Add tests that simulates user interaction
describe('Settings.vue', () => {
  it('Should generate alias with accented and multilingual text', () => {
    const wrapper = shallowMount(Form, { propsData: { data: props } });

    const cases = [
      { title: 'Árbol ñandú 中文', alias: 'Arbol-nandu-' },
      { title: 'Æsir and Œuvre', alias: 'AEsir-and-OEuvre' },
      { title: 'Ꜳrvíztűrő tükörfúrógép', alias: 'AArvizturo-tukorfurogep' },
      { title: 'Crème brûlée déjà vu', alias: 'Creme-brulee-deja-vu' },
      { title: 'São Paulo - año 2026', alias: 'Sao-Paulo---ano-2026' },
    ];

    for (const sample of cases) {
      wrapper.setData({ title: sample.title });
      (wrapper.vm as any).onGenerateAlias();
      expect((wrapper.vm as any).alias).toBe(sample.alias);
    }
  });

  it('Should call the function that opens collapsed panels', async () => {
    // We need to use any here because `.options.methods` is not inside the public API, yet that's the only way
    // to access the method in order to spy on it and check whether or not it has been called.
    const openCollapsed = jest.spyOn(
      (Form as any).options.methods,
      'openCollapsedIfRequired',
    );
    const wrapper = shallowMount(Form, { propsData: { data: props } });
    wrapper.find('[type="submit"]').trigger('click');
    await wrapper.vm.$nextTick();
    expect(openCollapsed).toBeCalled();
  });

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
});
