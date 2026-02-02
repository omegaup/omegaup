import { shallowMount } from '@vue/test-utils';

import omegaup_Markdown from './Markdown.vue';
import T from '../lang';
import * as ui from '../ui';

jest.mock('../ui', () => {
  const actual = jest.requireActual('../ui');
  return {
    __esModule: true,
    ...actual,
    copyToClipboard: jest.fn(),
  };
});

describe('Markdown.vue', () => {
  beforeEach(() => {
    (ui.copyToClipboard as jest.Mock).mockClear();
  });

  it('Should render markdown contents', () => {
    const wrapper = shallowMount(omegaup_Markdown, {
      propsData: {
        markdown: '_Hello_, **World**!',
      },
    });
    expect(wrapper.html()).toEqual(
      expect.stringContaining('<p><em>Hello</em>, <strong>World</strong>!</p>'),
    );
  });

  it('Does not inject copy button for non-token messages', () => {
    const wrapper = shallowMount(omegaup_Markdown, {
      propsData: {
        markdown: 'Just a regular notification message.',
      },
    });

    expect(wrapper.find('button').exists()).toBe(false);
  });

  it('Injects copy button and copies token for API token notification', async () => {
    const template = (T as any).apiTokenSuccessfullyCreated as string;
    const token = 'abc123token';
    const message = template.replace('%(token)', token);

    const wrapper = shallowMount(omegaup_Markdown, {
      propsData: {
        markdown: message,
      },
    });

    const button = wrapper.find('button');
    expect(button.exists()).toBe(true);

    await button.trigger('click');

    expect(ui.copyToClipboard).toHaveBeenCalledWith(token);

    const hint = wrapper.find('.api-token-copy-hint');
    expect(hint.exists()).toBe(true);
    expect(hint.classes()).not.toContain('d-none');
  });
});
