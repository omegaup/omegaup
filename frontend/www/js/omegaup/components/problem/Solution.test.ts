import { mount } from '@vue/test-utils';

import type { types } from '../../api_types';
import T from '../../lang';

import problem_Solution from './Solution.vue';

describe('Solution.vue', () => {
  it('Should handle an empty/locked solution', () => {
    const wrapper = mount(problem_Solution, {
      propsData: {
        solution: null as types.ProblemStatement | null,
        status: 'locked',
        availableTokens: 0,
        allTokens: 0,
        isDisabled: false,
      },
    });

    expect(wrapper.text()).toContain(T.solutionLocked.split('\n')[0]);
  });

  it('Should handle an empty/unlocked solution', () => {
    const wrapper = mount(problem_Solution, {
      propsData: {
        solution: null as types.ProblemStatement | null,
        status: 'unlocked',
        availableTokens: 0,
        allTokens: 0,
        isDisabled: false,
      },
    });

    expect(wrapper.text()).toContain(T.solutionConfirm);
  });

  it('Should handle a non-empty, unlocked solution', () => {
    const wrapper = mount(problem_Solution, {
      propsData: {
        solution: {
          markdown: 'Hello, World!',
          images: {},
        } as types.ProblemStatement | null,
        status: 'unlocked',
        availableTokens: 0,
        allTokens: 0,
        isDisabled: false,
      },
    });

    expect(wrapper.text()).toContain('Hello, World!');
  });

  it('Should handle unrecognized source filename error', () => {
    const wrapper = mount(problem_Solution, {
      propsData: {
        solution: {
          markdown: `# test with embed code in solution
Here we can add code.
<details>
  <summary>
    Example:
  </summary>

  {{sample.cpp}}

  </details>`,
          sources: {},
        } as types.ProblemStatement,
        status: 'unlocked',
        availableTokens: 0,
        allTokens: 0,
        isDisabled: false,
      },
    });

    expect(wrapper.find('div[data-markdown-statement]').text()).toContain(
      'Unrecognized source filename: sample.cpp',
    );
  });

  it('Should handle a valid source filename with content', async () => {
    const wrapper = mount(problem_Solution, {
      propsData: {
        solution: {
          markdown: `# test with embed code in solution
Here we can add code.
<details>
  <summary>
    Example:
  </summary>

  {{sample.cpp}}

  </details>`,
          sources: {
            'sample.cpp': `#include <iostream>

      int main() {
        std::cout << "This is only an example";
        return 0;
      }`,
          },
          images: {},
          language: 'en',
        } as types.ProblemStatement,
        status: 'unlocked',
        availableTokens: 0,
        allTokens: 0,
        isDisabled: false,
      },
    });

    expect(wrapper.find('details').attributes()).toMatchObject({});
    await wrapper.find('details > summary').trigger('click');
    expect(wrapper.find('details').attributes()).toMatchObject({ open: '' });
    expect(wrapper.find('div[data-markdown-statement]').text()).toContain(
      '#include <iostream>',
    );
    expect(wrapper.find('div[data-markdown-statement]').text()).toContain(
      'This is only an example',
    );
  });
});
