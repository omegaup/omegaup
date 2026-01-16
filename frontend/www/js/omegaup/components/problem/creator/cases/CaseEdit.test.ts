import { createLocalVue, shallowMount, mount } from '@vue/test-utils';

import CaseEdit from './CaseEdit.vue';
import BootstrapVue, { IconsPlugin, BButton } from 'bootstrap-vue';
import store from '@/js/omegaup/problem/creator/store';
import Vue from 'vue';
import { NIL as UUID_NIL } from 'uuid';
import {
  generateCase,
  generateGroup,
} from '@/js/omegaup/problem/creator/modules/cases';

import T from '../../../../lang';
import { MatrixDistinctType } from '@/js/omegaup/problem/creator/types';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('CaseEdit.vue', () => {
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
  beforeEach(async () => {
    store.commit('casesStore/resetStore');
    store.commit('casesStore/addGroup', newUngroupedCasegroup);
    store.commit('casesStore/addCase', newUngroupedCase);
    store.commit('casesStore/addGroup', newGroup);
    store.commit('casesStore/addCase', newCase);
  });

  it('Should show an ungrouped case', async () => {
    const wrapper = shallowMount(CaseEdit, { localVue, store: store });

    const groupID = newUngroupedCasegroup.groupID;
    const caseID = newUngroupedCase.caseID;
    store.commit('casesStore/setSelected', {
      groupID,
      caseID,
    });
    await Vue.nextTick();

    // There are currently 7 bootstrap buttons on the page.
    // - Edit case
    // - Delete case
    // - Download .in
    // - Download .txt
    // - Delete lines
    // - Add new line
    // - Erase output
    const initialBButtonsCount = 7;

    const buttons = wrapper.findAllComponents(BButton);
    expect(buttons.length).toBe(initialBButtonsCount);

    expect(wrapper.text()).toContain(newUngroupedCase.name);
    expect(wrapper.text()).toContain(newUngroupedCasegroup.name);

    expect(
      wrapper.find('biconpencilfill-stub').element.parentElement?.textContent,
    ).toContain(T.problemCreatorEditCase);
    expect(
      wrapper.find('bicontrashfill-stub').element.parentElement?.textContent,
    ).toContain(T.problemCreatorDeleteCase);
    expect(wrapper.find('b-dropdown-stub').exists()).toBe(true);
  });

  it('Should show a grouped case', async () => {
    const wrapper = shallowMount(CaseEdit, { localVue, store: store });

    const groupID = newGroup.groupID;
    const caseID = newCase.caseID;
    store.commit('casesStore/setSelected', {
      groupID,
      caseID,
    });
    await Vue.nextTick();

    expect(wrapper.text()).toContain(newCase.name);
    expect(wrapper.text()).toContain(newGroup.name);

    expect(
      wrapper.find('biconpencilfill-stub').element.parentElement?.textContent,
    ).toContain(T.problemCreatorEditCase);
    expect(
      wrapper.find('bicontrashfill-stub').element.parentElement?.textContent,
    ).toContain(T.problemCreatorDeleteCase);
    expect(wrapper.find('b-dropdown-stub').exists()).toBe(true);
  });

  it('Should delete a case', async () => {
    const wrapper = mount(CaseEdit, { localVue, store });

    const groupID = newGroup.groupID;
    const caseID = newCase.caseID;
    store.commit('casesStore/setSelected', {
      groupID,
      caseID,
    });
    await Vue.nextTick();

    const caseDeleteButton = wrapper.find('button[data-delete-case]');
    expect(caseDeleteButton.exists()).toBeTruthy();

    await caseDeleteButton.trigger('click');
    expect(
      wrapper.vm.groups.find((_group) => _group.groupID === newGroup.groupID),
    ).toBeTruthy();
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === newGroup.groupID)
        ?.cases.find((_case) => _case.caseID === newCase.caseID),
    ).toBeFalsy();
    expect(store.state.casesStore.selected.caseID).toBe(UUID_NIL);
    expect(store.state.casesStore.selected.groupID).toBe(UUID_NIL);
  });

  it('Should add, modify and delete a line', async () => {
    const wrapper = mount(CaseEdit, { localVue, store: store });

    const groupID = newGroup.groupID;
    const caseID = newCase.caseID;
    store.commit('casesStore/setSelected', {
      groupID,
      caseID,
    });
    await Vue.nextTick();

    expect(wrapper.text()).toContain(newCase.name);
    expect(wrapper.text()).toContain(newGroup.name);

    wrapper.vm.addNewLine();
    await Vue.nextTick();

    expect(wrapper.find('table').exists()).toBeTruthy();
    const formInputs = wrapper.findAll('input');

    formInputs.at(0).setValue('testLabel');
    formInputs.at(1).setValue('testValue');
    await wrapper.trigger('click');

    expect(wrapper.vm.getLinesFromSelectedCase[0].label).toBe('testLabel');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.value).toBe('testValue');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('line');

    // There are 4 dropdown items for each line:
    // - Line
    // - Multiline
    // - Array
    // - Matrix
    const dropdownItemCount = 4;

    const dropdowns = wrapper.findAll('a.dropdown-item');
    expect(dropdowns.length).toBe(dropdownItemCount);

    await dropdowns.at(1).trigger('click');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('multiline');

    await dropdowns.at(2).trigger('click');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('array');

    await dropdowns.at(3).trigger('click');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('matrix');

    wrapper.vm.deleteLine(wrapper.vm.getLinesFromSelectedCase[0].lineID);
    await Vue.nextTick();

    const formInputsUpdated = wrapper.findAll('input');

    // The input element should be replaced by textarea element
    expect(formInputsUpdated.length).toBe(0);
  });

  it('Should write and erase outputs', async () => {
    const wrapper = mount(CaseEdit, { localVue, store: store });

    const groupID = newGroup.groupID;
    const caseID = newCase.caseID;
    store.commit('casesStore/setSelected', {
      groupID,
      caseID,
    });
    await Vue.nextTick();

    const outputTextarea = wrapper.find('textarea[data-output-textarea]');
    expect(outputTextarea.exists()).toBeTruthy();

    const testOutput = 'Hello omegaUp';
    await outputTextarea.setValue(testOutput);

    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === newGroup.groupID)
        ?.cases.find((_case) => _case.caseID === newCase.caseID)?.output,
    ).toBe(testOutput);

    expect((outputTextarea.element as HTMLInputElement).value).toBe(testOutput);

    const eraseOutputButton = wrapper.find('button[data-erase-output]');
    expect(eraseOutputButton.exists()).toBeTruthy();

    await eraseOutputButton.trigger('click');
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === newGroup.groupID)
        ?.cases.find((_case) => _case.caseID === newCase.caseID)?.output,
    ).toBe('');
    expect((outputTextarea.element as HTMLInputElement).value).toBe('');
  });

  it('Should modify and move a case', async () => {
    store.commit('casesStore/resetStore');

    newUngroupedCasegroup.cases = [];
    newGroup.cases = [];

    store.commit('casesStore/addGroup', newUngroupedCasegroup);
    store.commit('casesStore/addCase', newUngroupedCase);
    store.commit('casesStore/addGroup', newGroup);

    const wrapper = mount(CaseEdit, { localVue, store: store });

    const groupID = newUngroupedCasegroup.groupID;
    const caseID = newUngroupedCase.caseID;
    store.commit('casesStore/setSelected', {
      groupID,
      caseID,
    });
    await Vue.nextTick();

    const editButton = wrapper.find('button');
    expect(editButton.text()).toBe(T.caseEditTitle);

    await editButton.trigger('click');

    const modifiedName = 'modifiedname';
    const editNameInput = wrapper.find('input');
    await editNameInput.setValue(modifiedName);

    const saveButton = wrapper.find('button.btn-success');
    expect(saveButton.text()).toBe(T.caseModalSave);

    await saveButton.trigger('click');

    expect(wrapper.vm.getSelectedCase.name).toBe(modifiedName);
    expect(wrapper.vm.getSelectedGroup.name).toBe(modifiedName);

    await editButton.trigger('click');

    wrapper.vm.caseInputRef.caseGroup = newGroup.groupID;
    wrapper.vm.updateCaseInfo();

    // There's only one group added to the store.
    expect(wrapper.vm.groups.length).toBe(1);

    expect(wrapper.vm.groups[0].cases[0].name).toBe(modifiedName);
    expect(wrapper.vm.groups[0].cases[0].groupID).toBe(newGroup.groupID);
  });

  const arrayInputMapping: {
    arrSize: number;
    arrLow: number;
    arrHigh: number;
    distinct: boolean;
    uniqueConstraint: boolean;
    emptyConstraint: boolean;
  }[] = [
    {
      arrSize: 10,
      arrLow: 0,
      arrHigh: 9,
      distinct: false,
      uniqueConstraint: false,
      emptyConstraint: false,
    },
    {
      arrSize: 10,
      arrLow: 0,
      arrHigh: 9,
      distinct: true,
      uniqueConstraint: true,
      emptyConstraint: false,
    },
    {
      arrSize: 11,
      arrLow: 0,
      arrHigh: 9,
      distinct: true,
      uniqueConstraint: false,
      emptyConstraint: true,
    },
  ];

  describe.each(arrayInputMapping)(`An array with:`, (arrayInput) => {
    it(`size ${arrayInput.arrSize}, minimum ${arrayInput.arrLow}, maximum ${arrayInput.arrHigh}, distinct ${arrayInput.distinct}, should have uniqueConstraint ${arrayInput.emptyConstraint} and emptyConstraint ${arrayInput.emptyConstraint}`, async () => {
      const wrapper = mount(CaseEdit, { localVue, store });

      const array = wrapper.vm
        .getArrayContent(
          arrayInput.arrSize,
          arrayInput.arrLow,
          arrayInput.arrHigh,
          arrayInput.distinct,
        )
        .split(' ')
        .map(Number);

      if (arrayInput.emptyConstraint) {
        expect(array).toStrictEqual([0]);
      } else {
        expect(array.length).toBe(arrayInput.arrSize);
        expect(
          array.every(
            (num) => num >= arrayInput.arrLow && num <= arrayInput.arrHigh,
          ),
        ).toBeTruthy();
      }

      if (arrayInput.uniqueConstraint) {
        expect(new Set(array).size).toBe(arrayInput.arrSize);
      }
    });
  });

  const matrixInputMapping: {
    matrixRows: number;
    matrixCols: number;
    matrixLow: number;
    matrixHigh: number;
    distinct: MatrixDistinctType;
    rowUniqueConstraint: boolean;
    colUniqueConstraint: boolean;
    emptyConstraint: boolean;
  }[] = [
    {
      matrixRows: 3,
      matrixCols: 3,
      matrixLow: 0,
      matrixHigh: 8,
      distinct: MatrixDistinctType.None,
      rowUniqueConstraint: false,
      colUniqueConstraint: false,
      emptyConstraint: false,
    },
    {
      matrixRows: 3,
      matrixCols: 3,
      matrixLow: 0,
      matrixHigh: 8,
      distinct: MatrixDistinctType.Rows,
      rowUniqueConstraint: true,
      colUniqueConstraint: false,
      emptyConstraint: false,
    },
    {
      matrixRows: 3,
      matrixCols: 3,
      matrixLow: 0,
      matrixHigh: 8,
      distinct: MatrixDistinctType.Cols,
      rowUniqueConstraint: false,
      colUniqueConstraint: true,
      emptyConstraint: false,
    },
    {
      matrixRows: 3,
      matrixCols: 3,
      matrixLow: 0,
      matrixHigh: 8,
      distinct: MatrixDistinctType.Both,
      rowUniqueConstraint: true,
      colUniqueConstraint: true,
      emptyConstraint: false,
    },
    {
      matrixRows: 3,
      matrixCols: 3,
      matrixLow: 0,
      matrixHigh: 7,
      distinct: MatrixDistinctType.Both,
      rowUniqueConstraint: false,
      colUniqueConstraint: false,
      emptyConstraint: true,
    },
  ];

  describe.each(matrixInputMapping)(`A matrix with:`, (matrixInput) => {
    it(`${matrixInput.matrixRows} rows, ${matrixInput.matrixCols} columns, minimum ${matrixInput.matrixLow}, maximum ${matrixInput.matrixHigh}, distinct type ${matrixInput.distinct}, should have emptyConstraint ${matrixInput.emptyConstraint}, rowUniqueConstraint ${matrixInput.rowUniqueConstraint}, colUniqueConstraint ${matrixInput.colUniqueConstraint}`, async () => {
      const wrapper = mount(CaseEdit, { localVue, store });
      const matrix = wrapper.vm
        .getMatrixContent(
          matrixInput.matrixRows,
          matrixInput.matrixCols,
          matrixInput.matrixLow,
          matrixInput.matrixHigh,
          matrixInput.distinct,
        )
        .trim()
        .split('\n')
        .map((row) => row.trim().split(' ').map(Number));

      if (matrixInput.emptyConstraint) {
        expect(matrix).toStrictEqual([[0]]);
      } else {
        expect(matrix.length).toBe(matrixInput.matrixRows);
        expect(matrix[0].length).toBe(matrixInput.matrixCols);
        expect(
          matrix.every((row) =>
            row.every(
              (num) =>
                num >= matrixInput.matrixLow && num <= matrixInput.matrixHigh,
            ),
          ),
        ).toBeTruthy();
      }

      if (matrixInput.rowUniqueConstraint) {
        expect(
          matrix.every((row) => new Set(row).size === matrixInput.matrixCols),
        ).toBe(true);
      }
      if (matrixInput.colUniqueConstraint) {
        expect(
          matrix[0]
            .map((_, colIndex) => matrix.map((row) => row[colIndex]))
            .every((row) => new Set(row).size === matrixInput.matrixRows),
        ).toBeTruthy();
      }
    });
  });

  it('Should generate and render arrays', async () => {
    const wrapper = mount(CaseEdit, { localVue, store });

    const groupID = newGroup.groupID;
    const caseID = newCase.caseID;
    store.commit('casesStore/setSelected', {
      groupID,
      caseID,
    });
    await Vue.nextTick();

    expect(wrapper.text()).toContain(newCase.name);
    expect(wrapper.text()).toContain(newGroup.name);

    wrapper.vm.addNewLine();
    await Vue.nextTick();

    const dropdowns = wrapper.findAll('a.dropdown-item');

    await dropdowns.at(2).trigger('click');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('array');

    const editSVG = wrapper.find('svg.bi-pencil-square');
    expect(editSVG.exists()).toBeTruthy();

    const editIcon = wrapper.find(`button[data-line-edit-button]`);
    await editIcon.trigger('click');

    const modalBody = wrapper.find('div[data-array-modal]');

    const modalInputs = modalBody.findAll('input');

    const modalButton = modalBody.find('button[data-array-modal-generate]');
    expect(modalButton.exists()).toBeTruthy();

    const mockGenerate = jest.spyOn(wrapper.vm, 'getArrayContent');

    await modalButton.trigger('click');
    expect(mockGenerate).toHaveBeenCalledWith(10, 0, 100, false);

    await modalBody.find('input[data-array-modal-size]').setValue(5);
    await modalBody.find('input[data-array-modal-min]').setValue(10);
    await modalBody.find('input[data-array-modal-max]').setValue(20);
    await modalBody.find('input[data-array-modal-checkbox]').setChecked(true);

    await modalButton.trigger('click');
    expect(mockGenerate).toHaveBeenCalledWith(5, 10, 20, true);
    mockGenerate.mockRestore();

    await modalInputs.at(0).setValue(5);
    await modalInputs.at(1).setValue(10);
    await modalInputs.at(2).setValue(10);
    await modalInputs.at(3).setChecked(false);

    await modalButton.trigger('click');

    expect(
      (
        modalBody.find('input[data-array-modal-generated-array]')
          .element as HTMLInputElement
      ).value,
    ).toBe('10 10 10 10 10');

    const modalFooter = wrapper.find('footer.modal-footer');

    // There should be two footer buttons: Save and Cancel.
    const footerButtonsCount = 2;

    const footerButtons = modalFooter.findAll('button');
    expect(footerButtons.length).toBe(footerButtonsCount);

    await modalFooter.find('button.btn-success').trigger('click');

    expect(wrapper.vm.getLinesFromSelectedCase[0].data.value).toBe(
      '10 10 10 10 10',
    );
  });

  it('Should generate and render matrices', async () => {
    const wrapper = mount(CaseEdit, { localVue, store });

    const groupID = newGroup.groupID;
    const caseID = newCase.caseID;
    store.commit('casesStore/setSelected', {
      groupID,
      caseID,
    });
    await Vue.nextTick();

    expect(wrapper.text()).toContain(newCase.name);
    expect(wrapper.text()).toContain(newGroup.name);

    const dropdowns = wrapper.findAll('a.dropdown-item');
    expect(dropdowns.length).toBe(4);

    await dropdowns.at(3).trigger('click');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('matrix');

    const editSVG = wrapper.find('svg.bi-pencil-square');
    expect(editSVG.exists()).toBeTruthy();

    const editIcon = wrapper.find(`button[data-line-edit-button]`);
    await editIcon.trigger('click');

    const modalBody = wrapper.find('div[data-matrix-modal]');

    const modalInputs = modalBody.findAll('input');
    expect(modalInputs.length).toBe(4);

    const modalButton = modalBody.find('button[data-matrix-modal-generate]');
    expect(modalButton.exists).toBeTruthy();

    const mockGenerateMatrix = jest.spyOn(wrapper.vm, 'getMatrixContent');

    await modalButton.trigger('click');
    expect(mockGenerateMatrix).toHaveBeenCalledWith(3, 3, 0, 100, 'none');
    mockGenerateMatrix.mockRestore();

    await modalBody.find('input[data-matrix-modal-rows]').setValue(3);
    await modalBody.find('input[data-matrix-modal-columns]').setValue(3);
    await modalBody.find('input[data-matrix-modal-min]').setValue(20);
    await modalBody.find('input[data-matrix-modal-max]').setValue(20);

    await modalButton.trigger('click');
    expect(
      (
        modalBody.find('textarea[data-matrix-modal-generated-matrix]')
          .element as HTMLInputElement
      ).value,
    ).toBe('20 20 20\n20 20 20\n20 20 20');

    const modalFooter = wrapper.find('footer.modal-footer');

    const footerButtons = modalFooter.findAll('button');
    expect(footerButtons.length).toBe(2);

    wrapper.vm.editLineValue([
      wrapper.vm.getLinesFromSelectedCase[0].lineID,
      wrapper.vm.matrixModalEditArray,
    ]);

    expect(wrapper.vm.getLinesFromSelectedCase[0].data.value).toBe(
      '20 20 20\n20 20 20\n20 20 20',
    );
  });

  const matrixDistinctTypeMapping: {
    matrixDistinctType: string;
    matrixDistinctEnum: MatrixDistinctType;
  }[] = [
    {
      matrixDistinctType: T.matrixModalDistinctNone,
      matrixDistinctEnum: MatrixDistinctType.None,
    },
    {
      matrixDistinctType: T.matrixModalDistinctRow,
      matrixDistinctEnum: MatrixDistinctType.Rows,
    },
    {
      matrixDistinctType: T.matrixModalDistinctColumn,
      matrixDistinctEnum: MatrixDistinctType.Cols,
    },
    {
      matrixDistinctType: T.matrixModalDistinctAll,
      matrixDistinctEnum: MatrixDistinctType.Both,
    },
  ];

  describe.each(matrixDistinctTypeMapping)(
    `When dropdown:`,
    ({ matrixDistinctType, matrixDistinctEnum }) => {
      it(`${matrixDistinctType} is selected, function should be called with ${matrixDistinctEnum}`, async () => {
        const wrapper = mount(CaseEdit, { localVue, store });

        const groupID = newGroup.groupID;
        const caseID = newCase.caseID;
        store.commit('casesStore/setSelected', {
          groupID,
          caseID,
        });
        await Vue.nextTick();
        const dropdowns = wrapper.findAll('a.dropdown-item');
        expect(dropdowns.length).toBe(4);

        await dropdowns.at(3).trigger('click');
        expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('matrix');

        const editIcon = wrapper.find(`button[data-line-edit-button]`);
        await editIcon.trigger('click');

        const modalBody = wrapper.find('div[data-matrix-modal]');

        const modalButton = modalBody.find(
          'button[data-matrix-modal-generate]',
        );
        expect(modalButton.exists).toBeTruthy();

        const mockGenerateMatrix = jest.spyOn(wrapper.vm, 'getMatrixContent');

        await modalBody
          .find(`a[data-matrix-modal-dropdown="${matrixDistinctType}"]`)
          .trigger('click');
        await modalButton.trigger('click');
        expect(mockGenerateMatrix).toHaveBeenCalledWith(
          3,
          3,
          0,
          100,
          matrixDistinctEnum,
        );
        mockGenerateMatrix.mockRestore();
      });
    },
  );

  it('deletes line, downloads .in and downloads .txt, when corresponding buttons are clicked', async () => {
    const wrapper = mount(CaseEdit, { localVue, store });

    const groupID = newGroup.groupID;
    const caseID = newCase.caseID;
    store.commit('casesStore/setSelected', {
      groupID,
      caseID,
    });
    store.dispatch('casesStore/deleteLinesForSelectedCase');
    await Vue.nextTick();

    const menuDropdown = wrapper.find('div[data-menu-dropdown]');
    expect(menuDropdown.exists()).toBeTruthy();

    // There are four buttons in the dropdown area.
    // - Toggle dropdown
    // - delete lines
    // - Download .in
    // - Download .txt
    const dropdownAreaButtonsCount = 4;

    const dropdownButtons = menuDropdown.findAll('button');
    expect(dropdownButtons.length).toBe(dropdownAreaButtonsCount);

    const deleteButton = menuDropdown.find('button[data-menu-delete-lines]');
    const downloadInButton = menuDropdown.find('button[data-menu-download-in]');
    const downloadTxtButton = menuDropdown.find(
      'button[data-menu-download-txt]',
    );

    expect(deleteButton.text()).toBe(T.problemCreatorLinesDelete);
    expect(downloadInButton.text()).toBe(T.problemCreatorCaseDownloadIn);
    expect(downloadTxtButton.text()).toBe(T.problemCreatorCaseDownloadTxt);

    wrapper.vm.addNewLine();
    await Vue.nextTick();

    // Only one line is added.
    expect(wrapper.vm.getLinesFromSelectedCase.length).toBe(1);

    await deleteButton.trigger('click');

    expect(wrapper.vm.getLinesFromSelectedCase.length).toBe(0);

    wrapper.vm.addNewLine();
    wrapper.vm.addNewLine();
    await Vue.nextTick();

    // Two lines are added, so!
    expect(wrapper.vm.getLinesFromSelectedCase.length).toBe(2);

    const dropdownsMultiline = wrapper.findAll(
      `a[data-array-modal-dropdown-kind]`,
    );

    // There are two dropdown items for multiline, one for each line.
    const fragment = 'multiline';
    const filteredDropdowns = dropdownsMultiline.filter((node) => {
      const nodeAttribute = node.attributes('data-array-modal-dropdown-kind');
      return nodeAttribute?.includes(fragment);
    });
    expect(filteredDropdowns.length).toBe(2);

    await filteredDropdowns.at(1).trigger('click');

    const formInputs = wrapper.findAll('input');
    const formTextArea = wrapper.find('textarea');

    await formInputs.at(0).setValue('testLabel');
    await formInputs.at(1).setValue('ome g a');

    await formInputs.at(2).setValue('testLabel');
    await formTextArea.setValue('u\np');

    expect(
      wrapper.vm.getStringifiedLinesFromCaseGroupID({
        groupID: groupID,
        caseID: caseID,
      }),
    ).toBe('ome g a\nu\np');

    const mockDownload = jest.spyOn(wrapper.vm, 'downloadInputFile');

    await downloadInButton.trigger('click');
    expect(mockDownload).toHaveBeenCalledWith('.in');

    await downloadTxtButton.trigger('click');
    expect(mockDownload).toHaveBeenCalledWith('.txt');

    expect(wrapper.emitted()['download-input-file']).toStrictEqual([
      [{ fileName: 'New Case.in', fileContent: 'ome g a\nu\np' }],
      [{ fileName: 'New Case.txt', fileContent: 'ome g a\nu\np' }],
    ]);

    mockDownload.mockRestore();
  });
});
