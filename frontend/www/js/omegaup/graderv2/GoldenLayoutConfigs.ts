import GoldenLayout from 'golden-layout';

export const TEXT_EDITOR_COMPONENT_NAME = 'text-editor-component';
interface TextEditorComponentConfig {
  contents: string;
  readOnly: boolean;
  module?: string;
  extension: string;
  id: string;
}
const createTextEditorComponent = ({
  contents,
  readOnly,
  module,
  extension,
  id,
}: TextEditorComponentConfig) => ({
  type: 'component',
  componentName: TEXT_EDITOR_COMPONENT_NAME,
  componentState: {
    storeMapping: { contents, module },
    readOnly,
    extension,
    module: id,
    id,
  },
  isClosable: false,
});

export const MONACO_EDITOR_COMPONENT_NAME = 'monaco-editor-component';
const createMonacoEditorComponent = () => ({
  type: 'component',
  componentName: MONACO_EDITOR_COMPONENT_NAME,
  componentState: {
    storeMapping: {
      contents: 'request.source',
      language: 'request.language',
      module: 'moduleName',
    },
    id: 'source',
  },
  isClosable: false,
});

export const UNEMBEDDED_CONFIG: GoldenLayout.Config = {
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
                createMonacoEditorComponent(),
                {
                  type: 'component',
                  componentName: 'settings-component',
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
                createTextEditorComponent({
                  contents: 'compilerOutput',
                  readOnly: true,
                  extension: 'out/err',
                  id: 'compiler',
                }),
                createTextEditorComponent({
                  contents: 'logs',
                  readOnly: true,
                  extension: 'txt',
                  id: 'logs',
                }),
                {
                  type: 'component',
                  componentName: 'zip-viewer-component',
                  componentState: {
                    storeMapping: {},
                    id: 'zipviewer',
                  },
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
                createTextEditorComponent({
                  contents: 'inputIn',
                  readOnly: false,
                  module: 'currentCase',
                  extension: 'in',
                  id: 'in',
                }),
                createTextEditorComponent({
                  contents: 'inputOut',
                  readOnly: false,
                  module: 'currentCase',
                  extension: 'out',
                  id: 'out',
                }),
              ],
            },
            {
              type: 'stack',
              content: [
                createTextEditorComponent({
                  contents: 'outputStdout',
                  readOnly: false,
                  module: 'currentCase',
                  extension: 'out',
                  id: 'stdout',
                }),
                createTextEditorComponent({
                  contents: 'outputStderr',
                  readOnly: false,
                  module: 'currentCase',
                  extension: 'err',
                  id: 'stderr',
                }),
                {
                  type: 'component',
                  componentName: 'monaco-diff-component',
                  componentState: {
                    storeMapping: {
                      originalContents: 'inputOut',
                      modifiedContents: 'outputStdout',
                    },
                    id: 'diff',
                  },
                  title: 'diff',
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
            id: 'cases',
          },
          title: 'cases/',
          width: 15,
          isClosable: false,
        },
      ],
    },
  ],
};
export const EMBEDDED_CONFIG: GoldenLayout.Config = {
  settings: {
    showPopoutIcon: false,
  },
  content: [
    {
      type: 'row',
      content: [
        {
          type: 'column',
          content: [
            {
              type: 'stack',
              id: 'source-and-logs',
              content: [
                {
                  type: 'column',
                  id: 'main-column',
                  title: 'code',
                  content: [
                    createMonacoEditorComponent(),
                    {
                      type: 'stack',
                      content: [
                        createTextEditorComponent({
                          contents: 'compilerOutput',
                          readOnly: true,
                          extension: 'out/err',
                          id: 'compiler',
                        }),
                        createTextEditorComponent({
                          contents: 'logs',
                          readOnly: true,
                          extension: 'txt',
                          id: 'logs',
                        }),
                        {
                          type: 'component',
                          componentName: 'zip-viewer-component',
                          componentState: {
                            storeMapping: {},
                            id: 'zipviewer',
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
                  title: 'cases',
                  content: [
                    {
                      type: 'row',
                      content: [
                        createTextEditorComponent({
                          contents: 'inputIn',
                          readOnly: false,
                          module: 'currentCase',
                          extension: 'in',
                          id: 'in',
                        }),
                        createTextEditorComponent({
                          contents: 'inputOut',
                          readOnly: false,
                          module: 'currentCase',
                          extension: 'out',
                          id: 'out',
                        }),
                      ],
                    },
                    {
                      type: 'stack',
                      content: [
                        createTextEditorComponent({
                          contents: 'outputStdout',
                          readOnly: false,
                          module: 'currentCase',
                          extension: 'out',
                          id: 'stdout',
                        }),
                        createTextEditorComponent({
                          contents: 'outputStderr',
                          readOnly: false,
                          module: 'currentCase',
                          extension: 'err',
                          id: 'stderr',
                        }),
                        {
                          type: 'component',
                          componentName: 'monaco-diff-component',
                          componentState: {
                            storeMapping: {
                              originalContents: 'inputOut',
                              modifiedContents: 'outputStdout',
                            },
                            id: 'diff',
                          },
                          title: 'diff',
                          isClosable: false,
                        },
                      ],
                    },
                  ],
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
            id: 'cases',
          },
          title: 'cases/',
          width: 15,
          isClosable: false,
        },
      ],
    },
  ],
};
