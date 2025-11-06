<template>
  <div v-if="getSelectedCase && getSelectedGroup">
    <div class="d-flex justify-content-between">
      <div>
        <h3 class="mb-0 d-md-inline mr-2">{{ getSelectedCase.name }}</h3>
        <h5 class="mb-0 d-none d-md-inline text-muted">
          {{ getSelectedGroup.name }}
        </h5>
      </div>
      <div v-if="!confirmingDelete">
        <b-button
          variant="light"
          class="mr-2"
          @click="editCaseModal = !editCaseModal"
        >
          <div class="container">
            <div class="row">
              <BIconPencilFill
                variant="info"
                font-scale="1.10"
                class="mr-1 pt-1"
              />
              {{ T.problemCreatorEditCase }}
            </div>
          </div>
        </b-button>
        <b-modal
          v-model="editCaseModal"
          :title="T.caseEditTitle"
          :ok-title="T.caseModalSave"
          ok-variant="success"
          :cancel-title="T.caseModalBack"
          cancel-variant="danger"
          static
          lazy
          @ok="updateCaseInfo"
        >
          <omegaup-problem-creator-case-input
            ref="case-input"
            :name="getSelectedCase.name"
            :group="getSelectedGroup.groupID"
            :points="getSelectedCase.points"
            :auto-points="getSelectedCase.autoPoints"
            :edit-mode="true"
          />
        </b-modal>
        <b-button
          data-delete-case
          variant="light"
          class="mr-2"
          @click="handleDeleteClick"
        >
          <div class="container">
            <div class="row">
              <BIconTrashFill
                variant="danger"
                font-scale="1.20"
                class="mr-1 pt-1"
              />
              {{ T.problemCreatorDeleteCase }}
            </div>
          </div>
        </b-button>
        <b-dropdown variant="light" class="mr-2" right no-caret>
          <template #button-content>
            {{ 'Generar input' }}
          </template>

          <b-button
            data-menu-generate-array
            :disabled="isInputTruncated"
            variant="light"
            class="w-100"
            @click="arrayModalEdit = !arrayModalEdit"
          >
            {{ T.problemCreatorLineArray }}
          </b-button>
          <b-button
            data-menu-generate-matrix
            :disabled="isInputTruncated"
            variant="light"
            class="w-100"
            @click="matrixModalEdit = !matrixModalEdit"
          >
            {{ T.problemCreatorLineMatrix }}
          </b-button>
        </b-dropdown>

        <b-dropdown
          ref="dropdown"
          data-menu-dropdown
          variant="light"
          class="h-100"
          right
          no-caret
        >
          <template #button-content>
            <BIconThreeDotsVertical />
          </template>
          <b-button
            data-menu-download-in
            variant="light"
            class="w-100"
            @click="downloadInputFile('.in')"
          >
            <div class="d-flex">
              <BIconBoxArrowDown
                variant="info"
                class="pt-1 mr-3"
                font-scale="1.2"
              />
              {{ T.problemCreatorCaseDownloadIn }}
            </div>
          </b-button>
          <b-button
            data-menu-download-txt
            variant="light"
            class="w-100"
            @click="downloadInputFile('.txt')"
          >
            <div class="d-flex">
              <BIconTextLeft
                variant="info"
                class="pt-1 mr-3"
                font-scale="1.2"
              />
              {{ T.problemCreatorCaseDownloadTxt }}
            </div>
          </b-button>
        </b-dropdown>
      </div>
    </div>
    <delete-confirmation-form
      v-if="isEditing"
      :visible="confirmingDelete"
      :item-name="itemNameForDelete"
      :item-id="getSelectedCase.caseID"
      :on-cancel="cancelDelete"
    />
    <hr class="border-top my-2" />
    <b-alert
      v-if="isInputTruncated || isOutputTruncated"
      variant="warning"
      show
      class="mb-2"
    >
      {{
        'Please note that the input or output content is truncated. Editing is disabled to avoid memory overload.'
      }}
    </b-alert>
    <div>
      <table class="table">
        <tbody>
          <tr>
            <td>
              <b-container fluid class="bg-light">
                <b-row class="d-flex justify-content-between" align-v="center">
                  <b-col class="pr-1 text-center">
                    <h5>Input</h5>
                    <b-form-textarea
                      v-model="inputText"
                      :disabled="isInputTruncated"
                      data-input-textarea
                      class="mt-3 mb-3 text-nowrap overflow-auto w-100"
                      rows="5"
                      max-rows="7"
                      :placeholder="T.problemCreatorContentPlaceHolder"
                    />
                  </b-col>
                  <b-col cols="1.5">
                    <b-button
                      data-erase-input
                      :disabled="isInputTruncated"
                      class="btn text-danger btn-lg"
                      type="button"
                      :title="T.problemCreatorEraseOutput"
                      variant="light"
                      @click="clearInput()"
                    >
                      <font-awesome-icon icon="eraser" />
                    </b-button>
                  </b-col>
                </b-row>
              </b-container>
            </td>
          </tr>
          <tr>
            <td>
              <b-container fluid class="bg-light">
                <b-row class="d-flex justify-content-between" align-v="center">
                  <b-col class="pr-1 text-center">
                    <h5>Output</h5>
                    <b-form-textarea
                      v-model="getSelectedCase.output"
                      :disabled="isOutputTruncated"
                      data-output-textarea
                      class="mt-3 mb-3 text-nowrap overflow-auto w-100"
                      rows="5"
                      max-rows="7"
                      :placeholder="T.problemCreatorOutputPlaceHolder"
                    >
                    </b-form-textarea>
                  </b-col>
                  <b-col cols="1.5">
                    <b-button
                      data-erase-output
                      :disabled="isOutputTruncated"
                      class="btn text-danger btn-lg"
                      type="button"
                      :title="T.problemCreatorEraseOutput"
                      variant="light"
                      @click="getSelectedCase.output = ''"
                    >
                      <font-awesome-icon icon="eraser" />
                    </b-button>
                  </b-col>
                </b-row>
              </b-container>
            </td>
          </tr>
        </tbody>
      </table>
      <CaseSimpleForm
        v-if="isEditing"
        :is-truncated-input="isInputTruncated"
        :is-truncated-output="isOutputTruncated"
        :is-case-edit="true"
      />
    </div>
    <b-modal
      v-model="arrayModalEdit"
      data-array-modal
      :title="T.arrayEditTitle"
      :ok-title="T.arrayModalSave"
      ok-variant="success"
      :cancel-title="T.arrayModalBack"
      cancel-variant="danger"
      static
      lazy
      @ok="
        getSelectedCase.input = arrayModalEditArray;
        arrayModalEditArray = '';
      "
    >
      <b-container>
        <b-row class="mb-4">
          <b-col class="text-left">
            {{ T.arrayModalSize }}
            <b-form-input
              v-model="arraySize"
              type="number"
              data-array-modal-size
            />
          </b-col>
          <b-col class="text-left">
            {{ T.arrayModalMinimum }}
            <b-form-input
              v-model="arrayMin"
              type="number"
              data-array-modal-min
            />
          </b-col>
          <b-col class="text-left">
            {{ T.arrayModalMaximum }}
            <b-form-input
              v-model="arrayMax"
              type="number"
              data-array-modal-max
            />
          </b-col>
        </b-row>

        <b-row class="mt-2 mb-4">
          <b-col class="text-left">
            <b-form-checkbox v-model="arrayDistinct" data-array-modal-checkbox>
              {{ T.arrayModalDistinctValues }}
            </b-form-checkbox>
          </b-col>
        </b-row>

        <b-row class="mt-4">
          <b-col class="text-left">
            <b-button
              variant="primary"
              data-array-modal-generate
              @click="
                arrayModalEditArray = getArrayContent(
                  Number(arraySize),
                  Number(arrayMin),
                  Number(arrayMax),
                  arrayDistinct,
                )
              "
            >
              {{ T.arrayModalGenerate }}
            </b-button>
          </b-col>
        </b-row>

        <hr />

        <div class="text-left">
          {{ T.arrayModalGeneratedArray }}
        </div>

        <b-form-input
          v-model="arrayModalEditArray"
          data-array-modal-generated-array
          class="w-100"
        />
      </b-container>
    </b-modal>

    <b-modal
      v-model="matrixModalEdit"
      data-matrix-modal
      :title="T.matrixEditTitle"
      :ok-title="T.matrixModalSave"
      ok-variant="success"
      :cancel-title="T.matrixModalBack"
      cancel-variant="danger"
      static
      lazy
      @ok="
        getSelectedCase.input = matrixModalEditArray;
        matrixModalEditArray = '';
      "
    >
      <b-container>
        <b-row class="mb-4">
          <b-col class="text-left">
            {{ T.matrixModalRows }}
            <b-form-input
              v-model="matrixRows"
              type="number"
              data-matrix-modal-rows
            />
          </b-col>
          <b-col>
            {{ T.matrixModalColumns }}
            <b-form-input
              v-model="matrixCols"
              type="number"
              data-matrix-modal-columns
            />
          </b-col>
          <b-col class="text-left">
            {{ T.matrixModalMinimum }}
            <b-form-input
              v-model="matrixMin"
              type="number"
              data-matrix-modal-min
            />
          </b-col>
          <b-col>
            {{ T.matrixModalMaximum }}
            <b-form-input
              v-model="matrixMax"
              type="number"
              data-matrix-modal-max
            />
          </b-col>
        </b-row>

        <b-row class="mt-2 mb-4">
          <b-col class="text-left">
            <div class="mb-1 font-weight-bold">
              {{ T.matrixModalDistinct }}
            </div>

            <b-dropdown
              :text="getDistinctNameFromType(matrixDistinct)"
              data-matrix-modal-dropdown
              variant="light"
            >
              <b-dropdown-header>
                {{ T.matrixModalDistinctHeader }}
              </b-dropdown-header>

              <b-dropdown-item
                v-for="matrixDistinctOption in matrixDistinctOptions"
                :key="matrixDistinctOption.distinctType"
                :data-matrix-modal-dropdown="matrixDistinctOption.type"
                @click="matrixDistinct = matrixDistinctOption.distinctType"
              >
                {{ matrixDistinctOption.type }}
              </b-dropdown-item>
            </b-dropdown>
          </b-col>
        </b-row>

        <b-row class="mt-4">
          <b-col class="text-left">
            <b-button
              variant="primary"
              data-matrix-modal-generate
              @click="
                matrixModalEditArray = getMatrixContent(
                  Number(matrixRows),
                  Number(matrixCols),
                  Number(matrixMin),
                  Number(matrixMax),
                  matrixDistinct,
                )
              "
            >
              {{ T.matrixModalGenerate }}
            </b-button>
          </b-col>
        </b-row>

        <hr />

        <div class="text-left">
          {{ T.matrixModalGeneratedArray }}
        </div>

        <b-form-textarea
          v-model="matrixModalEditArray"
          data-matrix-modal-generated-matrix
          class="w-100"
        />
      </b-container>
    </b-modal>
  </div>
</template>

<script lang="ts">
import { Component, Vue, Ref, Inject } from 'vue-property-decorator';
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
import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import { BNavItemDropdown, FormInputPlugin, ModalPlugin } from 'bootstrap-vue';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
import CaseSimpleForm from './CasesForm.vue';
import DeleteConfirmationForm from './DeleteConfirmationForm.vue';

library.add(fas);
Vue.use(FormInputPlugin);
Vue.use(ModalPlugin);

const casesStore = namespace('casesStore');
const TRUNC_SUFFIX = '...[TRUNCATED]';

@Component({
  components: {
    'omegaup-problem-creator-case-input': problemCreator_Cases_CaseInput,
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
    CaseSimpleForm: CaseSimpleForm,
    'delete-confirmation-form': DeleteConfirmationForm,
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

  arraySize: number = 10;
  arrayMin: number = 0;
  arrayMax: number = 100;
  arrayDistinct: boolean = false;

  matrixRows: number = 3;
  matrixCols: number = 3;
  matrixMin: number = 0;
  matrixMax: number = 100;
  matrixDistinct: MatrixDistinctType = MatrixDistinctType.None;

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
  @casesStore.Action('deleteLine') deleteLine!: (line: LineID) => void;
  @casesStore.Action('deleteLinesForSelectedCase')
  deleteLinesForSelectedCase!: () => void;

  deleteLines() {
    this.deleteLinesForSelectedCase();
    (this.$refs.dropdown as BNavItemDropdown).hide(true);
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

  @Inject({ default: false }) readonly isEditing!: boolean;

  get inputText(): string {
    return this.getLinesFromSelectedCase
      .map((l) => l.data.value ?? '')
      .join('\n');
  }
  set inputText(v: string) {
    if (this.isInputTruncated) return;
    this.deleteLinesForSelectedCase();
    this.addNewLine();
    const last = this.getLinesFromSelectedCase[
      this.getLinesFromSelectedCase.length - 1
    ];
    this.editLineKind([last.lineID, 'multiline']);
    this.editLineValue([last.lineID, v]);
  }

  clearInput() {
    if (this.isInputTruncated) return;
    this.deleteLinesForSelectedCase();
    this.addNewLine();
    const last = this.getLinesFromSelectedCase[
      this.getLinesFromSelectedCase.length - 1
    ];
    this.editLineKind([last.lineID, 'multiline']);
    this.editLineValue([last.lineID, '']);
  }

  get isInputTruncated(): boolean {
    const text = (this.inputText ?? '').trim();
    return text.endsWith(TRUNC_SUFFIX);
  }

  get isOutputTruncated(): boolean {
    const text = (this.getSelectedCase?.output ?? '').trim();
    return text.endsWith(TRUNC_SUFFIX);
  }

  confirmingDelete: boolean = false;

  startDeleteConfirmation() {
    console.log('Starting delete confirmation', this.isEditing);
    this.confirmingDelete = true;
  }

  cancelDelete() {
    this.confirmingDelete = false;
  }

  get itemNameForDelete(): string {
    const group = this.getSelectedGroup?.name ?? '';
    const name = this.getSelectedCase?.name ?? '';
    return group === name || group === '' ? name : `${group}.${name}`;
  }

  handleDeleteClick() {
    if (this.isEditing) {
      this.startDeleteConfirmation();
    } else {
      this.deleteCase({
        groupID: this.getSelectedGroup.groupID,
        caseID: this.getSelectedCase.caseID,
      });
    }
  }
}
</script>

<style lang="scss" scoped>
.table td {
  vertical-align: middle;
  border: none;
}
</style>
