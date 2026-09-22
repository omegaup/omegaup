<template>
  <div>
    <div class="d-flex align-items-center justify-content-between">
      <h5 class="mb-0 d-none d-md-inline">{{ T.problemCreatorGroups }}</h5>
      <div class="d-flex flex-nowrap align-items-center">
        <button
          type="button"
          data-toggle-layout-sidebar
          class="btn btn-primary btn-sm me-2"
          @click="showLayoutSidebar = !showLayoutSidebar"
        >
          <font-awesome-icon icon="columns" />
        </button>
        <div
          v-show="showLayoutSidebar"
          class="border bg-white h-100 overflow-auto layout-sidebar-panel"
        >
          <div class="p-3 border-bottom">
            <h5 class="mb-0">{{ T.problemCreatorLayoutWordLayouts }}</h5>
          </div>
          <omegaup-problem-creator-layout-sidebar />
          <div class="p-3">
            <div class="container">
              <div class="row justify-content-center">
                <button
                  type="button"
                  data-add-layout-from-selected-case
                  class="btn btn-success w-84 mb-2"
                  @click="addLayoutFromSelectedCase"
                >
                  {{ T.problemCreatorLayoutAddFromCase }}
                </button>
              </div>
              <div class="row justify-content-center">
                <button
                  type="button"
                  class="btn btn-success w-84 mb-2"
                  @click="addNewLayout"
                >
                  {{ T.problemCreatorLayoutAddNew }}
                </button>
              </div>
              <div class="row justify-content-center">
                <button
                  type="button"
                  data-close-layout-sidebar
                  class="btn btn-danger w-84 mb-3"
                  @click="showLayoutSidebar = false"
                >
                  {{ T.problemCreatorLayoutBarClose }}
                </button>
              </div>
            </div>
          </div>
        </div>
        <button
          type="button"
          data-add-window
          class="btn btn-success btn-sm me-2"
          :class="{ active: showWindow }"
          @click="$emit('open-add-window')"
        >
          <span class="d-none d-xl-inline">{{ T.problemCreatorAdd }}</span>
          <font-awesome-icon icon="plus-circle" class="d-inline d-xl-none" />
        </button>
        <div class="dropdown">
          <button
            type="button"
            class="btn btn-light btn-sm"
            data-bs-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
          >
            <font-awesome-icon icon="ellipsis-v" />
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            <a
              class="dropdown-item"
              href="#"
              data-sidebar-validate-points-dropdown-item
              @click.prevent="
                validateAndFixPointsModal = !validateAndFixPointsModal
              "
            >
              <div class="row">
                <div class="ms-6">
                  <font-awesome-icon icon="broadcast-tower" class="text-info" />
                </div>
                <div class="ms-8">
                  {{ T.problemCreatorValidatePointsButton }}
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
    <div v-if="validateAndFixPointsModal" data-sidebar-validate-points-modal>
      <div class="modal fade show d-block" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ T.problemCreatorValidatePoints }}</h5>
              <button
                type="button"
                class="btn-close"
                @click="validateAndFixPointsModal = false"
              >
                
              </button>
            </div>
            <div class="modal-body">
              {{ T.problemCreatorValidatePointsWarning }}
            </div>
            <footer class="modal-footer">
              <button
                type="button"
                class="btn btn-danger"
                @click="validateAndFixPointsModal = false"
              >
                {{ T.problemCreatorValidatePointsBack }}
              </button>
              <button
                type="button"
                class="btn btn-success"
                @click="onValidateAndFixPoints"
              >
                {{ T.problemCreatorValidatePointsContinue }}
              </button>
            </footer>
          </div>
        </div>
      </div>
      <div class="modal-backdrop fade show"></div>
    </div>
    <div>
      <div class="card border-0">
        <div class="card-body">
          <div class="row mb-1">
            <div class="d-flex flex-nowrap align-items-center w-100">
              <button
                type="button"
                data-sidebar-groups="ungrouped"
                data-bs-placement="top"
                :title="T.problemCreatorUngroupedCases"
                class="btn btn-light w-84"
                @click="showUngroupedCases = !showUngroupedCases"
              >
                <div class="d-flex justify-content-between">
                  <div class="me-2 text-truncate">
                    {{ T.problemCreatorUngrouped }}
                  </div>
                  <div class="d-inline-block text-nowrap">
                    <span
                      data-sidebar-ungrouped-cases="count"
                      class="badge text-bg-primary me-1"
                      >{{ ungroupedCases.length }}</span
                    >
                    <span
                      data-sidebar-ungrouped-cases="points"
                      class="badge text-bg-info"
                    >
                      {{ Math.round(getTotalPointsForUngroupedCases) }}
                      {{ T.problemCreatorPointsAbbreviation }}</span
                    >
                  </div>
                </div>
              </button>
              <div class="dropdown">
                <button
                  type="button"
                  class="btn btn-light btn-sm"
                  data-bs-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                >
                  <font-awesome-icon icon="ellipsis-v" />
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item disabled" href="#">
                    <div class="row">
                      <div class="ms-6">
                        <font-awesome-icon icon="trash" class="text-danger" />
                      </div>
                      <div class="ms-8">
                        {{ T.problemCreatorDeleteGroup }}
                      </div>
                    </div>
                  </a>
                  <a
                    class="dropdown-item"
                    href="#"
                    @click.prevent="deleteUngroupedCases()"
                  >
                    <div class="row">
                      <div class="ms-6">
                        <font-awesome-icon icon="trash" class="text-danger" />
                      </div>
                      <div class="ms-8">
                        {{ T.problemCreatorDeleteCases }}
                      </div>
                    </div>
                  </a>
                </div>
              </div>
            </div>
            <div v-show="showUngroupedCases" class="w-100">
              <div class="card border-0 w-100">
                <div class="card-body">
                  <div
                    v-for="{ name, points, cases, groupID } in ungroupedCases"
                    :key="groupID"
                    class="row mb-1"
                  >
                    <button
                      type="button"
                      class="btn btn-light w-82"
                      data-bs-placement="top"
                      :data-sidebar-cases-ungrouped="groupID"
                      :title="name"
                      @click="editCase(groupID, cases[0].caseID)"
                    >
                      <div class="d-flex justify-content-between">
                        <div class="me-2 text-truncate">{{ name }}</div>
                        <div class="d-inline-block text-nowrap">
                          <span class="badge text-bg-info">
                            {{ Math.round(points || 0) }}
                            {{ T.problemCreatorPointsAbbreviation }}</span
                          >
                        </div>
                      </div>
                    </button>
                    <div class="dropdown">
                      <button
                        type="button"
                        class="btn btn-light btn-sm"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                      >
                        <font-awesome-icon icon="ellipsis-v" />
                      </button>
                      <div class="dropdown-menu dropdown-menu-end">
                        <a
                          class="dropdown-item"
                          href="#"
                          @click.prevent="deleteCase({ groupID, caseID: '' })"
                        >
                          <div class="row">
                            <div class="ms-6">
                              <font-awesome-icon
                                icon="trash"
                                class="text-danger"
                              />
                            </div>
                            <div class="ms-8">
                              {{ T.problemCreatorDeleteCase }}
                            </div>
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div
            v-for="{ name, groupID, cases, points } in groupsButUngroupedCases"
            :key="groupID"
            class="row mb-1"
          >
            <button
              type="button"
              data-sidebar-groups="grouped"
              class="btn btn-light w-84"
              data-bs-placement="top"
              :title="name"
              @click="showCases[groupID] = !showCases[groupID]"
            >
              <div class="d-flex justify-content-between">
                <div class="me-2 text-truncate">{{ name }}</div>
                <div class="d-inline-block text-nowrap">
                  <span
                    data-sidebar-groups="count"
                    class="badge text-bg-primary me-1"
                    >{{ cases.length }}</span
                  >
                  <span data-sidebar-groups="points" class="badge text-bg-info"
                    >{{ Math.round(points || 0) }}
                    {{ T.problemCreatorPointsAbbreviation }}</span
                  >
                </div>
              </div>
            </button>
            <div class="dropdown" data-sidebar-edit-group-dropdown>
              <button
                type="button"
                class="btn btn-light btn-sm"
                data-bs-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
              >
                <font-awesome-icon icon="ellipsis-v" />
              </button>
              <div class="dropdown-menu dropdown-menu-end">
                <a
                  class="dropdown-item"
                  href="#"
                  data-sidebar-edit-group-dropdown="edit group"
                  @click.prevent="
                    editGroupModal[groupID] = !editGroupModal[groupID]
                  "
                >
                  <div class="row">
                    <div class="ms-6">
                      <font-awesome-icon icon="pencil-alt" class="text-info" />
                    </div>
                    <div class="ms-8">{{ T.omegaupTitleGroupsEdit }}</div>
                  </div>
                </a>
                <a
                  class="dropdown-item"
                  href="#"
                  data-sidebar-edit-group-dropdown="delete group"
                  @click.prevent="deleteGroup(groupID)"
                >
                  <div class="row">
                    <div class="ms-6">
                      <font-awesome-icon icon="trash" class="text-danger" />
                    </div>
                    <div class="ms-8">
                      {{ T.problemCreatorDeleteGroup }}
                    </div>
                  </div>
                </a>
                <a
                  class="dropdown-item"
                  href="#"
                  data-sidebar-edit-group-dropdown="delete cases"
                  @click.prevent="deleteGroupCases(groupID)"
                >
                  <div class="row">
                    <div class="ms-6">
                      <font-awesome-icon icon="trash" class="text-danger" />
                    </div>
                    <div class="ms-8">
                      {{ T.problemCreatorDeleteCases }}
                    </div>
                  </div>
                </a>
                <a
                  class="dropdown-item"
                  href="#"
                  data-sidebar-edit-group-dropdown="download .in"
                  @click.prevent="downloadGroupInput(groupID, '.in')"
                >
                  <div class="row">
                    <div class="ms-6">
                      <font-awesome-icon icon="download" class="text-info" />
                    </div>
                    <div class="ms-8">
                      {{ T.problemCraetorGroupDownloadIn }}
                    </div>
                  </div>
                </a>
                <a
                  class="dropdown-item"
                  href="#"
                  data-sidebar-edit-group-dropdown="download .txt"
                  @click.prevent="downloadGroupInput(groupID, '.txt')"
                >
                  <div class="row">
                    <div class="ms-6">
                      <font-awesome-icon icon="align-left" class="text-info" />
                    </div>
                    <div class="ms-8">
                      {{ T.problemCraetorGroupDownloadTxt }}
                    </div>
                  </div>
                </a>
              </div>
            </div>
            <div v-show="showCases[groupID]" class="w-100">
              <div class="card border-0 w-100">
                <div class="card-body">
                  <div
                    v-for="{
                      name: caseName,
                      points: casePoints,
                      caseID,
                    } in cases"
                    :key="caseID"
                    class="row mb-1"
                  >
                    <button
                      type="button"
                      class="btn btn-light w-82"
                      data-bs-placement="top"
                      :title="caseName"
                      @click="editCase(groupID, caseID)"
                    >
                      <div class="d-flex justify-content-between">
                        <div class="me-2 text-truncate">{{ caseName }}</div>
                        <div class="d-inline-block text-nowrap">
                          <span class="badge text-bg-info">
                            {{ Math.round(casePoints || 0) }}
                            {{ T.problemCreatorPointsAbbreviation }}</span
                          >
                        </div>
                      </div>
                    </button>
                    <div class="dropdown">
                      <button
                        type="button"
                        class="btn btn-light btn-sm"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                      >
                        <font-awesome-icon icon="ellipsis-v" />
                      </button>
                      <div class="dropdown-menu dropdown-menu-end">
                        <a
                          class="dropdown-item"
                          href="#"
                          @click.prevent="deleteCase({ groupID, caseID })"
                        >
                          <div class="row">
                            <div class="ms-6">
                              <font-awesome-icon
                                icon="trash"
                                class="text-danger"
                              />
                            </div>
                            <div class="ms-8">
                              {{ T.problemCreatorDeleteCase }}
                            </div>
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div
              v-if="editGroupModal[groupID]"
              data-sidebar-edit-group-modal
            >
              <div class="modal fade show d-block" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">{{ T.groupEditTitle }}</h5>
                      <button
                        type="button"
                        class="btn-close"
                        @click="editGroupModal[groupID] = false"
                      >
                        
                      </button>
                    </div>
                    <div class="modal-body">
                      <div class="mt-3">
                        <div class="mb-3 mb-4">
                          <label>{{ T.problemCreatorGroupName }}</label>
                          <input
                            :value="editGroupName[groupID]"
                            data-sidebar-edit-group-modal="edit name"
                            class="form-control"
                            required
                            autocomplete="off"
                            @input="onEditGroupNameInput(groupID, $event)"
                          />
                          <small class="form-text text-muted">{{
                            T.problemCreatorCaseGroupNameHelper
                          }}</small>
                        </div>
                        <div
                          v-show="!editGroupAutoPoints[groupID]"
                          class="mb-3"
                        >
                          <label>{{ T.problemCreatorPoints }}</label>
                          <input
                            :value="editGroupPoints[groupID]"
                            data-sidebar-edit-group-modal="edit points"
                            class="form-control"
                            type="number"
                            min="0"
                            @input="onEditGroupPointsInput(groupID, $event)"
                          />
                        </div>
                        <div class="mb-3">
                          <label>{{ T.problemCreatorAutomaticPoints }}</label>
                          <small class="form-text text-muted d-block">{{
                            T.problemCreatorAutomaticPointsHelperGroup
                          }}</small>
                          <input
                            type="checkbox"
                            data-sidebar-edit-group-modal="edit autoPoints"
                            :checked="editGroupAutoPoints[groupID]"
                            @change="toggleGroupAutoPoints(groupID)"
                          />
                        </div>
                      </div>
                    </div>
                    <footer class="modal-footer">
                      <button
                        type="button"
                        class="btn btn-danger"
                        @click="editGroupModal[groupID] = false"
                      >
                        {{ T.groupModalBack }}
                      </button>
                      <button
                        type="button"
                        class="btn btn-success"
                        @click="onUpdateGroupInfo(groupID)"
                      >
                        {{ T.groupModalSave }}
                      </button>
                    </footer>
                  </div>
                </div>
              </div>
              <div class="modal-backdrop fade show"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue, Watch } from 'vue-property-decorator';
import problemCreator_LayoutSidebar from './LayoutSidebar.vue';
import { namespace } from 'vuex-class';
import T from '../../../../lang';
import {
  Group,
  GroupID,
  CaseID,
  CaseGroupID,
} from '@/js/omegaup/problem/creator/types';
import JSZip from 'jszip';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';

library.add(fas);

const casesStore = namespace('casesStore');

@Component({
  components: {
    'omegaup-problem-creator-layout-sidebar': problemCreator_LayoutSidebar,
    'font-awesome-icon': FontAwesomeIcon,
  },
})
export default class Sidebar extends Vue {
  T = T;
  showLayoutSidebar = false;

  @Prop() showWindow!: boolean;

  @casesStore.State('groups') groups!: Group[];
  @casesStore.Getter('getUngroupedCases') ungroupedCases!: Group[];
  @casesStore.Getter('getGroupsButUngroupedCases')
  groupsButUngroupedCases!: Group[];
  @casesStore.Getter('getTotalPointsForUngroupedCases')
  getTotalPointsForUngroupedCases!: number;
  @casesStore.Mutation('deleteGroup') deleteGroup!: (groupID: GroupID) => void;
  @casesStore.Mutation('addLayoutFromSelectedCase')
  addLayoutFromSelectedCase!: () => void;
  @casesStore.Mutation('addNewLayout')
  addNewLayout!: () => void;
  @casesStore.Mutation('validateAndFixPoints')
  validateAndFixPoints!: () => void;
  @casesStore.Mutation('deleteCase') deleteCase!: ({
    groupID,
    caseID,
  }: CaseGroupID) => void;
  @casesStore.Mutation('deleteGroupCases') deleteGroupCases!: (
    groupID: GroupID,
  ) => void;
  @casesStore.Mutation('deleteUngroupedCases')
  deleteUngroupedCases!: () => void;
  @casesStore.Mutation('setSelected') setSelected!: (
    CaseGroupsIDToBeSelected: CaseGroupID,
  ) => void;
  @casesStore.Mutation('updateGroup') updateGroup!: ([
    groupID,
    newName,
    newPoints,
  ]: [GroupID, string, number]) => void;
  @casesStore.Getter('getStringifiedLinesFromCaseGroupID')
  getStringifiedLinesFromCaseGroupID!: (caseGroupID: CaseGroupID) => string;

  validateAndFixPointsModal: boolean = false;
  showUngroupedCases = false;
  showCases: { [key: string]: boolean } = {};
  editGroupModal: { [key: GroupID]: boolean } = {};
  editGroupName: { [key: GroupID]: string } = {};
  editGroupPoints: { [key: GroupID]: number } = {};
  editGroupAutoPoints: { [key: GroupID]: boolean } = {};

  @Watch('groups')
  onGroupsChanged() {
    this.editGroupModal = this.groups.reduce((acc, group) => {
      acc[group.groupID] = false;
      return acc;
    }, {} as { [key: string]: boolean });
    this.editGroupName = this.groups.reduce((acc, group) => {
      acc[group.groupID] = group.name;
      return acc;
    }, {} as { [key: string]: string });
    this.editGroupPoints = this.groups.reduce((acc, group) => {
      acc[group.groupID] = group.points;
      return acc;
    }, {} as { [key: string]: number });
    this.editGroupAutoPoints = this.groups.reduce((acc, group) => {
      acc[group.groupID] = group.autoPoints;
      return acc;
    }, {} as { [key: string]: boolean });
  }

  toggleGroupAutoPoints(groupID: GroupID) {
    this.editGroupAutoPoints[groupID] = !this.editGroupAutoPoints[groupID];
    if (this.editGroupAutoPoints[groupID]) {
      this.editGroupPoints[groupID] = 100;
    }
  }

  formatter(text: string) {
    return text.toLowerCase().replace(/[^a-zA-Z0-9_-]/g, '');
  }

  pointsFormatter(points: number) {
    return Math.max(points, 0);
  }

  onEditGroupNameInput(groupID: GroupID, event: Event) {
    this.editGroupName[groupID] = this.formatter(
      (event.target as HTMLInputElement).value,
    );
  }

  onEditGroupPointsInput(groupID: GroupID, event: Event) {
    this.editGroupPoints[groupID] = this.pointsFormatter(
      Number((event.target as HTMLInputElement).value),
    );
  }

  updateGroupInfo(groupID: GroupID) {
    this.updateGroup([
      groupID,
      this.editGroupName[groupID],
      this.editGroupPoints[groupID],
    ]);
  }

  onValidateAndFixPoints() {
    this.validateAndFixPoints();
    this.validateAndFixPointsModal = false;
  }

  onUpdateGroupInfo(groupID: GroupID) {
    this.updateGroupInfo(groupID);
    this.editGroupModal[groupID] = false;
  }

  editCase(groupID: GroupID, caseID: CaseID) {
    this.setSelected({
      groupID: groupID,
      caseID: caseID,
    });
    this.$emit('open-case-edit-window');
  }

  downloadGroupInput(groupID: GroupID, ext: '.in' | '.txt') {
    const groupZip: JSZip = new JSZip();
    const targetGroup = this.groups.find(
      (_group: Group) => _group.groupID === groupID,
    );
    if (!targetGroup) return;

    targetGroup.cases.forEach((_case) => {
      let fileName = _case.name;
      const caseGroupID: CaseGroupID = {
        groupID: targetGroup.groupID,
        caseID: _case.caseID,
      };
      const input = this.getStringifiedLinesFromCaseGroupID(caseGroupID);
      groupZip?.file(`${fileName}${ext}`, input);
    });

    this.$emit('download-zip-file', {
      fileName: targetGroup.name,
      zipContent: groupZip,
    });
  }
}
</script>

<style lang="scss" scoped>
.ms-8 {
  margin-left: 8%;
}

.ms-6 {
  margin-left: 6%;
}

.w-84 {
  width: 84%;
}

.w-82 {
  width: 82%;
}

.layout-sidebar-panel {
  position: fixed;
  top: 0;
  right: 0;
  width: 385px;
  z-index: 1040;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
</style>
