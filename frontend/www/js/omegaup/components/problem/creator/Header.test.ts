import { shallowMount, createLocalVue, mount } from '@vue/test-utils';
import JSZip from 'jszip';

import Header from './Header.vue';
import BootstrapVue, { IconsPlugin, BButton, BFormInput } from 'bootstrap-vue';
import store from '@/js/omegaup/problem/creator/store';
import T from '../../../lang';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('Header.vue', () => {
  it('Should contain the header buttons and problem name input', async () => {
    const wrapper = shallowMount(Header, { localVue, store });

    const buttons = wrapper.findAllComponents(BButton);
    const expectedText = [
      T.problemCreatorLoadProblem,
      T.problemCreatorGenerateProblem,
      T.problemCreatorNewProblem,
    ];

    expect(expectedText.length).toBe(buttons.length);
    for (let i = 0; i < expectedText.length; i++) {
      expect(buttons.at(i).text()).toBe(expectedText[i]);
    }

    expect(wrapper.findComponent(BFormInput).exists()).toBe(true);
  });

  it('Should reset the store on clicking the reset button', async () => {
    const original = window.location;

    Object.defineProperty(window, 'location', {
      configurable: true,
      value: { reload: jest.fn() },
    });

    const wrapper = mount(Header, { localVue, store });

    const buttonsList = wrapper.findAll('button');
    expect(buttonsList.length).toBe(3);

    const resetButton = buttonsList.at(2);
    expect(resetButton.exists()).toBe(true);

    const testText = 'Hello';
    const emptyText = '';
    wrapper.vm.$store.state.problemName = testText;
    wrapper.vm.$store.state.problemMarkdown = testText;
    wrapper.vm.$store.state.problemCodeContent = testText;
    wrapper.vm.$store.state.problemCodeExtension = testText;
    wrapper.vm.$store.state.problemSolutionMarkdown = testText;

    await resetButton.trigger('click');

    expect(window.location.reload).toHaveBeenCalledTimes(1);

    expect(wrapper.vm.$store.state.problemName).toBe(
      T.problemCreatorNewProblem,
    );
    expect(wrapper.vm.$store.state.problemMarkdown).toBe(emptyText);
    expect(wrapper.vm.$store.state.problemCodeContent).toBe(emptyText);
    expect(wrapper.vm.$store.state.problemCodeExtension).toBe(emptyText);
    expect(wrapper.vm.$store.state.problemSolutionMarkdown).toBe(emptyText);

    jest.restoreAllMocks();
    Object.defineProperty(window, 'location', {
      configurable: true,
      value: original,
    });
  });

  it('Should process the zip after it is uploaded', async () => {
    const wrapper = mount(Header, {
      localVue,
      store,
    });

    const cdpDataText = `{"problemName":"Hello","problemMarkdown":"Hello statement","problemCodeContent":"print('Hello omegaUp')","problemCodeExtension":"py","problemSolutionMarkdown":"Hello solution","casesStore":{"groups":[{"groupID":"73f13442-b6a3-406d-a2b7-bd2af8b36322","name":"hello","points":100,"autoPoints":false,"ungroupedCase":false,"cases":[{"caseID":"7b3a840d-812e-4726-ade8-6c304a189c3e","groupID":"73f13442-b6a3-406d-a2b7-bd2af8b36322","lines":[{"lineID":"e2a1942f-cc5a-4776-ad27-2fc657539621","caseID":"7b3a840d-812e-4726-ade8-6c304a189c3e","label":"Hello","data":{"kind":"line","value":"there"}}],"points":50,"output":"omegaup","name":"hello1"},{"caseID":"d33b7c36-594b-4166-9860-8d485951447a","groupID":"73f13442-b6a3-406d-a2b7-bd2af8b36322","lines":[{"lineID":"0dfe32e8-5c0e-42c4-88b3-7085cd11a3c4","caseID":"d33b7c36-594b-4166-9860-8d485951447a","label":"Hi","data":{"kind":"line","value":"there"}}],"points":50,"output":"omegaup","name":"hello2"}]}],"selected":{"groupID":"73f13442-b6a3-406d-a2b7-bd2af8b36322","caseID":"d33b7c36-594b-4166-9860-8d485951447a"},"layouts":[],"hide":false}}`;

    const zip = new JSZip();
    zip.file('cdp.data', cdpDataText);

    const zipContent = await zip.generateAsync({ type: 'blob' });
    const testZipFile = new File([zipContent], 'testfile.zip', {
      type: 'application/zip',
    });

    const mockReadFileMethod = jest
      .spyOn(wrapper.vm, 'readFile')
      .mockImplementation(() => testZipFile);

    const loadFileButton = wrapper.find('button[data-load-problem-button]');
    expect(loadFileButton.exists()).toBeTruthy();

    await loadFileButton.trigger('click');

    const fileInput = wrapper.find('input[data-upload-zip-file]');
    expect(fileInput.exists()).toBeTruthy();

    await fileInput.trigger('change');

    wrapper.vm.retrieveStore();
    expect(mockReadFileMethod).toHaveBeenCalled();

    const emittedStoreData = {
      problemName: 'Hello',
      problemMarkdown: 'Hello statement',
      problemCodeContent: "print('Hello omegaUp')",
      problemCodeExtension: 'py',
      problemSolutionMarkdown: 'Hello solution',
      casesStore: {
        groups: [
          {
            groupID: '73f13442-b6a3-406d-a2b7-bd2af8b36322',
            name: 'hello',
            points: 100,
            autoPoints: false,
            ungroupedCase: false,
            cases: [
              {
                caseID: '7b3a840d-812e-4726-ade8-6c304a189c3e',
                groupID: '73f13442-b6a3-406d-a2b7-bd2af8b36322',
                lines: [
                  {
                    lineID: 'e2a1942f-cc5a-4776-ad27-2fc657539621',
                    caseID: '7b3a840d-812e-4726-ade8-6c304a189c3e',
                    label: 'Hello',
                    data: { kind: 'line', value: 'there' },
                  },
                ],
                points: 50,
                output: 'omegaup',
                name: 'hello1',
              },
              {
                caseID: 'd33b7c36-594b-4166-9860-8d485951447a',
                groupID: '73f13442-b6a3-406d-a2b7-bd2af8b36322',
                lines: [
                  {
                    lineID: '0dfe32e8-5c0e-42c4-88b3-7085cd11a3c4',
                    caseID: 'd33b7c36-594b-4166-9860-8d485951447a',
                    label: 'Hi',
                    data: { kind: 'line', value: 'there' },
                  },
                ],
                points: 50,
                output: 'omegaup',
                name: 'hello2',
              },
            ],
          },
        ],
        selected: {
          groupID: '73f13442-b6a3-406d-a2b7-bd2af8b36322',
          caseID: 'd33b7c36-594b-4166-9860-8d485951447a',
        },
        layouts: [],
        hide: false,
      },
    };

    // let emittedStoreData = null;
    // expect(wrapper.vm.zipFile).toBeTruthy();
    // if(wrapper.vm.zipFile) {
    //   zip
    //   .loadAsync(wrapper.vm.zipFile)
    //   .then((zipContent) => {
    //     const cdpDataFile = zipContent.file('cdp.data');
    //     if (cdpDataFile) {
    //       cdpDataFile.async('text').then((content) => {
    //         emittedStoreData =JSON.parse(content);
    //       });
    //     }
    //   })
    // }

    await new Promise((r) => setTimeout(r, 1000)); // Waiting for the JSZIp to complete extracting the file.

    // expect(wrapper.emitted()['upload-zip-file']).toStrictEqual(emittedStoreData);

    // expect(wrapper.emitted()['upload-zip-file']).toStrictEqual([emittedStoreData]);
    expect(wrapper.vm.$store.state.problemName).toBe(
      emittedStoreData.problemName,
    );
    expect(wrapper.vm.$store.state.problemCodeContent).toBe(
      emittedStoreData.problemCodeContent,
    );
    expect(wrapper.vm.$store.state.problemCodeExtension).toBe(
      emittedStoreData.problemCodeExtension,
    );
    expect(wrapper.vm.$store.state.problemMarkdown).toBe(
      emittedStoreData.problemMarkdown,
    );
    expect(wrapper.vm.$store.state.problemSolutionMarkdown).toBe(
      emittedStoreData.problemSolutionMarkdown,
    );
    expect(wrapper.vm.$store.state.casesStore).toStrictEqual(
      emittedStoreData.casesStore,
    );
  });
});
