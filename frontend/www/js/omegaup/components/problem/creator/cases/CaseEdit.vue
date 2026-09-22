<template>
  <div v-if="getSelectedCase && getSelectedGroup">
    <div class="d-flex justify-content-between">
      <div>
        <h3 class="mb-0 d-md-inline me-2">{{ getSelectedCase.name }}</h3>
        <h5 class="mb-0 d-none d-md-inline text-muted">
          {{ getSelectedGroup.name }}
        </h5>
      </div>
      <div>
        <button
          type="button"
          class="btn btn-light me-2"
          @click="editCaseModal = !editCaseModal"
        >
          <div class="container">
            <div class="row">
              <font-awesome-icon
                icon="pencil-alt"
                class="text-info me-1 pt-1"
              />
              {{ T.problemCreatorEditCase }}
            </div>
          </div>
        </button>
        <div v-if="editCaseModal">
          <div class="modal fade show d-block" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">{{ T.caseEditTitle }}</h5>
                  <button
                    type="button"
                    class="btn-close"
                    @click="editCaseModal = false"
                  >
                    
                  </button>
                </div>
                <div class="modal-body">
                  <omegaup-problem-creator-case-input
                    ref="case-input"
                    :name="getSelectedCase.name"
                    :group="getSelectedGroup.groupID"
                    :points="getSelectedCase.points"
                    :auto-points="getSelectedCase.autoPoints"
                    :edit-mode="true"
                  />
                </div>
                <footer class="modal-footer">
                  <button
                    type="button"
                    class="btn btn-danger"
                    @click="editCaseModal = false"
                  >
                    {{ T.caseModalBack }}
                  </button>
                  <button
                    type="button"
                    class="btn btn-success"
                    @click="onUpdateCaseInfo"
                  >
                    {{ T.caseModalSave }}
                  </button>
                </footer>
              </div>
            </div>
          </div>
          <div class="modal-backdrop fade show"></div>
        </div>
        <button
          type="button"
          data-delete-case
          class="btn btn-light me-2"
          @click="
            deleteCase({
              groupID: getSelectedGroup.groupID,
              caseID: getSelectedCase.caseID,
            })
          "
        >
          <div class="container">
            <div class="row">
              <font-awesome-icon icon="trash-alt" class="text-danger me-1 pt-1" />
              {{ T.problemCreatorDeleteCase }}
            </div>
          </div>
        </button>
        <div ref="dropdown" data-menu-dropdown class="dropdown d-inline-block">
          <button
            type="button"
            class="btn btn-light"
            data-bs-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
          >
            <font-awesome-icon icon="ellipsis-v" />
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            <button
              type="button"
              data-menu-delete-lines
              class="dropdown-item"
              @click="deleteLines()"
            >
              <div class="d-flex">
                <font-awesome-icon icon="trash" class="text-danger pt-1 me-3" />
                {{ T.problemCreatorLinesDelete }}
              </div>
            </button>
            <div class="dropdown-divider"></div>
            <button
              type="button"
              data-menu-download-in
              class="dropdown-item"
              @click="downloadInputFile('.in')"
            >
              <div class="d-flex">
                <font-awesome-icon
                  icon="download"
                  class="text-info pt-1 me-3"
                />
                {{ T.problemCreatorCaseDownloadIn }}
              </div>
            </button>
            <button
              type="button"
              data-menu-download-txt
              class="dropdown-item"
              @click="downloadInputFile('.txt')"
            >
              <div class="d-flex">
                <font-awesome-icon
                  icon="align-left"
                  class="text-info pt-1 me-3"
                />
                {{ T.problemCreatorCaseDownloadTxt }}
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
    <hr class="border-top my-2" />
    <div>
      <table class="table">
        <draggable
          v-model="lines"
          tag="tbody"
          :animation="200"
          handle=".drag-handle"
        >
          <tr v-for="line in lines" :key="line.lineID">
            <td>
              <div class="container-fluid bg-light">
                <div
                  class="row d-flex justify-content-between align-items-center"
                >
                  <div class="col-1">
                    <button
                      class="btn btn-link drag-handle"
                      type="button"
                      :title="T.problemCreatorLinesReorder"
                    >
                      <font-awesome-icon icon="sort" />
                    </button>
                  </div>
                  <div class="col-2 ps-0 pe-2">
                    <input
                      v-model="line.label"
                      class="form-control form-control-sm"
                      :placeholder="T.problemCreatorLabelPlaceHolder"
                    />
                  </div>
                  <div class="col-5 pe-0 text-center">
                    <input
                      v-if="getLineDisplay(line) === LineDisplayOption.LINE"
                      v-model="line.data.value"
                      class="form-control form-control-sm mt-3 mb-3"
                      :placeholder="T.problemCreatorContentPlaceHolder"
                    />
                    <textarea
                      v-if="
                        getLineDisplay(line) === LineDisplayOption.MULTILINE
                      "
                      v-model="line.data.value"
                      class="form-control mt-3 mb-3 text-nowrap overflow-auto w-100"
                      rows="2"
                      :placeholder="T.problemCreatorContentPlaceHolder"
                    ></textarea>
                  </div>
                  <div class="col-3 ps-2 pe-0 text-center">
                    <div class="dropdown d-inline-block">
                      <button
                        :data-array-modal-dropdown="line.lineID"
                        type="button"
                        class="btn btn-light dropdown-toggle"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                      >
                        {{ getLineNameFromKind(line.data.kind) }}
                      </button>
                      <div class="dropdown-menu">
                        <a
                          v-for="lineKindOption in lineKindOptions"
                          :key="lineKindOption.kind"
                          class="dropdown-item"
                          href="#"
                          :data-array-modal-dropdown-kind="`${line.lineID}-${lineKindOption.kind}`"
                          @click.prevent="
                            editLineKind([line.lineID, lineKindOption.kind])
                          "
                        >
                          {{ lineKindOption.type }}
                        </a>
                      </div>
                    </div>
                    <button
                      v-if="
                        getEditIconDisplay(line) ===
                        EditIconDisplayOption.EDIT_ICON
                      "
                      :data-line-edit-button="line.lineID"
                      class="btn btn-light btn-sm"
                      type="button"
                      :title="T.problemCreatorLineEdit"
                      @click="editModalState(line.data.kind)"
                    >
                      <font-awesome-icon icon="pen-square" class="text-info" />
                    </button>
                    <div
                      v-if="arrayModalEdit && line.data.kind === 'array'"
                      data-array-modal
                    >
                      <div
                        class="modal fade show d-block"
                        tabindex="-1"
                        role="dialog"
                      >
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">{{ T.arrayEditTitle }}</h5>
                              <button
                                type="button"
                                class="btn-close"
                                @click="arrayModalEdit = false"
                              >
                                
                              </button>
                            </div>
                            <div class="modal-body">
                              <div class="container">
                                <div class="row mb-4">
                                  <div class="col text-start">
                                    {{ T.arrayModalSize }}
                                    <input
                                      v-model="line.data.size"
                                      type="number"
                                      data-array-modal-size
                                      class="form-control"
                                    />
                                  </div>
                                  <div class="col text-start">
                                    {{ T.arrayModalMinimum }}
                                    <input
                                      v-model="line.data.min"
                                      type="number"
                                      data-array-modal-min
                                      class="form-control"
                                    />
                                  </div>
                                  <div class="col text-start">
                                    {{ T.arrayModalMaximum }}
                                    <input
                                      v-model="line.data.max"
                                      type="number"
                                      data-array-modal-max
                                      class="form-control"
                                    />
                                  </div>
                                </div>
                                <div class="row mt-2 mb-4">
                                  <div class="col text-start">
                                    <input
                                      id="array-modal-distinct"
                                      v-model="line.data.distinct"
                                      type="checkbox"
                                      data-array-modal-checkbox
                                    />
                                    <label
                                      class="ms-1"
                                      for="array-modal-distinct"
                                    >
                                      {{ T.arrayModalDistinctValues }}
                                    </label>
                                  </div>
                                </div>
                                <div class="row mt-4">
                                  <div class="col text-start">
                                    <button
                                      type="button"
                                      class="btn btn-primary"
                                      data-array-modal-generate
                                      @click="
                                        arrayModalEditArray = getArrayContent(
                                          Number(line.data.size),
                                          Number(line.data.min),
                                          Number(line.data.max),
                                          line.data.distinct,
                                        )
                                      "
                                    >
                                      {{ T.arrayModalGenerate }}
                                    </button>
                                  </div>
                                </div>
                                <hr />
                                <div class="text-start">
                                  {{ T.arrayModalGeneratedArray }}
                                </div>
                                <input
                                  v-model="arrayModalEditArray"
                                  data-array-modal-generated-array
                                  class="form-control w-100"
                                />
                              </div>
                            </div>
                            <footer class="modal-footer">
                              <button
                                type="button"
                                class="btn btn-danger"
                                @click="arrayModalEdit = false"
                              >
                                {{ T.arrayModalBack }}
                              </button>
                              <button
                                type="button"
                                class="btn btn-success"
                                @click="onSaveArray(line.lineID)"
                              >
                                {{ T.arrayModalSave }}
                              </button>
                            </footer>
                          </div>
                        </div>
                      </div>
                      <div class="modal-backdrop fade show"></div>
                    </div>
                    <div
                      v-if="matrixModalEdit && line.data.kind === 'matrix'"
                      data-matrix-modal
                    >
                      <div
                        class="modal fade show d-block"
                        tabindex="-1"
                        role="dialog"
                      >
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">
                                {{ T.matrixEditTitle }}
                              </h5>
                              <button
                                type="button"
                                class="btn-close"
                                @click="matrixModalEdit = false"
                              >
                                
                              </button>
                            </div>
                            <div class="modal-body">
                              <div class="container">
                                <div class="row mb-4">
                                  <div class="col text-start">
                                    {{ T.matrixModalRows }}
                                    <input
                                      v-model="line.data.rows"
                                      type="number"
                                      data-matrix-modal-rows
                                      class="form-control"
                                    />
                                  </div>
                                  <div class="col">
                                    {{ T.matrixModalColumns }}
                                    <input
                                      v-model="line.data.cols"
                                      type="number"
                                      data-matrix-modal-columns
                                      class="form-control"
                                    />
                                  </div>
                                  <div class="col text-start">
                                    {{ T.matrixModalMinimum }}
                                    <input
                                      v-model="line.data.min"
                                      type="number"
                                      data-matrix-modal-min
                                      class="form-control"
                                    />
                                  </div>
                                  <div class="col">
                                    {{ T.matrixModalMaximum }}
                                    <input
                                      v-model="line.data.max"
                                      type="number"
                                      data-matrix-modal-max
                                      class="form-control"
                                    />
                                  </div>
                                </div>
                                <div class="row mt-2 mb-4">
                                  <div class="col text-start">
                                    <div class="mb-1 fw-bold">
                                      {{ T.matrixModalDistinct }}
                                    </div>
                                    <div class="dropdown d-inline-block">
                                      <button
                                        type="button"
                                        class="btn btn-light dropdown-toggle"
                                        data-matrix-modal-dropdown
                                        data-bs-toggle="dropdown"
                                        aria-haspopup="true"
                                        aria-expanded="false"
                                      >
                                        {{
                                          getDistinctNameFromType(
                                            line.data.distinct,
                                          )
                                        }}
                                      </button>
                                      <div class="dropdown-menu">
                                        <h6 class="dropdown-header">
                                          {{ T.matrixModalDistinctHeader }}
                                        </h6>
                                        <a
                                          v-for="matrixDistinctOption in matrixDistinctOptions"
                                          :key="
                                            matrixDistinctOption.distinctType
                                          "
                                          class="dropdown-item"
                                          href="#"
                                          :data-matrix-modal-dropdown="
                                            matrixDistinctOption.type
                                          "
                                          @click.prevent="
                                            line.data.distinct =
                                              matrixDistinctOption.distinctType
                                          "
                                        >
                                          {{ matrixDistinctOption.type }}
                                        </a>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="row mt-4">
                                  <div class="col text-start">
                                    <button
                                      type="button"
                                      class="btn btn-primary"
                                      data-matrix-modal-generate
                                      @click="
                                        matrixModalEditArray = getMatrixContent(
                                          Number(line.data.rows),
                                          Number(line.data.cols),
                                          Number(line.data.min),
                                          Number(line.data.max),
                                          line.data.distinct,
                                        )
                                      "
                                    >
                                      {{ T.matrixModalGenerate }}
                                    </button>
                                  </div>
                                </div>
                                <hr />
                                <div class="text-start">
                                  {{ T.matrixModalGeneratedArray }}
                                </div>
                                <textarea
                                  v-model="matrixModalEditArray"
                                  data-matrix-modal-generated-matrix
                                  class="form-control w-100"
                                ></textarea>
                              </div>
                            </div>
                            <footer class="modal-footer">
                              <button
                                type="button"
                                class="btn btn-danger"
                                @click="matrixModalEdit = false"
                              >
                                {{ T.matrixModalBack }}
                              </button>
                              <button
                                type="button"
                                class="btn btn-success"
                                @click="onSaveMatrix(line.lineID)"
                              >
                                {{ T.matrixModalSave }}
                              </button>
                            </footer>
                          </div>
                        </div>
                      </div>
                      <div class="modal-backdrop fade show"></div>
                    </div>
                  </div>
                  <div class="col-1">
                    <button
                      class="btn btn-light btn-sm"
                      type="button"
                      :title="T.problemCreatorLineDelete"
                      @click="deleteLine(line.lineID)"
                    >
                      <font-awesome-icon
                        icon="trash-alt"
                        class="text-danger"
                      />
                    </button>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        </draggable>
        <tbody>
          <tr>
            <td>
              <div class="container-fluid bg-light">
                <div
                  class="row d-flex justify-content-between align-items-center"
                >
                  <div class="col pe-1 text-center">
                    <textarea
                      v-model="getSelectedCase.output"
                      data-output-textarea
                      class="form-control mt-3 mb-3 text-nowrap overflow-auto w-100"
                      rows="2"
                      :placeholder="T.problemCreatorOutputPlaceHolder"
                    >
                    </textarea>
                  </div>
                  <div class="col">
                    <button
                      data-erase-output
                      class="btn text-danger btn-lg"
                      type="button"
                      :title="T.problemCreatorEraseOutput"
                      @click="getSelectedCase.output = ''"
                    >
                      <font-awesome-icon icon="eraser" />
                    </button>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="text-center">
      <button
        type="button"
        data-edit-case-add-line
        class="btn btn-light me-2"
        @click="addNewLine"
      >
        <div class="container">
          <div class="row">
            <font-awesome-icon
              icon="plus-square"
              class="text-info me-2 pt-1"
            />
            {{ T.problemCreatorAddLine }}
          </div>
        </div>
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue, Ref } from 'vue-property-decorator';
import T from '../../../../lang';
import problemCreator_Cases_CaseInput from './CaseInput.vue';
import { namespace } from 'vuex-class';
import {
  Case,
  Group,
  CaseLineKind,
  CaseLine,
  LineID,
  CaseGroupID,
  MatrixDistinctType,
  CaseRequest,
  GroupID,
} from '@/js/omegaup/problem/creator/types';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
import draggable from 'vuedraggable';
library.add(fas);

const casesStore = namespace('casesStore');

@Component({
  components: {
    'omegaup-problem-creator-case-input': problemCreator_Cases_CaseInput,
    'font-awesome-icon': FontAwesomeIcon,
    draggable: draggable,
  },
})
export default class CaseEdit extends Vue {
  T = T;
  editCaseModal = false;

  @Ref('case-input') caseInputRef!: problemCreator_Cases_CaseInput;

  arrayModalEdit: boolean = false;
  matrixModalEdit: boolean = false;

  arrayModalEditArray: string = '';
  matrixModalEditArray: string = '';

  @casesStore.State('groups') groups!: Group[];
  @casesStore.Getter('getSelectedCase') getSelectedCase!: Case;
  @casesStore.Getter('getLinesFromSelectedCase')
  getLinesFromSelectedCase!: CaseLine[];
  @casesStore.Getter('getSelectedGroup') getSelectedGroup!: Group;

  @casesStore.Action('editLineKind') editLineKind!: ([lineID, kind]: [
    LineID,
    CaseLineKind,
  ]) => void;
  @casesStore.Action('editLineValue') editLineValue!: ([lineID, value]: [
    LineID,
    string,
  ]) => void;
  @casesStore.Mutation('deleteCase') deleteCase!: ({
    groupID,
    caseID,
  }: CaseGroupID) => void;
  @casesStore.Mutation('updateCase') updateCase!: ([
    oldGroupID,
    updateCaseRequest,
  ]: [GroupID, CaseRequest]) => void;
  @casesStore.Getter('getStringifiedLinesFromCaseGroupID')
  getStringifiedLinesFromCaseGroupID!: (caseGroupID: CaseGroupID) => string;

  @casesStore.Action('addNewLine') addNewLine!: () => void;
  @casesStore.Action('setLines') setLines!: (lines: CaseLine[]) => void;
  @casesStore.Action('deleteLine') deleteLine!: (line: LineID) => void;
  @casesStore.Action('deleteLinesForSelectedCase')
  deleteLinesForSelectedCase!: () => void;

  deleteLines() {
    this.deleteLinesForSelectedCase();
    this.hideMenuDropdown();
  }

  hideMenuDropdown() {
    const dropdown = this.$refs.dropdown as HTMLElement | undefined;
    if (!dropdown || !dropdown.classList) {
      return;
    }
    dropdown.classList.remove('show');
    dropdown.querySelector('.dropdown-menu')?.classList.remove('show');
  }

  onUpdateCaseInfo() {
    this.updateCaseInfo();
    this.editCaseModal = false;
  }

  onSaveArray(lineID: LineID) {
    this.editLineValue([lineID, this.arrayModalEditArray]);
    this.arrayModalEditArray = '';
    this.arrayModalEdit = false;
  }

  onSaveMatrix(lineID: LineID) {
    this.editLineValue([lineID, this.matrixModalEditArray]);
    this.matrixModalEditArray = '';
    this.matrixModalEdit = false;
  }

  LineDisplayOption = Object.freeze({
    LINE: 'line',
    MULTILINE: 'multiline',
  });

  EditIconDisplayOption = Object.freeze({
    EDIT_ICON: 'edit_icon',
  });

  get getLineDisplay() {
    return (line: CaseLine) => {
      if (line.data.kind === 'line' || line.data.kind === 'array') {
        return this.LineDisplayOption.LINE;
      }
      return this.LineDisplayOption.MULTILINE;
    };
  }

  get getEditIconDisplay() {
    return (line: CaseLine) => {
      if (line.data.kind === 'array' || line.data.kind === 'matrix') {
        return this.EditIconDisplayOption.EDIT_ICON;
      }
    };
  }

  get lines(): CaseLine[] {
    return this.getLinesFromSelectedCase;
  }

  set lines(newLines: CaseLine[]) {
    this.setLines(newLines);
  }

  lineKindOptions: {
    type: string;
    kind: CaseLineKind;
  }[] = [
    { type: T.problemCreatorLineLine, kind: 'line' },
    { type: T.problemCreatorLineMultiline, kind: 'multiline' },
    { type: T.problemCreatorLineArray, kind: 'array' },
    { type: T.problemCreatorLineMatrix, kind: 'matrix' },
  ];

  matrixDistinctOptions: {
    type: string;
    distinctType: MatrixDistinctType;
  }[] = [
    { type: T.matrixModalDistinctNone, distinctType: MatrixDistinctType.None },
    { type: T.matrixModalDistinctRow, distinctType: MatrixDistinctType.Rows },
    {
      type: T.matrixModalDistinctColumn,
      distinctType: MatrixDistinctType.Cols,
    },
    { type: T.matrixModalDistinctAll, distinctType: MatrixDistinctType.Both },
  ];

  editModalState(kind: CaseLineKind): void {
    if (kind === 'array') {
      this.matrixModalEdit = false;
      this.arrayModalEdit = true;
    } else if (kind === 'matrix') {
      this.arrayModalEdit = false;
      this.matrixModalEdit = true;
    }
  }

  getLineNameFromKind(kind: CaseLineKind): string | undefined {
    return this.lineKindOptions.find((row) => row.kind === kind)?.type;
  }

  getDistinctNameFromType(distinctype: MatrixDistinctType): string | undefined {
    return this.matrixDistinctOptions.find(
      (row) => row.distinctType === distinctype,
    )?.type;
  }

  getDistinctArrayContents(
    size: number,
    low: number = 0,
    high: number = 0,
  ): string {
    const generatedArray = new Set<number>();
    while (generatedArray.size < size) {
      generatedArray.add(low + Math.floor(Math.random() * (high - low + 1)));
    }
    return [...generatedArray].join(' ');
  }

  getNonDistinctArrayContents(
    size: number,
    low: number = 0,
    high: number = 0,
  ): string {
    const generatedArray = [];
    while (generatedArray.length < size) {
      generatedArray.push(low + Math.floor(Math.random() * (high - low + 1)));
    }
    return [...generatedArray].join(' ');
  }

  getArrayContent(
    size: number,
    low: number = 0,
    high: number = 0,
    distinct: boolean = false,
  ): string {
    if (distinct && high - low + 1 < size) {
      return '';
    }
    if (distinct) {
      return this.getDistinctArrayContents(size, low, high);
    } else {
      return this.getNonDistinctArrayContents(size, low, high);
    }
  }

  getNoneDistinctMatrixContents(
    rows: number,
    columns: number,
    low: number = 0,
    high: number = 0,
  ): string {
    const generatedArray: number[] = this.getNonDistinctArrayContents(
      rows * columns,
      low,
      high,
    )
      .split(' ')
      .map(Number);

    let matrix = [];
    let index = 0;

    for (let i = 0; i < rows; i++) {
      let row = [];
      for (let j = 0; j < columns; j++) {
        row.push(generatedArray[index]);
        index++;
      }
      matrix.push(row);
    }

    return matrix.map((row) => row.join(' ')).join('\n');
  }

  getRowsDistinctMatrixContents(
    rows: number,
    columns: number,
    low: number = 0,
    high: number = 0,
  ): string {
    const generatedRows: Set<number>[] = Array.from(
      { length: rows },
      () => new Set<number>(),
    );
    for (let i = 0; i < rows; i++) {
      while (generatedRows[i].size < columns) {
        generatedRows[i].add(
          low + Math.floor(Math.random() * (high - low + 1)),
        );
      }
    }
    return generatedRows.map((row) => [...row].join(' ')).join('\n');
  }

  getColsDistinctMatrixContents(
    rows: number,
    columns: number,
    low: number = 0,
    high: number = 0,
  ): string {
    const generatedColumns: Set<number>[] = Array.from(
      { length: columns },
      () => new Set<number>(),
    );
    for (let i = 0; i < columns; i++) {
      while (generatedColumns[i].size < rows) {
        generatedColumns[i].add(
          low + Math.floor(Math.random() * (high - low + 1)),
        );
      }
    }
    const generatedColumnsList: number[][] = generatedColumns.map((column) =>
      Array.from(column),
    );
    const transposedColumnsList: number[][] = generatedColumnsList[0].map(
      (_, rowIndex) => generatedColumnsList.map((column) => column[rowIndex]),
    );
    return transposedColumnsList.map((row) => row.join(' ')).join('\n');
  }

  getAllDistinctMatrixContents(
    rows: number,
    columns: number,
    low: number = 0,
    high: number = 0,
  ): string {
    const generatedArray: number[] = this.getDistinctArrayContents(
      rows * columns,
      low,
      high,
    )
      .split(' ')
      .map(Number);

    let matrix = [];
    let index = 0;

    for (let i = 0; i < rows; i++) {
      let row = [];
      for (let j = 0; j < columns; j++) {
        row.push(generatedArray[index]);
        index++;
      }
      matrix.push(row);
    }

    return matrix.map((row) => row.join(' ')).join('\n');
  }

  getMatrixContent(
    rows: number,
    columns: number,
    low: number = 0,
    high: number = 100,
    distinct: MatrixDistinctType = MatrixDistinctType.None,
  ): string {
    if (distinct === 'both' && high - low + 1 < rows * columns) {
      return '';
    }
    if (distinct === 'rows' && high - low + 1 < columns) {
      return '';
    }
    if (distinct === 'cols' && high - low + 1 < rows) {
      return '';
    }
    if (distinct === 'none') {
      return this.getNoneDistinctMatrixContents(rows, columns, low, high);
    }
    if (distinct === 'both') {
      return this.getAllDistinctMatrixContents(rows, columns, low, high);
    }
    if (distinct === 'rows') {
      return this.getRowsDistinctMatrixContents(rows, columns, low, high);
    }
    if (distinct === 'cols') {
      return this.getColsDistinctMatrixContents(rows, columns, low, high);
    }
    return '';
  }

  updateCaseInfo() {
    const updateCaseRequest: CaseRequest = {
      groupID: this.caseInputRef.caseGroup,
      caseID: this.getSelectedCase.caseID,
      name: this.caseInputRef.caseName,
      points: this.caseInputRef.casePoints,
      autoPoints: this.caseInputRef.caseAutoPoints,
    };
    const oldGroupID: GroupID = this.getSelectedGroup.groupID;
    this.updateCase([oldGroupID, updateCaseRequest]);
  }

  downloadInputFile(ext: '.txt' | '.in') {
    const caseGroupID: CaseGroupID = {
      groupID: this.getSelectedGroup.groupID,
      caseID: this.getSelectedCase.caseID,
    };
    const input = this.getStringifiedLinesFromCaseGroupID(caseGroupID);
    this.$emit('download-input-file', {
      fileName: `${this.getSelectedCase.name}${ext}`,
      fileContent: input,
    });
  }
}
</script>

<style lang="scss" scoped>
.table td {
  vertical-align: middle;
  border: none;
}
</style>
