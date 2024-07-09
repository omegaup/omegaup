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
        <b-dropdown ref="dropdown" variant="light" class="h-100" right no-caret>
          <template #button-content>
            <BIconThreeDotsVertical />
          </template>
          <b-button variant="light" class="w-100" @click="deleteLines()">
            <div class="d-flex">
              <BIconTrash variant="danger" class="pt-1 mr-3" font-scale="1.2" />
              {{ T.problemCreatorLinesDelete }}
            </div>
          </b-button>
          <b-dropdown-divider></b-dropdown-divider>
          <b-button
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
    <hr class="border-top my-2" />
    <div>
      <table class="table">
        <tbody v-sortable="{ onUpdate: updateLinesOrder }">
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
                      v-if="
                        line.data.kind === 'line' || line.data.kind === 'array'
                      "
                      v-model="line.data.value"
                      size="sm"
                      class="mt-3 mb-3"
                      :placeholder="T.problemCreatorContentPlaceHolder"
                    />
                    <b-form-textarea
                      v-if="
                        line.data.kind === 'multiline' ||
                        line.data.kind === 'matrix'
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
                        v-for="lineoption in lineOptions"
                        :key="lineoption.kind"
                        @click="line.data.kind = lineoption.kind"
                      >
                        {{ lineoption.type }}
                      </b-dropdown-item>
                    </b-dropdown>
                    <b-button
                      v-if="
                        line.data.kind === 'array' ||
                        line.data.kind === 'matrix'
                      "
                      size="sm"
                      type="button"
                      :title="T.problemCreatorLineEdit"
                      variant="light"
                    >
                      <BIconPencilSquare variant="info" font-scale="1.20" />
                    </b-button>
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
  CaseGroupID,
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

  @casesStore.State('groups') groups!: Group[];
  @casesStore.Getter('getSelectedCase') getSelectedCase!: Case;
  @casesStore.Getter('getLinesFromSelectedCase')
  getLinesFromSelectedCase!: CaseLine[];
  @casesStore.Getter('getSelectedGroup') getSelectedGroup!: Group;
  @casesStore.Getter('getStringifiedLinesFromCaseGroupID')
  getStringifiedLinesFromCaseGroupID!: (caseGroupID: CaseGroupID) => string;

  @casesStore.Action('addNewLine') addNewLine!: () => void;
  @casesStore.Action('deleteLine') deleteLine!: (line: LineID) => void;
  @casesStore.Action('sortLines') sortLines!: (
    exchangePair: [number, number],
  ) => void;
  @casesStore.Action('deleteLinesForSelectedCase')
  deleteLinesForSelectedCase!: () => void;

  deleteLines() {
    this.deleteLinesForSelectedCase();
    (this.$refs.dropdown as any).hide(true);
  }

  updateLinesOrder(event: any) {
    this.sortLines([event.oldIndex, event.newIndex]);
  }

  lineOptions: {
    type: string;
    kind: CaseLineKind;
  }[] = [
    { type: T.problemCreatorLineLine, kind: 'line' },
    { type: T.problemCreatorLineMultiline, kind: 'multiline' },
    { type: T.problemCreatorLineArray, kind: 'array' },
    { type: T.problemCreatorLineMatrix, kind: 'matrix' },
  ];

  getLineNameFromKind(kind: CaseLineKind) {
    return this.lineOptions.find((line) => line.kind === kind)?.type;
  }

  downloadInputFile(ext: '.txt' | '.in') {
    const caseGroupID: CaseGroupID = {
      groupID: this.getSelectedGroup.groupID,
      caseID: this.getSelectedCase.caseID,
    };
    const input = this.getStringifiedLinesFromCaseGroupID(caseGroupID);
    const blob = new Blob([input], { type: 'text/plain' });

    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = this.getSelectedCase.name + ext;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
}
</script>

<style lang="scss" scoped>
.table td {
  vertical-align: middle;
  border: none;
}
</style>
