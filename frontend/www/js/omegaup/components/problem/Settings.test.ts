import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import Vue from 'vue';

import { types } from '../../api_types';
import T from '../../lang';

import problem_Settings from './Settings.vue';

describe('Settings.vue', () => {
  it('Should handle problem settings', () => {
    const wrapper = shallowMount(problem_Settings, {
      propsData: {
        errors: [],
        extraWallTimeLimit: 0,
        initialLanguage:
          'c11-clang,c11-gcc,cpp11-clang,cpp11-gcc,cpp17-clang,cpp17-gcc,cs,hs,java,lua,pas,py2,py3,rb',
        initialValidator: 'token',
        inputLimit: 10240,
        memoryLimit: 32768,
        outputLimit: 10240,
        overallWallTimeLimit: 6000,
        timeLimit: 1000,
        validLanguages: {
          '': T.wordsNoSubmissions,
          'c11-clang,c11-gcc,cpp11-clang,cpp11-gcc,cpp17-clang,cpp17-gcc,cs,hs,java,lua,pas,py2,py3,rb':
            'C, C++, C++11, C#, Haskell, Java, Pascal, Python, Ruby, Lua',
          cat: T.wordsJustOutput,
          'kj,kp': 'Karel',
        },
        validatorTimeLimit: 0,
        validatorTypes: {
          custom: T.problemEditFormCustom,
          literal: T.problemEditFormLiteral,
          token: T.problemEditFormTokenByToken,
          'token-caseless': T.problemEditFormTokenCaseless,
          'token-numeric': T.problemEditFormNumericTokensWithTolerance,
        },
      },
    });

    expect(
      wrapper.find('select[name="languages"]').findAll('option').at(0).text(),
    ).toContain(T.wordsNoSubmissions);
    expect(
      wrapper.find('select[name="languages"]').findAll('option').at(1).text(),
    ).toContain('C, C++, C++11, C#, Haskell, Java, Pascal, Python, Ruby, Lua');
    expect(
      wrapper.find('select[name="languages"]').findAll('option').at(2).text(),
    ).toContain(T.wordsJustOutput);
    expect(
      wrapper.find('select[name="languages"]').findAll('option').at(3).text(),
    ).toContain('Karel');
  });
});
