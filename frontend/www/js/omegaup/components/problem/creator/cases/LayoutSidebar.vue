<template>
  <div class="h-77 overflow-auto">
    <div
      v-for="layout in getAllLayouts"
      :key="layout.layoutID"
      class="d-flex justify-content-center"
    >
      <div
        v-if="showRenameModal[layout.layoutID]"
        data-layout-dropdown-rename-modal
      >
        <div class="modal fade show d-block" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">
                  {{ T.problemCreatorRenameModalTitle }}
                </h5>
                <button
                  type="button"
                  class="btn-close"
                  @click="showRenameModal[layout.layoutID] = false"
                ></button>
              </div>
              <div class="modal-body">
                <input
                  v-model="editLayoutModalName[layout.layoutID]"
                  data-layout-sidebar-rename-layout
                  class="form-control"
                />
              </div>
              <footer class="modal-footer">
                <button
                  type="button"
                  class="btn btn-danger"
                  @click="showRenameModal[layout.layoutID] = false"
                >
                  {{ T.problemCreatorRenameModalBack }}
                </button>
                <button
                  type="button"
                  class="btn btn-success"
                  @click="onRenameLayout(layout.layoutID)"
                >
                  {{ T.problemCreatorRenameModalRename }}
                </button>
              </footer>
            </div>
          </div>
        </div>
        <div class="modal-backdrop fade show"></div>
      </div>
      <div class="card w-84 mb-2">
        <div class="card-header p-0">
          <div class="btn-group d-flex" :data-layout-dropdown="layout.layoutID">
            <button
              type="button"
              class="btn btn-primary flex-grow-1 text-start"
              @click="
                showLayout[layout.layoutID] = !showLayout[layout.layoutID]
              "
            >
              {{ layout.name }}
            </button>
            <button
              type="button"
              class="btn btn-primary dropdown-toggle dropdown-toggle-split"
              data-bs-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
            >
              <span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a
                class="dropdown-item"
                href="#"
                data-layout-dropdown-rename-layout
                @click.prevent="
                  showRenameModal[layout.layoutID] = !showRenameModal[
                    layout.layoutID
                  ]
                "
              >
                <div class="d-flex">
                  <font-awesome-icon
                    icon="pencil-alt"
                    class="text-success pt-1 me-3"
                  />
                  {{ T.problemCreatorRenameLayout }}
                </div>
              </a>
              <a
                class="dropdown-item"
                href="#"
                data-layout-dropdown-enforce-to-selected
                @click.prevent="enforceLayoutToTheSelectedCase(layout.layoutID)"
              >
                <div class="d-flex">
                  <font-awesome-icon
                    icon="exchange-alt"
                    class="text-success pt-1 me-3"
                  />
                  {{ T.problemCreatorLayoutLoadToSelected }}
                </div>
              </a>
              <a
                class="dropdown-item"
                href="#"
                data-layout-dropdown-enforce-to-all
                @click.prevent="enforceLayoutToAllCases(layout.layoutID)"
              >
                <div class="d-flex">
                  <font-awesome-icon
                    icon="sync"
                    class="text-success pt-1 me-3"
                  />
                  {{ T.problemCreatorLayoutLoadToAll }}
                </div>
              </a>
              <a
                class="dropdown-item"
                href="#"
                data-layout-dropdown-copy
                @click.prevent="copyLayout(layout.layoutID)"
              >
                <div class="d-flex">
                  <font-awesome-icon
                    icon="download"
                    class="text-success pt-1 me-3"
                  />
                  {{ T.problemCreatorLayoutCopy }}
                </div>
              </a>
              <a
                class="dropdown-item"
                href="#"
                data-layout-dropdown-delete
                @click.prevent="removeLayout(layout.layoutID)"
              >
                <div class="d-flex">
                  <font-awesome-icon
                    icon="trash"
                    class="text-danger pt-1 me-3"
                  />
                  {{ T.problemCreatorLayoutDelete }}
                </div>
              </a>
            </div>
          </div>
        </div>
        <div v-show="showLayout[layout.layoutID]">
          <div>
            <table class="table">
              <tbody>
                <tr
                  v-for="lineInfo in layout.caseLineInfos"
                  :key="lineInfo.lineInfoID"
                >
                  <td class="align-middle border-0">
                    <div class="container-fluid bg-light">
                      <div
                        class="row d-flex justify-content-between align-items-center"
                      >
                        <div class="col-4 mt-2 mb-2 ps-2 pe-1">
                          <input
                            v-model="lineInfo.label"
                            class="form-control form-control-sm"
                            :placeholder="T.problemCreatorLabelPlaceHolder"
                          />
                        </div>
                        <div class="col-6 ps-0 pe-0 text-center">
                          <div
                            class="dropdown d-inline-block"
                            data-line-info-dropdown
                          >
                            <button
                              type="button"
                              class="btn btn-light dropdown-toggle"
                              data-bs-toggle="dropdown"
                              aria-haspopup="true"
                              aria-expanded="false"
                            >
                              {{ getLineNameFromKind(lineInfo.data.kind) }}
                            </button>
                            <div class="dropdown-menu">
                              <a
                                v-for="lineKindOption in lineKindOptions"
                                :key="lineKindOption.kind"
                                class="dropdown-item"
                                href="#"
                                :data-line-info-dropdown-item="
                                  lineKindOption.kind
                                "
                                @click.prevent="
                                  editLineInfoKind([
                                    layout.layoutID,
                                    lineInfo.lineInfoID,
                                    lineKindOption.kind,
                                  ])
                                "
                              >
                                {{ lineKindOption.kind }}
                              </a>
                            </div>
                          </div>
                          <button
                            v-if="
                              getEditIconDisplay(lineInfo) ===
                              EditIconDisplayOption.EDIT_ICON
                            "
                            class="btn btn-light btn-sm"
                            type="button"
                            :title="T.problemCreatorLineEdit"
                          >
                            <font-awesome-icon
                              icon="pen-square"
                              class="text-info"
                            />
                          </button>
                        </div>
                        <div class="col-2">
                          <button
                            class="btn btn-light btn-sm"
                            type="button"
                            :title="T.problemCreatorLineDelete"
                            @click="
                              removeLineInfoFromLayout([
                                layout.layoutID,
                                lineInfo.lineInfoID,
                              ])
                            "
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
              </tbody>
            </table>
          </div>
          <div class="text-center mb-2">
            <button
              type="button"
              data-layout-add-line-info
              class="btn btn-light me-2"
              @click="addNewLineInfoToLayout(layout.layoutID)"
            >
              <div class="container">
                <div class="row">
                  <font-awesome-icon
                    icon="plus-square"
                    class="text-info me-2 pt-1"
                  />
                  {{ T.problemCreatorLayoutAddLineInfo }}
                </div>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue, Watch } from 'vue-property-decorator';
import { namespace } from 'vuex-class';
import T from '../../../../lang';
import {
  Layout,
  LayoutID,
  CaseLineKind,
  LineInfoID,
  Group,
  CaseLineInfo,
} from '@/js/omegaup/problem/creator/types';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';

library.add(fas);

const casesStore = namespace('casesStore');

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
  },
})
export default class Sidebar extends Vue {
  T = T;

  @casesStore.State('groups') groups!: Group[];
  @casesStore.Getter('getAllLayouts') getAllLayouts!: Layout[];
  @casesStore.Mutation('enforceLayoutToTheSelectedCase')
  enforceLayoutToTheSelectedCase!: (layoutID: LayoutID) => void;
  @casesStore.Mutation('addNewLineInfoToLayout')
  addNewLineInfoToLayout!: (layoutID: LayoutID) => void;
  @casesStore.Mutation('editLineInfoKind') editLineInfoKind!: ([
    layoutID,
    lineInfoID,
    kind,
  ]: [LayoutID, LineInfoID, CaseLineKind]) => void;
  @casesStore.Mutation('enforceLayoutToAllCases')
  enforceLayoutToAllCases!: (layoutID: LayoutID) => void;
  @casesStore.Mutation('copyLayout')
  copyLayout!: (layoutID: LayoutID) => void;
  @casesStore.Mutation('removeLayout')
  removeLayout!: (layoutID: LayoutID) => void;
  @casesStore.Mutation('removeLineInfoFromLayout')
  removeLineInfoFromLayout!: ([layoutID, lineInfoID]: [
    LayoutID,
    LineInfoID,
  ]) => void;
  @casesStore.Mutation('editLayoutName')
  editLayoutName!: ([layoutID, newValue]: [LayoutID, string]) => void;

  showLayout: { [key: LayoutID]: boolean } = {};
  showRenameModal: { [key: LayoutID]: boolean } = {};
  editLayoutModalName: { [key: LayoutID]: string } = {};

  created() {
    this.onGroupsChanged();
  }

  @Watch('getAllLayouts')
  onGroupsChanged() {
    this.showLayout = this.getAllLayouts.reduce((acc, layout) => {
      acc[layout.layoutID] = false;
      return acc;
    }, {} as { [key: string]: boolean });
    this.showRenameModal = this.getAllLayouts.reduce((acc, layout) => {
      acc[layout.layoutID] = false;
      return acc;
    }, {} as { [key: string]: boolean });
    this.editLayoutModalName = this.getAllLayouts.reduce((acc, layout) => {
      acc[layout.layoutID] = layout.name;
      return acc;
    }, {} as { [key: string]: string });
  }

  EditIconDisplayOption = Object.freeze({
    EDIT_ICON: 'edit_icon',
  });

  get getEditIconDisplay() {
    return (lineInfo: CaseLineInfo) => {
      if (lineInfo.data.kind === 'array' || lineInfo.data.kind === 'matrix') {
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

  getLineNameFromKind(kind: CaseLineKind) {
    return this.lineKindOptions.find((line) => line.kind === kind)?.type;
  }

  onRenameLayout(layoutID: LayoutID) {
    this.editLayoutName([layoutID, this.editLayoutModalName[layoutID]]);
    this.showRenameModal[layoutID] = false;
  }
}
</script>

<style lang="scss" scoped>
.h-77 {
  height: 77%;
}

.w-84 {
  width: 84%;
}
</style>
