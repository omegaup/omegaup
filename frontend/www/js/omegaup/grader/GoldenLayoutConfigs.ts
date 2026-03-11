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
}: TextEditorComponentConfig): GoldenLayout.ComponentConfig => ({
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
const createMonacoEditorComponent = (): GoldenLayout.ComponentConfig => ({
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

export const CASE_SELECTOR_COMPONENT_NAME = 'case-selector-component';
const createCaseSelectorComponent = (): GoldenLayout.ComponentConfig => ({
  type: 'component',
  componentName: CASE_SELECTOR_COMPONENT_NAME,
  componentState: {
    storeMapping: {
      cases: 'request.input.cases',
      currentCase: 'currentCase',
    },
    id: 'cases',
  },
  title: 'cases/',
  width: 19,
  isClosable: false,
});

export const MONACO_DIFF_COMPONENT_NAME = 'monaco-diff-component';
const createMonacoDiffComponent = (): GoldenLayout.ComponentConfig => ({
  type: 'component',
  componentName: MONACO_DIFF_COMPONENT_NAME,
  componentState: {
    storeMapping: {
      originalContents: 'inputOut',
      modifiedContents: 'outputStdout',
    },
    id: 'diff',
  },
  title: 'diff',
  isClosable: false,
});

export const ZIP_VIEWER_COMPONENT_NAME = 'zip-viewer-component';
const createZipViewerComponent = (): GoldenLayout.ComponentConfig => ({
  type: 'component',
  componentName: ZIP_VIEWER_COMPONENT_NAME,
  componentState: {
    storeMapping: {},
    id: 'zipviewer',
  },
  title: 'files.zip',
  isClosable: false,
});

export const SETTINGS_COMPONENT_NAME = 'settings-component';
const createSettingsComponent = (): GoldenLayout.ComponentConfig => ({
  type: 'component',
  componentName: SETTINGS_COMPONENT_NAME,
  componentState: {
    storeMapping: {},
    id: 'settings',
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
                {
                  type: 'column',
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
                        createZipViewerComponent(),
                      ],
                      height: 20,
                    },
                  ],
                  isClosable: false,
                },
                createSettingsComponent(),
              ],
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
                  readOnly: true,
                  module: 'currentCase',
                  extension: 'out',
                  id: 'stdout',
                }),
                createTextEditorComponent({
                  contents: 'outputStderr',
                  readOnly: true,
                  module: 'currentCase',
                  extension: 'err',
                  id: 'stderr',
                }),
                createMonacoDiffComponent(),
              ],
            },
          ],
          isClosable: false,
        },
        createCaseSelectorComponent(),
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
                        createZipViewerComponent(),
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
                          readOnly: true,
                          module: 'currentCase',
                          extension: 'out',
                          id: 'stdout',
                        }),
                        createTextEditorComponent({
                          contents: 'outputStderr',
                          readOnly: true,
                          module: 'currentCase',
                          extension: 'err',
                          id: 'stderr',
                        }),
                        createMonacoDiffComponent(),
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
        createCaseSelectorComponent(),
      ],
    },
  ],
};
