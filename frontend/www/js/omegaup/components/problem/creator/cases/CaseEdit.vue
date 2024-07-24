<template>
  <div>
    <div
      v-if="getSelectedCase && getSelectedGroup"
      class="d-flex justify-content-between"
    >
      <div>
        <h3 class="mb-0 d-md-inline mr-2">{{ getSelectedCase.name }}</h3>
        <h5 class="mb-0 d-none d-md-inline text-muted">
          {{ getSelectedGroup.name }}
        </h5>
      </div>
      <div>
        <b-button variant="light" class="mr-2">
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
        <b-button variant="light" class="mr-2">
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
        <b-button class="h-100" variant="light">
          <BIconThreeDotsVertical />
        </b-button>
      </div>
    </div>
    <hr class="border-top my-2" />
    <div>
      <table class="table">
        <tbody>
          <tr v-for="line in getLinesFromSelectedCase">
            <td>
              <b-container fluid class="bg-light">
                <b-row class="d-flex justify-content-between" align-v="center">
                  <b-col cols="1">
                    <b-button
                      class="btn btn-link"
                      type="button"
                      :title="T.problemCreatorLinesReorder"
                      variant="light"
                    >
                      <font-awesome-icon icon="sort" />
                    </b-button>
                  </b-col>
                  <b-col cols="2" class="pl-0 pr-2">
                    <b-form-input
                      v-model="line.label"
                      size="sm"
                      :placeholder="T.problemCreatorLabelPlaceHolder"
                    />
                  </b-col>
                  <b-col cols="5" class="pr-0 text-center">
                    <b-form-input
                      v-if="getLineDisplay(line) === LineDisplayOption.LINE"
                      v-model="line.data.value"
                      size="sm"
                      class="mt-3 mb-3"
                      :placeholder="T.problemCreatorContentPlaceHolder"
                    />
                    <b-form-textarea
                      v-if="
                        getLineDisplay(line) === LineDisplayOption.MULTILINE
                      "
                      v-model="line.data.value"
                      class="mt-3 mb-3 text-nowrap overflow-auto w-100"
                      rows="2"
                      max-rows="3"
                      :placeholder="T.problemCreatorContentPlaceHolder"
                    ></b-form-textarea>
                  </b-col>
                  <b-col cols="3" class="pl-2 pr-0 text-center">
                    <b-dropdown
                      :text="getLineNameFromKind(line.data.kind)"
                      variant="light"
                    >
                      <b-dropdown-item
                        v-for="lineKindoption in lineKindOptions"
                        :key="lineKindoption.kind"
                        @click="
                          editLineKind([line.lineID, lineKindoption.kind])
                        "
                      >
                        {{ lineKindoption.type }}
                      </b-dropdown-item>
                    </b-dropdown>
                    <b-button
                      v-if="
                        getEditIconDisplay(line) ===
                        EditIconDisplayOption.EDIT_ICON
                      "
                      size="sm"
                      type="button"
                      :title="T.problemCreatorLineEdit"
                      variant="light"
                      @click="editModalState(line.data.kind)"
                    >
                      <BIconPencilSquare variant="info" font-scale="1.20" />
                    </b-button>
                    <b-modal
                      v-if="line.data.kind === 'array'"
                      v-model="arrayModalEdit"
                      :title="T.arrayEditTitle"
                      :ok-title="T.arrayModalSave"
                      ok-variant="success"
                      :cancel-title="T.arrayModalBack"
                      cancel-variant="danger"
                      static
                      lazy
                      @ok="
                        editLineValue([line.lineID, arrayModalEditArray]);
                        arrayModalEditArray = '';
                      "
                    >
                      <b-container>
                        <b-row class="mb-4">
                          <b-col class="text-left">
                            {{ T.arrayModalSize }}
                            <b-form-input
                              v-model="line.data.size"
                              type="number"
                            />
                          </b-col>
                          <b-col class="text-left">
                            {{ T.arrayModalMinimum }}
                            <b-form-input
                              v-model="line.data.min"
                              type="number"
                            />
                          </b-col>
                          <b-col class="text-left">
                            {{ T.arrayModalMaximum }}
                            <b-form-input
                              v-model="line.data.max"
                              type="number"
                            />
                          </b-col>
                        </b-row>
                        <b-row class="mt-2 mb-4">
                          <b-col class="text-left">
                            <b-form-checkbox v-model="line.data.distinct">
                              {{ T.arrayModalDistinctValues }}
                            </b-form-checkbox>
                          </b-col>
                        </b-row>
                        <b-row class="mt-4">
                          <b-col class="text-left">
                            <b-button
                              variant="primary"
                              @click="
                                arrayModalEditArray = getArrayContent(
                                  Number(line.data.size),
                                  Number(line.data.min),
                                  Number(line.data.max),
                                  line.data.distinct,
                                )
                              "
                              >{{ T.arrayModalGenerate }}</b-button
                            >
                          </b-col>
                        </b-row>
                        <hr />
                        <div class="text-left">
                          {{ T.arrayModalGeneratedArray }}
                        </div>
                        <b-form-input
                          v-model="arrayModalEditArray"
                          class="w-100"
                        />
                      </b-container>
                    </b-modal>
                    <b-modal
                      v-if="line.data.kind === 'matrix'"
                      v-model="matrixModalEdit"
                      :title="T.matrixEditTitle"
                      :ok-title="T.matrixModalSave"
                      ok-variant="success"
                      :cancel-title="T.matrixModalBack"
                      cancel-variant="danger"
                      static
                      lazy
                      @ok="
                        editLineValue([line.lineID, matrixModalEditArray]);
                        matrixModalEditArray = '';
                      "
                    >
                      <b-container>
                        <b-row class="mb-4">
                          <b-col class="text-left">
                            {{ T.matrixModalRows }}
                            <b-form-input v-model="line.data.rows" />
                          </b-col>
                          <b-col>
                            {{ T.matrixModalColumns }}
                            <b-form-input v-model="line.data.cols" />
                          </b-col>
                          <b-col class="text-left">
                            {{ T.matrixModalMinimum }}
                            <b-form-input v-model="line.data.min" />
                          </b-col>
                          <b-col>
                            {{ T.matrixModalMaximum }}
                            <b-form-input v-model="line.data.max" />
                          </b-col>
                        </b-row>
                        <b-row class="mt-2 mb-4">
                          <b-col class="text-left">
                            <div class="mb-1 font-weight-bold">
                              {{ T.matrixModalDistinct }}
                            </div>
                            <b-dropdown
                              :text="
                                getDistinctNameFromType(line.data.distinct)
                              "
                              variant="light"
                            >
                              <b-dropdown-header>
                                {{ T.matrixModalDistinctHeader }}
                              </b-dropdown-header>
                              <b-dropdown-item
                                v-for="matrixDistinctOption in matrixDistinctOptions"
                                :key="matrixDistinctOption.distinctType"
                                @click="
                                  line.data.distinct =
                                    matrixDistinctOption.distinctType
                                "
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
                              @click="
                                matrixModalEditArray = getMatrixContent(
                                  Number(line.data.rows),
                                  Number(line.data.cols),
                                  Number(line.data.min),
                                  Number(line.data.max),
                                  line.data.distinct,
                                )
                              "
                              >{{ T.matrixModalGenerate }}</b-button
                            >
                          </b-col>
                        </b-row>
                        <hr />
                        <div class="text-left">
                          {{ T.matrixModalGeneratedArray }}
                        </div>
                        <b-form-textarea
                          v-model="matrixModalEditArray"
                          class="w-100"
                        />
                      </b-container>
                    </b-modal>
                  </b-col>
                  <b-col cols="1">
                    <b-button
                      size="sm"
                      type="button"
                      :title="T.problemCreatorLineDelete"
                      variant="light"
                      @click="deleteLine(line.lineID)"
                    >
                      <BIconTrashFill variant="danger" font-scale="1.20" />
                    </b-button>
                  </b-col>
                </b-row>
              </b-container>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="text-center">
      <b-button variant="light" class="mr-2" @click="addNewLine">
        <div class="container">
          <div class="row">
            <BIconPlusSquare
              variant="info"
              font-scale="1.25"
              class="mr-2 pt-1"
            />
            {{ T.problemCreatorAddLine }}
          </div>
        </div>
      </b-button>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import T from '../../../../lang';
import { namespace } from 'vuex-class';
import {
  Case,
  Group,
  CaseLineKind,
  CaseLine,
  LineID,
  MatrixDistinctType,
} from '@/js/omegaup/problem/creator/types';
import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import { FormInputPlugin, ModalPlugin } from 'bootstrap-vue';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);
Vue.use(FormInputPlugin);
Vue.use(ModalPlugin);

const casesStore = namespace('casesStore');

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class CaseEdit extends Vue {
  T = T;

  arrayModalEdit: boolean = false;
  matrixModalEdit: boolean = false;

  arrayModalEditArray: string = '';
  matrixModalEditArray: string = '';

  @casesStore.State('groups') groups!: Group[];
  @casesStore.Getter('getSelectedCase') getSelectedCase!: Case;
  @casesStore.Getter('getLinesFromSelectedCase')
  getLinesFromSelectedCase!: CaseLine[];
  @casesStore.Getter('getSelectedGroup') getSelectedGroup!: Group;

  @casesStore.Mutation('editLineKind') editLineKind!: ([lineID, kind]: [
    LineID,
    CaseLineKind,
  ]) => void;
  @casesStore.Mutation('editLineValue') editLineValue!: ([lineID, value]: [
    LineID,
    string,
  ]) => void;

  @casesStore.Action('addNewLine') addNewLine!: () => void;
  @casesStore.Action('deleteLine') deleteLine!: (line: LineID) => void;

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
    { type: T.matrixModalDistinctNone, distinctType: 'none' },
    { type: T.matrixModalDistinctRow, distinctType: 'rows' },
    { type: T.matrixModalDistinctColumn, distinctType: 'cols' },
    { type: T.matrixModalDistinctAll, distinctType: 'both' },
  ];

  editModalState(kind: CaseLineKind) {
    if (kind === 'array') {
      this.matrixModalEdit = false;
      this.arrayModalEdit = true;
    } else if (kind === 'matrix') {
      this.arrayModalEdit = false;
      this.matrixModalEdit = true;
    }
  }

  getLineNameFromKind(kind: CaseLineKind) {
    return this.lineKindOptions.find((row) => row.kind === kind)?.type;
  }

  getDistinctNameFromType(distinctype: MatrixDistinctType) {
    return this.matrixDistinctOptions.find(
      (row) => row.distinctType === distinctype,
    )?.type;
  }

  getArrayContent(
    size: number,
    low: number = 0,
    high: number = 0,
    distinct: boolean = false,
  ) {
    if (distinct && high - low + 1 < size) {
      return '';
    }
    let generatedArray: number[] | Set<number>;
    if (distinct) {
      generatedArray = new Set<number>();
      while (generatedArray.size < size) {
        generatedArray.add(
          Number(low.toString()) + Math.floor(Math.random() * (high - low + 1)),
        );
      }
    } else {
      generatedArray = [];
      while (generatedArray.length < size) {
        generatedArray.push(
          Number(low.toString()) + Math.floor(Math.random() * (high - low + 1)),
        );
      }
    }
    return [...generatedArray].join(' ');
  }

  getMatrixContent(
    rows: number,
    columns: number,
    low: number = 0,
    high: number = 100,
    distinct: MatrixDistinctType = 'none',
  ) {
    if (distinct === 'both' && high - low + 1 < rows * columns) {
      return '';
    }
    if (distinct === 'rows' && high - low + 1 < columns) {
      return '';
    }
    if (distinct === 'cols' && high - low + 1 < rows) {
      return '';
    }
    let matrix = [];
    if (distinct === 'none') {
      const generatedArray: number[] = [];
      while (generatedArray.length < rows * columns) {
        generatedArray.push(
          Number(low.toString()) + Math.floor(Math.random() * (high - low + 1)),
        );
      }

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
    if (distinct === 'both') {
      const generatedArray: Set<number> = new Set<number>();
      while (generatedArray.size < rows * columns) {
        generatedArray.add(
          Number(low.toString()) + Math.floor(Math.random() * (high - low + 1)),
        );
      }

      const generatedArrayList: number[] = Array.from(generatedArray);
      let index = 0;

      for (let i = 0; i < rows; i++) {
        let row = [];
        for (let j = 0; j < columns; j++) {
          row.push(generatedArrayList[index]);
          index++;
        }
        matrix.push(row);
      }
      return matrix.map((row) => row.join(' ')).join('\n');
    }
    if (distinct === 'rows') {
      const generatedRows: Set<number>[] = Array.from(
        { length: rows },
        () => new Set<number>(),
      );
      for (let i = 0; i < rows; i++) {
        while (generatedRows[i].size < columns) {
          generatedRows[i].add(
            Number(low.toString()) +
              Math.floor(Math.random() * (high - low + 1)),
          );
        }
      }
      return generatedRows.map((row) => [...row].join(' ')).join('\n');
    }
    if (distinct === 'cols') {
      const generatedColumns: Set<number>[] = Array.from(
        { length: columns },
        () => new Set<number>(),
      );
      for (let i = 0; i < columns; i++) {
        while (generatedColumns[i].size < rows) {
          generatedColumns[i].add(
            Number(low.toString()) +
              Math.floor(Math.random() * (high - low + 1)),
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
    return '';
  }
}
</script>

<style lang="scss" scoped>
.table td {
  vertical-align: middle;
  border: none;
}
</style>
