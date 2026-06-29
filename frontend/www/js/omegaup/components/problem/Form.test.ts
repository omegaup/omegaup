import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';

import T from '../../lang';
import * as ui from '../../ui';

import Form from './Form.vue';
import { CreationMethods } from './Form.vue';

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

  it('Should show creation method selector when feature flag is enabled', () => {
    const wrapper = shallowMount(Form, {
      propsData: {
        data: props,
        showCreationMethodSelector: true,
      },
    });

    expect(wrapper.find('.introjs-creation-method').exists()).toBe(true);
  });

  it('Should hide creation method selector on update mode', () => {
    const wrapper = shallowMount(Form, {
      propsData: {
        data: props,
        isUpdate: true,
        showCreationMethodSelector: true,
      },
    });

    expect(wrapper.find('.introjs-creation-method').exists()).toBe(false);
  });

  it('Should show open creator button when creator method is selected', async () => {
    const wrapper = shallowMount(Form, {
      propsData: {
        data: props,
        showCreationMethodSelector: true,
      },
    });

    await wrapper.setData({ currentCreationMethod: CreationMethods.Creator });

    expect(wrapper.find('.introjs-open-creator button').exists()).toBe(true);
  });

  it('Should hide open creator button when zip method is selected', async () => {
    const wrapper = shallowMount(Form, {
      propsData: {
        data: props,
        showCreationMethodSelector: true,
      },
    });

    await wrapper.setData({ currentCreationMethod: CreationMethods.Zip });

    expect(wrapper.find('.introjs-open-creator button').exists()).toBe(false);
  });

  it('Should hide separate file input when feature flag is enabled', () => {
    const wrapper = shallowMount(Form, {
      propsData: {
        data: props,
        showCreationMethodSelector: true,
      },
    });

    expect(wrapper.find('.form-group.col-md-6.introjs-file').exists()).toBe(
      false,
    );
  });

  it('Should show zip file input in selector area when zip method is selected', async () => {
    const wrapper = shallowMount(Form, {
      propsData: {
        data: props,
        showCreationMethodSelector: true,
      },
    });

    await wrapper.setData({ currentCreationMethod: CreationMethods.Zip });

    expect(
      wrapper.find('.introjs-creation-method .introjs-file').exists(),
    ).toBe(true);
  });

  it('Should open creator modal when clicking open creator button', async () => {
    const wrapper = shallowMount(Form, {
      propsData: {
        data: props,
        showCreationMethodSelector: true,
      },
    });

    await wrapper.setData({ currentCreationMethod: CreationMethods.Creator });
    await wrapper.find('.introjs-open-creator button').trigger('click');

    expect((wrapper.vm as any).showProblemCreator).toBe(true);
    expect(wrapper.find('.problem-creator-modal').exists()).toBe(true);
  });

  it('Should close creator modal when clicking close button', async () => {
    const wrapper = shallowMount(Form, {
      propsData: {
        data: props,
        showCreationMethodSelector: true,
      },
    });

    await wrapper.setData({ showProblemCreator: true });
    await wrapper.find('[data-problem-creator-close]').trigger('click');

    expect((wrapper.vm as any).showProblemCreator).toBe(false);
  });
});

describe('Form.vue creator modal events', () => {
  async function mountWithOpenCreator() {
    const wrapper = shallowMount(Form, {
      propsData: {
        data: props,
        showCreationMethodSelector: true,
      },
    });
    await wrapper.setData({ showProblemCreator: true });
    return wrapper;
  }

  afterEach(() => {
    jest.restoreAllMocks();
  });

  it('Should show success notification on show-update-success-message', async () => {
    const successSpy = jest
      .spyOn(ui, 'success')
      .mockImplementation(() => undefined);
    const wrapper = await mountWithOpenCreator();

    const creator = wrapper.find('omegaup-problem-creator-stub');
    expect(creator.exists()).toBe(true);
    creator.vm.$emit('show-update-success-message');

    expect(successSpy).toHaveBeenCalledWith(T.problemCreatorUpdateAlert);
  });

  it('Should download a file on download-input-file', async () => {
    const wrapper = await mountWithOpenCreator();

    const link = document.createElement('a');
    const clickSpy = jest
      .spyOn(link, 'click')
      .mockImplementation(() => undefined);
    jest.spyOn(document, 'createElement').mockReturnValue(link);
    (URL as any).createObjectURL = jest.fn(() => 'blob:mock');
    (URL as any).revokeObjectURL = jest.fn();

    wrapper
      .find('omegaup-problem-creator-stub')
      .vm.$emit('download-input-file', {
        fileName: 'case.txt',
        fileContent: '1 2 3',
      });

    expect(clickSpy).toHaveBeenCalled();
    expect(link.download).toBe('case.txt');
  });

  it('Should download a zip on download-zip-file', async () => {
    const wrapper = await mountWithOpenCreator();

    const link = document.createElement('a');
    const clickSpy = jest
      .spyOn(link, 'click')
      .mockImplementation(() => undefined);
    jest.spyOn(document, 'createElement').mockReturnValue(link);
    (URL as any).createObjectURL = jest.fn(() => 'blob:mock');
    (URL as any).revokeObjectURL = jest.fn();

    const zipContent = {
      generateAsync: jest.fn().mockResolvedValue(new Blob(['zip'])),
    };

    wrapper.find('omegaup-problem-creator-stub').vm.$emit('download-zip-file', {
      fileName: 'problem',
      zipContent,
    });
    await wrapper.vm.$nextTick();
    await Promise.resolve();

    expect(zipContent.generateAsync).toHaveBeenCalledWith({ type: 'blob' });
    expect(clickSpy).toHaveBeenCalled();
    expect(link.download).toBe('problem.zip');
  });
});
