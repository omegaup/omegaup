import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import grader_Ephemeral from '../components/grader/Ephemeral.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.EphemeralDetailsPayload();
  const theme = payload.theme;
  const goldenLayoutSettings = {
    settings: {
      showPopoutIcon: false,
    },
    content: [
      {
        type: 'row',
        content: [
          {
            type: 'column',
            id: 'main-column',
            content: [
              {
                type: 'stack',
                id: 'source-and-settings',
                content: [
                  {
                    type: 'component',
                    componentName: 'monaco-editor-component',
                    componentState: {
                      storeMapping: {
                        contents: 'request.source',
                        language: 'request.language',
                        module: 'moduleName',
                      },
                      theme,
                    },
                    id: 'source',
                    isClosable: false,
                  },
                  {
                    type: 'component',
                    componentName: 'settings-component',
                    id: 'settings',
                    componentState: {
                      storeMapping: {},
                      id: 'settings',
                    },
                    isClosable: false,
                  },
                ],
              },
              {
                type: 'stack',
                content: [
                  {
                    type: 'component',
                    componentName: 'text-editor-component',
                    componentState: {
                      storeMapping: {
                        contents: 'compilerOutput',
                      },
                      id: 'compiler',
                      readOnly: true,
                      module: 'compiler',
                      extension: 'out/err',
                      theme,
                    },
                    isClosable: false,
                  },
                  {
                    type: 'component',
                    componentName: 'text-editor-component',
                    componentState: {
                      storeMapping: {
                        contents: 'logs',
                      },
                      id: 'logs',
                      readOnly: true,
                      module: 'logs',
                      extension: 'txt',
                      theme,
                    },
                    isClosable: false,
                  },
                  {
                    type: 'component',
                    componentName: 'zip-viewer-component',
                    componentState: {
                      storeMapping: {},
                      id: 'zipviewer',
                      theme,
                    },
                    title: 'files.zip',
                    isClosable: false,
                  },
                ],
                height: 20,
              },
            ],
            isClosable: false,
          },
          {
            type: 'column',
            id: 'cases-column',
            content: [
              {
                type: 'row',
                content: [
                  {
                    type: 'component',
                    componentName: 'text-editor-component',
                    componentState: {
                      storeMapping: {
                        contents: 'inputIn',
                        module: 'currentCase',
                      },
                      id: 'in',
                      readOnly: false,
                      extension: 'in',
                      theme,
                    },
                    isClosable: false,
                  },
                  {
                    type: 'component',
                    componentName: 'text-editor-component',
                    componentState: {
                      storeMapping: {
                        contents: 'inputOut',
                        module: 'currentCase',
                      },
                      id: 'out',
                      readOnly: false,
                      extension: 'out',
                      theme,
                    },
                    isClosable: false,
                  },
                ],
              },
              {
                type: 'stack',
                content: [
                  {
                    type: 'component',
                    componentName: 'text-editor-component',
                    componentState: {
                      storeMapping: {
                        contents: 'outputStdout',
                        module: 'currentCase',
                      },
                      id: 'stdout',
                      readOnly: false,
                      extension: 'out',
                      theme,
                    },
                    isClosable: false,
                  },
                  {
                    type: 'component',
                    componentName: 'text-editor-component',
                    componentState: {
                      storeMapping: {
                        contents: 'outputStderr',
                        module: 'currentCase',
                      },
                      id: 'stderr',
                      readOnly: false,
                      extension: 'err',
                      theme,
                    },
                    isClosable: false,
                  },
                  {
                    type: 'component',
                    componentName: 'monaco-diff-component',
                    componentState: {
                      storeMapping: {
                        originalContents: 'inputOut',
                        modifiedContents: 'outputStdout',
                      },
                      id: 'diff',
                      theme,
                    },
                    isClosable: false,
                  },
                ],
              },
            ],
            isClosable: false,
          },
          {
            type: 'component',
            id: 'case-selector-column',
            componentName: 'case-selector-component',
            componentState: {
              storeMapping: {
                cases: 'request.input.cases',
                currentCase: 'currentCase',
              },
              id: 'source',
              theme,
            },
            title: 'cases/',
            width: 15,
            isClosable: false,
          },
        ],
      },
    ],
  };
  const validatorSettings = {
    type: 'component',
    componentName: 'monaco-editor-component',
    componentState: {
      storeMapping: {
        contents: 'request.input.validator.custom_validator.source',
        language: 'request.input.validator.custom_validator.language',
      },
      initialModule: 'validator',
      theme,
    },
    id: 'validator',
    isClosable: false,
  };
  const interactiveIdlSettings = {
    type: 'component',
    componentName: 'monaco-editor-component',
    componentState: {
      storeMapping: {
        contents: 'request.input.interactive.idl',
        module: 'request.input.interactive.module_name',
      },
      initialLanguage: 'idl',
      readOnly: true,
      theme,
    },
    id: 'interactive-idl',
    isClosable: false,
  };
  const interactiveMainSourceSettings = {
    type: 'component',
    componentName: 'monaco-editor-component',
    componentState: {
      storeMapping: {
        contents: 'request.input.interactive.main_source',
        language: 'request.input.interactive.language',
      },
      initialModule: 'Main',
      theme,
    },
    id: 'interactive-main-source',
    isClosable: false,
  };

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-grader-ephemeral': grader_Ephemeral,
    },
    render: function (createElement) {
      return createElement('omegaup-grader-ephemeral', {
        props: {
          theme: payload.theme,
          goldenLayoutSettings,
          validatorSettings,
          interactiveIdlSettings,
          interactiveMainSourceSettings,
        },
        on: {
          // TODO: Add all the actions needed
        },
      });
    },
  });
});
