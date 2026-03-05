import { shallowMount, createLocalVue } from '@vue/test-utils';
import MonacoEditor from './MonacoEditor.vue';

// 1. Mock Monaco Editor (Crucial for Jest/JSDOM)
jest.mock('monaco-editor', () => ({
  editor: {
    create: jest.fn(() => ({
      getModel: jest.fn(() => ({
        getValue: jest.fn(() => 'console.log("Hello World");'),
        setValue: jest.fn(),
        onDidChangeContent: jest.fn(),
        dispose: jest.fn(),
      })),
      updateOptions: jest.fn(),
      layout: jest.fn(),
      dispose: jest.fn(),
    })),
  },
}));

// 2. Mock the GraderStore
jest.mock('./GraderStore', () => ({
  getters: {
    theme: 'vs-dark',
    'mockMapping.language': 'javascript',
    'mockMapping.module': 'main',
    'mockMapping.contents': 'console.log("Hello World");',
  },
  dispatch: jest.fn(),
}));

// 3. Mock Utilities and Translations
jest.mock('./util', () => ({
  supportedLanguages: {
    javascript: { extension: 'js', modelMapping: 'javascript' },
  },
}));

jest.mock('../lang', () => ({
  fontSize: 'Font Size',
}));

const localVue = createLocalVue();

describe('MonacoEditor.vue', () => {
  let wrapper: any;

  beforeEach(() => {
    // Suppress ResizeObserver error in JSDOM
    global.ResizeObserver = class {
      observe() {}
      unobserve() {}
      disconnect() {}
    } as any;

    wrapper = shallowMount(MonacoEditor, {
      localVue,
      propsData: {
        storeMapping: {
          contents: 'mockMapping.contents',
          language: 'mockMapping.language',
          module: 'mockMapping.module',
        },
        readOnly: false,
      },
    });
  });

  afterEach(() => {
    wrapper.destroy();
    jest.clearAllMocks();
  });

  it('renders correctly and displays the filename', () => {
    expect(wrapper.find('.monaco-root').exists()).toBe(true);
    expect(wrapper.find('.toolbar-filename').text()).toContain('main.js');
  });

  it('toggles fullscreen mode when the method is called', async () => {
    expect(wrapper.vm.isFullscreen).toBe(false);
    
    wrapper.vm.toggleFullscreen();
    await wrapper.vm.$nextTick();
    
    expect(wrapper.vm.isFullscreen).toBe(true);
    expect(wrapper.classes()).toContain('monaco-root--fullscreen');
    expect(document.body.style.overflow).toBe('hidden');
  });

  it('opens the reset modal when confirmReset is triggered', async () => {
    // Force a difference between default and current contents to simulate changes
    wrapper.vm.defaultContents = 'old code';
    
    wrapper.vm.confirmReset();
    await wrapper.vm.$nextTick();
    
    const modal = wrapper.find('.modal-overlay');
    expect(modal.exists()).toBe(true);
  });

  it('resets contents to default when resetToDefault is called', async () => {
    wrapper.vm.defaultContents = 'original code';
    wrapper.vm.showResetModal = true;

    wrapper.vm.resetToDefault();
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.showResetModal).toBe(false);
    expect(wrapper.vm.contents).toBe('original code');
  });
});