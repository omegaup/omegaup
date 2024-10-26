import { shallowMount, createLocalVue, mount } from '@vue/test-utils';
import JSZip from 'jszip';

import Header from './Header.vue';
import BootstrapVue, { IconsPlugin, BButton, BFormInput } from 'bootstrap-vue';
import store from '@/js/omegaup/problem/creator/store';
import T from '../../../lang';
import Vue from 'vue';
import {
  generateCase,
  generateGroup,
} from '@/js/omegaup/problem/creator/modules/cases';

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

    const createNewProblemModal = wrapper.find('[data-create-new-problem]');
    await createNewProblemModal.find('button.btn-danger').trigger('click');

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

  it('Should download the zip file', async () => {
    const wrapper = mount(Header, { localVue, store });

    const generateProblemSpy = jest.spyOn(wrapper.vm, 'generateProblem');

    const downloadButton = wrapper.find('button[data-download-zip]');
    expect(downloadButton.exists()).toBe(true);

    await downloadButton.trigger('click');
    await Vue.nextTick();
    expect(generateProblemSpy).toHaveBeenCalled();

    jest.restoreAllMocks();
  });

  it('Should have correct file/folder structure', async () => {
    const wrapper = mount(Header, { localVue, store });

    const newUngroupedCasegroup = generateGroup({
      name: 'New Ungrouped Case Group',
      ungroupedCase: true,
    });
    const newUngroupedCase = generateCase({
      name: 'New Ungrouped Case',
      groupID: newUngroupedCasegroup.groupID,
    });
    const newGroup = generateGroup({
      name: 'New Group',
      ungroupedCase: false,
    });
    const newCase = generateCase({
      name: 'New Case',
      groupID: newGroup.groupID,
    });
    const fileFolderList = [
      'statements/',
      'statements/es.markdown',
      'solutions/',
      'solutions/es.markdown',
      'cases/',
      'cases/New Ungrouped Case.in',
      'cases/New Ungrouped Case.out',
      'cases/New Group.New Case.in',
      'cases/New Group.New Case.out',
      'testplan',
      'cdp.data',
    ];

    store.commit('casesStore/resetStore');
    store.commit('casesStore/addGroup', newUngroupedCasegroup);
    store.commit('casesStore/addCase', newUngroupedCase);
    store.commit('casesStore/addGroup', newGroup);
    store.commit('casesStore/addCase', newCase);

    const downloadButton = wrapper.find('button[data-download-zip]');
    expect(downloadButton.exists()).toBe(true);

    await downloadButton.trigger('click');
    await Vue.nextTick();

    expect(Object.keys(wrapper.vm.zip.files)).toStrictEqual(fileFolderList);

    jest.restoreAllMocks();
  });

  it('Should process the zip after it is uploaded', async () => {
    const wrapper = mount(Header, {
      localVue,
      store,
    });

    const cdpDataText = `{"problemName":"Hello","problemMarkdown":"Hello Statement!","problemCodeContent":"print('Hello world')","problemCodeExtension":"py","problemSolutionMarkdown":"Hello Solution!","casesStore":{"groups":[{"groupID":"26fdf593-d0c5-49f0-80b0-09bf0673c974","name":"hellogroup","points":100,"autoPoints":false,"ungroupedCase":false,"cases":[{"caseID":"8dda8030-60b9-4daf-b484-c4254525be6e","groupID":"26fdf593-d0c5-49f0-80b0-09bf0673c974","lines":[{"lineID":"c54c349e-b56e-426e-b4e1-032c62b05e8e","caseID":"8dda8030-60b9-4daf-b484-c4254525be6e","label":"Line1","data":{"kind":"line","value":"Hello line"}},{"lineID":"9768961e-27fc-4cdc-a4ca-c2de43fc15b1","caseID":"8dda8030-60b9-4daf-b484-c4254525be6e","label":"Line2","data":{"kind":"multiline","value":"Hello multiline"}}],"points":100,"output":"","name":"hellocase"}]}],"selected":{"groupID":"26fdf593-d0c5-49f0-80b0-09bf0673c974","caseID":"8dda8030-60b9-4daf-b484-c4254525be6e"},"layouts":[{"layoutID":"2b741bd6-6f20-46bd-9a0f-d3fbfc4ad03f","name":"hellogroup_hellocase","caseLineInfos":[{"lineInfoID":"d8942896-0c67-42ce-8f4b-581eade8840f","label":"Line1","data":{"kind":"line","value":""}},{"lineInfoID":"416ce979-62c7-4e9c-b282-d4734a7b556e","label":"Line2","data":{"kind":"multiline","value":""}}]}],"hide":false}}`;

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
      problemMarkdown: 'Hello Statement!',
      problemCodeContent: "print('Hello world')",
      problemCodeExtension: 'py',
      problemSolutionMarkdown: 'Hello Solution!',
      casesStore: {
        groups: [
          {
            groupID: '26fdf593-d0c5-49f0-80b0-09bf0673c974',
            name: 'hellogroup',
            points: 100,
            autoPoints: false,
            ungroupedCase: false,
            cases: [
              {
                caseID: '8dda8030-60b9-4daf-b484-c4254525be6e',
                groupID: '26fdf593-d0c5-49f0-80b0-09bf0673c974',
                lines: [
                  {
                    lineID: 'c54c349e-b56e-426e-b4e1-032c62b05e8e',
                    caseID: '8dda8030-60b9-4daf-b484-c4254525be6e',
                    label: 'Line1',
                    data: {
                      kind: 'line',
                      value: 'Hello line',
                    },
                  },
                  {
                    lineID: '9768961e-27fc-4cdc-a4ca-c2de43fc15b1',
                    caseID: '8dda8030-60b9-4daf-b484-c4254525be6e',
                    label: 'Line2',
                    data: {
                      kind: 'multiline',
                      value: 'Hello multiline',
                    },
                  },
                ],
                points: 100,
                output: '',
                name: 'hellocase',
              },
            ],
          },
        ],
        selected: {
          groupID: '26fdf593-d0c5-49f0-80b0-09bf0673c974',
          caseID: '8dda8030-60b9-4daf-b484-c4254525be6e',
        },
        layouts: [
          {
            layoutID: '2b741bd6-6f20-46bd-9a0f-d3fbfc4ad03f',
            name: 'hellogroup_hellocase',
            caseLineInfos: [
              {
                lineInfoID: 'd8942896-0c67-42ce-8f4b-581eade8840f',
                label: 'Line1',
                data: {
                  kind: 'line',
                  value: '',
                },
              },
              {
                lineInfoID: '416ce979-62c7-4e9c-b282-d4734a7b556e',
                label: 'Line2',
                data: {
                  kind: 'multiline',
                  value: '',
                },
              },
            ],
          },
        ],
        hide: false,
      },
    };

    await new Promise((r) => setTimeout(r, 1000)); // Waiting for the JSZIp to complete extracting the file.

    expect(wrapper.emitted()['upload-zip-file']).toStrictEqual([
      [emittedStoreData],
    ]);

    expect(wrapper.vm.nameInternal).toBe(emittedStoreData.problemName);
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
