import { createLocalVue, shallowMount, mount } from '@vue/test-utils';

import CaseEdit from './CaseEdit.vue';
import BootstrapVue, { IconsPlugin, BButton } from 'bootstrap-vue';
import store from '@/js/omegaup/problem/creator/store';
import Vue from 'vue';
import {
  generateCase,
  generateGroup,
} from '@/js/omegaup/problem/creator/modules/cases';

import T from '../../../../lang';

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

    const buttons = wrapper.findAllComponents(BButton);
    expect(buttons.length).toBe(4);

    expect(wrapper.text()).toContain(newUngroupedCase.name);
    expect(wrapper.text()).toContain(newUngroupedCasegroup.name);

    expect(
      wrapper.find('biconpencilfill-stub').element.parentElement?.textContent,
    ).toContain(T.problemCreatorEditCase);
    expect(
      wrapper.find('bicontrashfill-stub').element.parentElement?.textContent,
    ).toContain(T.problemCreatorDeleteCase);
    expect(wrapper.find('biconthreedotsvertical-stub').exists()).toBe(true);
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

    const buttons = wrapper.findAllComponents(BButton);
    expect(buttons.length).toBe(4);

    expect(wrapper.text()).toContain(newCase.name);
    expect(wrapper.text()).toContain(newGroup.name);

    expect(
      wrapper.find('biconpencilfill-stub').element.parentElement?.textContent,
    ).toContain(T.problemCreatorEditCase);
    expect(
      wrapper.find('bicontrashfill-stub').element.parentElement?.textContent,
    ).toContain(T.problemCreatorDeleteCase);
    expect(wrapper.find('biconthreedotsvertical-stub').exists()).toBe(true);
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

    const buttons = wrapper.findAllComponents(BButton);
    expect(buttons.length).toBe(4);

    expect(wrapper.text()).toContain(newCase.name);
    expect(wrapper.text()).toContain(newGroup.name);

    wrapper.vm.addNewLine();
    await Vue.nextTick();

    expect(wrapper.find('table').exists()).toBe(true);
    const formInputs = wrapper.findAll('input');

    formInputs.at(0).setValue('testLabel');
    formInputs.at(1).setValue('testValue');
    await wrapper.trigger('click');

    expect(wrapper.vm.getLinesFromSelectedCase[0].label).toBe('testLabel');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.value).toBe('testValue');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('line');

    const dropdowns = wrapper.findAll('a.dropdown-item');
    expect(dropdowns.length).toBe(4);

    await dropdowns.at(1).trigger('click');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('multiline');

    await dropdowns.at(2).trigger('click');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('array');

    await dropdowns.at(3).trigger('click');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('matrix');

    wrapper.vm.deleteLine(wrapper.vm.getLinesFromSelectedCase[0].lineID);
    await Vue.nextTick();

    const formInputsUpdated = wrapper.findAll('input');
    expect(formInputsUpdated.length).toBe(0);
  });

  it('Should generate arrays and matrices', async () => {
    const wrapper = mount(CaseEdit, { localVue, store });

    const arrSize = 10;
    const arrlow = 0;
    const arrHigh = 9;

    let array = wrapper.vm
      .getArrayContent(arrSize, arrlow, arrHigh, false)
      .split(' ')
      .map(Number);

    expect(array.length).toBe(arrSize);
    expect(array.every((num) => num >= arrlow && num <= arrHigh)).toBeTruthy();

    array = wrapper.vm
      .getArrayContent(arrSize, arrlow, arrHigh, true)
      .split(' ')
      .map(Number);

    expect(array.length).toBe(arrSize);
    expect(array.every((num) => num >= arrlow && num <= arrHigh));
    expect(new Set(array).size).toBe(arrSize);

    const emptyArray = wrapper.vm.getArrayContent(
      arrSize,
      arrlow,
      arrHigh - 1,
      true,
    );

    expect(emptyArray).toBe('');

    const matrixRows = 10;
    const matrixColumns = 9;
    const matrixLow = 0;
    const matrixHigh = 89;

    let matrix = wrapper.vm
      .getMatrixContent(
        matrixRows,
        matrixColumns,
        matrixLow,
        matrixHigh,
        'none',
      )
      .trim()
      .split('\n')
      .map((row) => row.trim().split(' ').map(Number));

    expect(matrix.length).toBe(matrixRows);
    expect(matrix[0].length).toBe(matrixColumns);
    expect(
      matrix.every((row) =>
        row.every((num) => num >= matrixLow && num <= matrixHigh),
      ),
    ).toBeTruthy();

    matrix = wrapper.vm
      .getMatrixContent(
        matrixRows,
        matrixColumns,
        matrixLow,
        matrixHigh,
        'rows',
      )
      .trim()
      .split('\n')
      .map((row) => row.trim().split(' ').map(Number));

    expect(matrix.length).toBe(matrixRows);
    expect(matrix[0].length).toBe(matrixColumns);
    expect(
      matrix.every((row) =>
        row.every((num) => num >= matrixLow && num <= matrixHigh),
      ),
    ).toBe(true);
    expect(matrix.every((row) => new Set(row).size === matrixColumns)).toBe(
      true,
    );

    matrix = wrapper.vm
      .getMatrixContent(
        matrixRows,
        matrixColumns,
        matrixLow,
        matrixHigh,
        'cols',
      )
      .trim()
      .split('\n')
      .map((row) => row.trim().split(' ').map(Number));

    expect(matrix.length).toBe(matrixRows);
    expect(matrix[0].length).toBe(matrixColumns);
    expect(
      matrix.every((row) =>
        row.every((num) => num >= matrixLow && num <= matrixHigh),
      ),
    ).toBe(true);
    expect(
      matrix[0]
        .map((_, colIndex) => matrix.map((row) => row[colIndex]))
        .every((row) => new Set(row).size === matrixRows),
    ).toBe(true);

    matrix = wrapper.vm
      .getMatrixContent(
        matrixRows,
        matrixColumns,
        matrixLow,
        matrixHigh,
        'both',
      )
      .trim()
      .split('\n')
      .map((row) => row.trim().split(' ').map(Number));

    expect(matrix.length).toBe(matrixRows);
    expect(matrix[0].length).toBe(matrixColumns);
    expect(
      matrix.every((row) =>
        row.every((num) => num >= matrixLow && num <= matrixHigh),
      ),
    ).toBe(true);
    expect(matrix.every((row) => new Set(row).size === matrixColumns)).toBe(
      true,
    );
    expect(
      matrix[0]
        .map((_, colIndex) => matrix.map((row) => row[colIndex]))
        .every((row) => new Set(row).size === matrixRows),
    ).toBe(true);

    let emptyMatrix = wrapper.vm.getMatrixContent(
      matrixRows,
      matrixColumns,
      matrixLow,
      matrixLow + matrixColumns - 2,
      'rows',
    );

    expect(emptyMatrix).toBe('');

    emptyMatrix = wrapper.vm.getMatrixContent(
      matrixRows,
      matrixColumns,
      matrixLow,
      matrixLow + matrixRows - 2,
      'cols',
    );

    expect(emptyMatrix).toBe('');

    emptyMatrix = wrapper.vm.getMatrixContent(
      matrixRows,
      matrixColumns,
      matrixLow,
      matrixHigh - 1,
      'both',
    );

    expect(emptyMatrix).toBe('');

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
    expect(dropdowns.length).toBe(4);

    await dropdowns.at(2).trigger('click');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('array');

    const editSVG = wrapper.find('svg.bi-pencil-square');
    expect(editSVG.exists()).toBe(true);

    let editIcon = wrapper.find(`button[title="${T.problemCreatorLineEdit}"]`);
    await editIcon.trigger('click');

    let modalBody = wrapper.find('div.modal-body');

    let modalInputs = modalBody.findAll('input');
    expect(modalInputs.length).toBe(5);

    let modalButtons = modalBody.findAll('button');
    expect(modalButtons.length).toBe(1);

    const mockGenerate = jest.spyOn(wrapper.vm, 'getArrayContent');

    await modalButtons.at(0).trigger('click');
    expect(mockGenerate).toHaveBeenCalledWith(10, 0, 100, false);

    await modalInputs.at(0).setValue(5);
    await modalInputs.at(1).setValue(10);
    await modalInputs.at(2).setValue(20);
    await modalInputs.at(3).setChecked(true);

    await modalButtons.at(0).trigger('click');
    expect(mockGenerate).toHaveBeenCalledWith(5, 10, 20, true);
    mockGenerate.mockRestore();

    await modalInputs.at(0).setValue(5);
    await modalInputs.at(1).setValue(10);
    await modalInputs.at(2).setValue(10);
    await modalInputs.at(3).setChecked(false);

    await modalButtons.at(0).trigger('click');

    expect(
      (modalBody.findAll('input').at(4).element as HTMLInputElement).value,
    ).toBe('10 10 10 10 10');

    let modalFooter = wrapper.find('footer.modal-footer');

    let footerButtons = modalFooter.findAll('button');
    expect(footerButtons.length).toBe(2);

    await footerButtons.at(1).trigger('click');

    expect(wrapper.vm.getLinesFromSelectedCase[0].data.value).toBe(
      '10 10 10 10 10',
    );

    await dropdowns.at(3).trigger('click');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('matrix');

    editIcon = wrapper.find('button[title="Editar"]');
    await editIcon.trigger('click');

    modalBody = wrapper.find('div.modal-body');

    modalInputs = modalBody.findAll('input');
    expect(modalInputs.length).toBe(4);

    modalButtons = modalBody.findAll('button');
    expect(modalButtons.length).toBe(2);

    const mockGenerateMatrix = jest.spyOn(wrapper.vm, 'getMatrixContent');

    await modalButtons.at(1).trigger('click');
    expect(mockGenerateMatrix).toHaveBeenCalledWith(3, 3, 0, 100, 'none');
    mockGenerateMatrix.mockRestore();

    await modalInputs.at(0).setValue(3);
    await modalInputs.at(1).setValue(3);
    await modalInputs.at(2).setValue(20);
    await modalInputs.at(3).setValue(20);

    await modalButtons.at(1).trigger('click');
    expect(
      (modalBody.findAll('textarea').at(0).element as HTMLInputElement).value,
    ).toBe('20 20 20\n20 20 20\n20 20 20');

    modalFooter = wrapper.find('footer.modal-footer');

    footerButtons = modalFooter.findAll('button');
    expect(footerButtons.length).toBe(2);

    wrapper.vm.editLineValue([
      wrapper.vm.getLinesFromSelectedCase[0].lineID,
      wrapper.vm.matrixModalEditArray,
    ]);

    expect(wrapper.vm.getLinesFromSelectedCase[0].data.value).toBe(
      '20 20 20\n20 20 20\n20 20 20',
    );
  });
});
