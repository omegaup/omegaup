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
                        v-for="lineoption in lineOptions"
                        :key="lineoption.kind"
                        @click="line.data.kind = lineoption.kind"
                      >
                        {{ lineoption.type }}
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
}
</script>

<style lang="scss" scoped>
.table td {
  vertical-align: middle;
  border: none;
}
</style>
