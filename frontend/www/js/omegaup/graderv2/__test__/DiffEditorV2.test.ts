import { shallowMount } from '@vue/test-utils';
import DiffEditorV2 from '../DiffEditorV2.vue';

// Mock Monaco Editor since it doesn't run in JSDOM
jest.mock('monaco-editor', () => ({
  editor: {
    createModel: jest.fn(),
    createDiffEditor: jest.fn(() => ({
      setModel: jest.fn(),
      layout: jest.fn(),
      dispose: jest.fn(),
    })),
  },
}));

((global as unknown) as {
  ResizeObserver: unknown;
}).ResizeObserver = class ResizeObserver {
  observe(): void {}
  unobserve(): void {}
  disconnect(): void {}
};

describe('DiffEditorV2.vue', () => {
  it('Should mount without crashing', () => {
    const wrapper = shallowMount(DiffEditorV2, {
      propsData: {
        storeMapping: {
          originalContents: 'original',
          modifiedContents: 'modified',
        },
      },
    });
    expect(wrapper.exists()).toBe(true);
  });
});
