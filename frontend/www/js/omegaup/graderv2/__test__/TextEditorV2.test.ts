import { shallowMount, createLocalVue, Wrapper } from '@vue/test-utils';
import TextEditorV2 from '../TextEditorV2.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import T from '../../lang';

// 1. Mock lodash so debounce executes synchronously during tests
jest.mock('lodash', () => ({
  debounce: jest.fn((fn) => {
    const debounced = (...args: any[]) => fn(...args);
    debounced.cancel = jest.fn();
    return debounced;
  }),
}));

// 2. Mock the store
jest.mock('../../grader/GraderStore', () => ({
  __esModule: true,
  default: {
    getters: {},
    dispatch: jest.fn(),
  },
}));

import store from '../../grader/GraderStore';

const localVue = createLocalVue();
localVue.component('FontAwesomeIcon', FontAwesomeIcon);
localVue.directive('clipboard', {
  bind: () => {},
  unbind: () => {},
});

describe('TextEditorV2.vue', () => {
  let wrapper: Wrapper<TextEditorV2>;

  const defaultProps = {
    storeMapping: { contents: 'testContents', module: 'testModule' },
    extension: 'out',
    module: 'defaultModule',
    readOnly: false,
  };

  beforeEach(() => {
    Object.assign(store.getters, {
      'theme': 'vs-dark',
      'testContents': 'Initial store content\nLine 2',
      'testModule': 'my_program',
    });
    (store.dispatch as jest.Mock).mockClear();
  });

  afterEach(() => {
    if (wrapper) wrapper.destroy();
    jest.clearAllMocks();
  });

  const createWrapper = (propsOverrides = {}) => {
    return shallowMount(TextEditorV2, {
      localVue,
      propsData: { ...defaultProps, ...propsOverrides },
    });
  };

  it('renders correctly and matches the theme', () => {
    wrapper = createWrapper();
    expect(wrapper.classes()).toContain('text-editor-wrapper');
    expect(wrapper.classes()).toContain('vs-dark');
  });

  it('computes the filename correctly using storeMapping', () => {
    wrapper = createWrapper();
    expect((wrapper.vm as any).filename).toBe('my_program.out');
  });

  it('computes the filename correctly using the prop fallback', () => {
    wrapper = createWrapper({
      storeMapping: { contents: 'testContents' },
    });
    expect((wrapper.vm as any).filename).toBe('defaultModule.out');
  });

  it('dynamically assigns icons and colors based on extension', () => {
    const errWrapper = createWrapper({ extension: 'err' });
    expect((errWrapper.vm as any).fileIcon).toBe('exclamation-triangle');
    expect((errWrapper.vm as any).iconClass).toBe('icon-danger');
    errWrapper.destroy();

    const inWrapper = createWrapper({ extension: 'in' });
    expect((inWrapper.vm as any).fileIcon).toBe('keyboard');
    expect((inWrapper.vm as any).iconClass).toBe('icon-success');
    inWrapper.destroy();
  });

  it('calculates the line count and displays the badge', () => {
    wrapper = createWrapper();
    expect((wrapper.vm as any).lineCount).toBe(2);

    const badge = wrapper.find('.line-badge');
    expect(badge.exists()).toBe(true);
    expect(badge.text()).toBe('2 L');
  });

  it('syncs text to the Vuex store using debounce', () => {
    wrapper = createWrapper();
    const textarea = wrapper.find('.te-textarea');

    (textarea.element as HTMLTextAreaElement).value = 'New user input';
    textarea.trigger('input');

    expect((wrapper.vm as any).localContents).toBe('New user input');

    // Because lodash is mocked, the dispatch runs immediately!
    expect(store.dispatch).toHaveBeenCalledWith(
      'testContents',
      'New user input',
    );
  });

  it('respects readOnly mode', () => {
    wrapper = createWrapper({ readOnly: true });
    const textarea = wrapper.find('.te-textarea');

    expect(textarea.attributes('disabled')).toBe('disabled');

    textarea.trigger('input');

    // Even with immediate execution, it shouldn't dispatch because readOnly is true
    expect(store.dispatch).not.toHaveBeenCalled();
  });

  it('truncates massive strings to prevent browser crashes', () => {
    store.getters['testContents'] = 'A'.repeat(150000);

    wrapper = createWrapper();

    const localContent = (wrapper.vm as any).localContents;
    const MAX_RENDER_LENGTH = 100000;

    expect(localContent.length).toBeLessThan(150000);
    expect(
      localContent.endsWith(
        T.textEditorOutputTruncated ||
          '\n\n... [OUTPUT TRUNCATED: Exceeded character limit]',
      ),
    ).toBe(true);
    expect(localContent.startsWith('A'.repeat(MAX_RENDER_LENGTH))).toBe(true);
  });
});
