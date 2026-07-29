import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';

import T from '../../lang';
import * as ui from '../../ui';

import Form from './Form.vue';
import { CreationMethods } from './Form.vue';
import CreatorWrapper from './CreatorWrapper.vue';

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

  it('Should re-emit show-update-success-message from the creator', async () => {
    const wrapper = shallowMount(Form, {
      propsData: { data: props, showCreationMethodSelector: true },
    });
    await wrapper.setData({ showProblemCreator: true });

    wrapper
      .findComponent(CreatorWrapper)
      .vm.$emit('show-update-success-message');

    expect(wrapper.emitted('show-update-success-message')).toBeTruthy();
  });

  it('Should re-emit download-input-file with its payload', async () => {
    const wrapper = shallowMount(Form, {
      propsData: { data: props, showCreationMethodSelector: true },
    });
    await wrapper.setData({ showProblemCreator: true });

    const payload = { fileName: 'cases/1.in', fileContent: 'content' };
    wrapper
      .findComponent(CreatorWrapper)
      .vm.$emit('download-input-file', payload);

    expect(wrapper.emitted('download-input-file')?.[0]).toEqual([payload]);
  });

  it('Should capture the generated zip from the creator for submission', async () => {
    const wrapper = shallowMount(Form, {
      propsData: { data: props, showCreationMethodSelector: true },
    });
    await wrapper.setData({ showProblemCreator: true });

    const blob = new Blob(['zip'], { type: 'application/zip' });
    const payload = {
      fileName: 'problem',
      zipContent: { generateAsync: jest.fn().mockResolvedValue(blob) },
    };
    wrapper
      .findComponent(CreatorWrapper)
      .vm.$emit('download-zip-file', payload);
    await new Promise(process.nextTick);

    expect(wrapper.emitted('download-zip-file')).toBeFalsy();
    expect((wrapper.vm as any).creatorGeneratedZipBlob).toBe(blob);
    expect((wrapper.vm as any).isGeneratingCreatorZip).toBe(false);
  });

  it('Should block submit in creator mode when no zip has been generated', async () => {
    const uiError = jest.spyOn(ui, 'error').mockImplementation(() => {});
    const wrapper = shallowMount(Form, {
      propsData: { data: props, showCreationMethodSelector: true },
    });
    await wrapper.setData({
      currentCreationMethod: CreationMethods.Creator,
    });

    const ev = { preventDefault: jest.fn() };
    (wrapper.vm as any).handleFormSubmit(ev);

    expect(ev.preventDefault).toHaveBeenCalled();
    expect(uiError).toHaveBeenCalledWith(T.problemCreatorOpenBeforeSubmit);
    uiError.mockRestore();
  });

  it('Should block submit while the creator zip is being generated', async () => {
    const uiError = jest.spyOn(ui, 'error').mockImplementation(() => {});
    const wrapper = shallowMount(Form, {
      propsData: { data: props, showCreationMethodSelector: true },
    });
    await wrapper.setData({
      currentCreationMethod: CreationMethods.Creator,
      isGeneratingCreatorZip: true,
    });

    const ev = { preventDefault: jest.fn() };
    (wrapper.vm as any).handleFormSubmit(ev);

    expect(ev.preventDefault).toHaveBeenCalled();
    expect(uiError).toHaveBeenCalledWith(T.problemCreatorGeneratingZip);
    uiError.mockRestore();
  });

  it('Should attach the generated zip to the hidden input on submit', async () => {
    const addedFiles: File[] = [];
    (window as any).DataTransfer = class {
      items = { add: (file: File) => addedFiles.push(file) };
      get files() {
        return addedFiles;
      }
    };
    const wrapper = shallowMount(Form, {
      propsData: { data: props, showCreationMethodSelector: true },
    });
    await wrapper.setData({
      currentCreationMethod: CreationMethods.Creator,
      creatorGeneratedZipBlob: new Blob(['zip'], { type: 'application/zip' }),
    });

    const fakeInput: { files: FileList | null } = { files: null };
    (wrapper.vm as any).$refs.creatorFileInput = fakeInput;
    const ev = { preventDefault: jest.fn() };
    (wrapper.vm as any).handleFormSubmit(ev);

    expect(ev.preventDefault).not.toHaveBeenCalled();
    expect(addedFiles).toHaveLength(1);
    expect(addedFiles[0].name).toBe('title.zip');
    expect(fakeInput.files).toHaveLength(1);
    delete (window as any).DataTransfer;
  });
});
