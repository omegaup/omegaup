<template>
  <div>
    <div class="d-flex align-items-center justify-content-between">
      <h5 class="mb-0 d-none d-md-inline">{{ T.problemCreatorGroups }}</h5>
      <div>
        <b-button
          data-toggle-layout-sidebar
          size="sm"
          variant="primary"
          class="mr-2"
          @click="showLayoutSidebar = !showLayoutSidebar"
        >
          <BIconLayoutSidebar />
        </b-button>
        <b-sidebar
          v-model="showLayoutSidebar"
          right
          :title="T.problemCreatorLayoutWordLayouts"
          shadow
          no-header-close
          width="385px"
        >
          <omegaup-problem-creator-layout-sidebar />
          <div class="fixed-bottom">
            <b-container>
              <b-row class="justify-content-center">
                <b-button
                  data-add-layout-from-selected-case
                  class="w-84 mb-2"
                  variant="success"
                  @click="addLayoutFromSelectedCase"
                >
                  {{ T.problemCreatorLayoutAddFromCase }}
                </b-button>
              </b-row>
              <b-row class="justify-content-center">
                <b-button
                  class="w-84 mb-2"
                  variant="success"
                  @click="addNewLayout"
                >
                  {{ T.problemCreatorLayoutAddNew }}
                </b-button>
              </b-row>
              <b-row class="justify-content-center">
                <b-button
                  data-close-layout-sidebar
                  class="w-84 mb-3"
                  variant="danger"
                  @click="showLayoutSidebar = false"
                >
                  {{ T.problemCreatorLayoutBarClose }}
                </b-button>
              </b-row>
            </b-container>
          </div>
        </b-sidebar>
        <b-button
          data-add-window
          size="sm"
          variant="success"
          class="mr-2"
          :pressed="showWindow"
          @click="$emit('open-add-window')"
        >
          <span class="d-none d-xl-inline">{{ T.problemCreatorAdd }}</span>
          <BIconPlusCircle class="d-inline d-xl-none" />
        </b-button>
        <b-dropdown variant="light" size="sm" right no-caret>
          <template #button-content>
            <BIconThreeDotsVertical />
          </template>
          <b-dropdown-item
            data-sidebar-validate-points-dropdown-item
            @click="validateAndFixPointsModal = !validateAndFixPointsModal"
            ><b-row>
              <div class="ml-6">
                <BIconBroadcast variant="info" font-scale="1.05" />
              </div>
              <div class="ml-8">{{ T.problemCreatorValidatePointsButton }}</div>
            </b-row>
          </b-dropdown-item>
        </b-dropdown>
      </div>
    </div>
    <b-modal
      v-model="validateAndFixPointsModal"
      data-sidebar-validate-points-modal
      :title="T.problemCreatorValidatePoints"
      :ok-title="T.problemCreatorValidatePointsContinue"
      ok-variant="success"
      :cancel-title="T.problemCreatorValidatePointsBack"
      cancel-variant="danger"
      static
      lazy
      @ok="validateAndFixPoints"
    >
      {{ T.problemCreatorValidatePointsWarning }}
    </b-modal>
    <div>
      <b-card class="border-0">
        <b-row class="mb-1">
          <b-button
            data-sidebar-groups="ungrouped"
            variant="light"
            data-placement="top"
            :title="T.problemCreatorUngroupedCases"
            class="w-84"
            @click="showUngroupedCases = !showUngroupedCases"
          >
            <div class="d-flex justify-content-between">
              <div class="mr-2 text-truncate">
                {{ T.problemCreatorUngrouped }}
              </div>
              <div class="d-inline-block text-nowrap">
                <b-badge
                  data-sidebar-ungrouped-cases="count"
                  variant="primary"
                  class="mr-1"
                  >{{ ungroupedCases.length }}</b-badge
                >
                <b-badge data-sidebar-ungrouped-cases="points" variant="info">
                  {{ Math.round(getTotalPointsForUngroupedCases) }}
                  {{ T.problemCreatorPointsAbbreviation }}</b-badge
                >
              </div>
            </div>
          </b-button>
          <b-dropdown variant="light" size="sm" right no-caret>
            <template #button-content>
              <BIconThreeDotsVertical />
            </template>
            <b-dropdown-item disabled
              ><b-row>
                <div class="ml-6">
                  <BIconTrash variant="danger" font-scale=".95" />
                </div>
                <div class="ml-8">
                  {{ T.problemCreatorDeleteGroup }}
                </div>
              </b-row>
            </b-dropdown-item>
            <b-dropdown-item v-if="!isEditing" @click="deleteUngroupedCases()"
              ><b-row>
                <div class="ml-6">
                  <BIconTrash variant="danger" font-scale=".95" />
                </div>
                <div class="ml-8">
                  {{ T.problemCreatorDeleteCases }}
                </div>
              </b-row>
            </b-dropdown-item>
          </b-dropdown>
          <b-collapse v-model="showUngroupedCases" class="w-100">
            <b-card class="border-0 w-100">
              <b-row
                v-for="{ name, points, cases, groupID } in ungroupedCases"
                :key="groupID"
                class="mb-1"
              >
                <b-button
                  variant="light"
                  data-placement="top"
                  :data-sidebar-cases-ungrouped="groupID"
                  :title="name"
                  class="w-82"
                  @click="editCase(groupID, cases[0].caseID)"
                >
                  <div class="d-flex justify-content-between">
                    <div class="mr-2 text-truncate">{{ name }}</div>
                    <div class="d-inline-block text-nowrap">
                      <b-badge variant="info">
                        {{ Math.round(points || 0) }}
                        {{ T.problemCreatorPointsAbbreviation }}</b-badge
                      >
                    </div>
                  </div>
                </b-button>
                <b-dropdown variant="light" size="sm" right no-caret>
                  <template #button-content>
                    <BIconThreeDotsVertical />
                  </template>
                  <b-dropdown-item @click="handleDeleteClick('case', groupID, cases[0].caseID)"
                    ><b-row>
                      <div class="ml-6">
                        <BIconTrash variant="danger" font-scale=".95" />
                      </div>
                      <div class="ml-8">
                        {{ T.problemCreatorDeleteCase }}
                      </div>
                    </b-row>
                  </b-dropdown-item>
                </b-dropdown>
                <delete-confirmation-form
                  v-if="isEditing"
                  :visible="isConfirmingDelete('case', groupID, cases[0].caseID)"
                  :item-name="getItemNameForDelete()"
                  :on-cancel="cancelDelete"
                />
              </b-row>
            </b-card>
          </b-collapse>
        </b-row>
        <b-row
          v-for="{ name, groupID, cases, points } in groupsButUngroupedCases"
          :key="groupID"
          class="mb-1"
        >
          <b-button
            data-sidebar-groups="grouped"
            variant="light"
            data-placement="top"
            :title="name"
            class="w-84"
            @click="showCases[groupID] = !showCases[groupID]"
          >
            <div class="d-flex justify-content-between">
              <div class="mr-2 text-truncate">{{ name }}</div>
              <div class="d-inline-block text-nowrap">
                <b-badge
                  data-sidebar-groups="count"
                  variant="primary"
                  class="mr-1"
                  >{{ cases.length }}</b-badge
                >
                <b-badge data-sidebar-groups="points" variant="info"
                  >{{ Math.round(points || 0) }}
                  {{ T.problemCreatorPointsAbbreviation }}</b-badge
                >
              </div>
            </div>
          </b-button>
          <b-dropdown
            data-sidebar-edit-group-dropdown
            variant="light"
            size="sm"
            right
            no-caret
          >
            <template #button-content>
              <BIconThreeDotsVertical />
            </template>
            <b-dropdown-item
              data-sidebar-edit-group-dropdown="edit group"
              @click="editGroupModal[groupID] = !editGroupModal[groupID]"
              ><b-row>
                <div class="ml-6">
                  <BIconPencil variant="info" font-scale=".95" />
                </div>
                <div class="ml-8">{{ T.omegaupTitleGroupsEdit }}</div>
              </b-row>
            </b-dropdown-item>
            <b-dropdown-item
              data-sidebar-edit-group-dropdown="delete group"
              @click="handleDeleteClick('group', groupID)"
              ><b-row>
                <div class="ml-6">
                  <BIconTrash variant="danger" font-scale=".95" />
                </div>
                <div class="ml-8">
                  {{ T.problemCreatorDeleteGroup }}
                </div>
              </b-row>
            </b-dropdown-item>
            <b-dropdown-item
              v-if="!isEditing"
              data-sidebar-edit-group-dropdown="delete cases"
              @click="deleteGroupCases(groupID)"
              ><b-row>
                <div class="ml-6">
                  <BIconTrash variant="danger" font-scale=".95" />
                </div>
                <div class="ml-8">
                  {{ T.problemCreatorDeleteCases }}
                </div>
              </b-row>
            </b-dropdown-item>
            <b-dropdown-item
              data-sidebar-edit-group-dropdown="download .in"
              @click="downloadGroupInput(groupID, '.in')"
              ><b-row>
                <div class="ml-6">
                  <BIconBoxArrowDown variant="info" font-scale=".95" />
                </div>
                <div class="ml-8">
                  {{ T.problemCraetorGroupDownloadIn }}
                </div>
              </b-row>
            </b-dropdown-item>
            <b-dropdown-item
              data-sidebar-edit-group-dropdown="download .txt"
              @click="downloadGroupInput(groupID, '.txt')"
              ><b-row>
                <div class="ml-6">
                  <BIconTextLeft variant="info" font-scale=".95" />
                </div>
                <div class="ml-8">
                  {{ T.problemCraetorGroupDownloadTxt }}
                </div>
              </b-row>
            </b-dropdown-item>
          </b-dropdown>
          <delete-confirmation-form
            v-if="isEditing"
            :visible="isConfirmingDelete('group', groupID)"
            :item-name="getItemNameForDelete()"
            :on-cancel="cancelDelete"
          />
          <b-collapse v-model="showCases[groupID]" class="w-100">
            <b-card class="border-0 w-100">
              <b-row
                v-for="{ name: caseName, points: casePoints, caseID } in cases"
                :key="caseID"
                class="mb-1"
              >
                <b-button
                  variant="light"
                  data-placement="top"
                  :title="caseName"
                  class="w-82"
                  @click="editCase(groupID, caseID)"
                >
                  <div class="d-flex justify-content-between">
                    <div class="mr-2 text-truncate">{{ caseName }}</div>
                    <div class="d-inline-block text-nowrap">
                      <b-badge variant="info">
                        {{ Math.round(casePoints || 0) }}
                        {{ T.problemCreatorPointsAbbreviation }}</b-badge
                      >
                    </div>
                  </div>
                </b-button>
                <b-dropdown variant="light" size="sm" right no-caret>
                  <template #button-content>
                    <BIconThreeDotsVertical />
                  </template>
                  <b-dropdown-item @click="handleDeleteClick('case', groupID, caseID)"
                    ><b-row>
                      <div class="ml-6">
                        <BIconTrash variant="danger" font-scale=".95" />
                      </div>
                      <div class="ml-8">
                        {{ T.problemCreatorDeleteCase }}
                      </div>
                    </b-row>
                  </b-dropdown-item>
                </b-dropdown>
                <delete-confirmation-form
                  v-if="isEditing"
                  :visible="isConfirmingDelete('case', groupID, caseID)"
                  :item-name="getItemNameForDelete()"
                  :on-cancel="cancelDelete"
                />
              </b-row>
            </b-card>
          </b-collapse>
          <b-modal
            v-model="editGroupModal[groupID]"
            data-sidebar-edit-group-modal
            :title="T.groupEditTitle"
            :ok-title="T.groupModalSave"
            ok-variant="success"
            :cancel-title="T.groupModalBack"
            cancel-variant="danger"
            static
            lazy
            @ok="updateGroupInfo(groupID)"
          >
            <div class="mt-3">
              <b-form-group
                :description="T.problemCreatorCaseGroupNameHelper"
                :label="T.problemCreatorGroupName"
                class="mb-4"
              >
                <b-form-input
                  v-model="editGroupName[groupID]"
                  data-sidebar-edit-group-modal="edit name"
                  :formatter="formatter"
                  required
                  autocomplete="off"
                />
              </b-form-group>
              <b-form-group
                v-show="!editGroupAutoPoints[groupID]"
                :label="T.problemCreatorPoints"
              >
                <b-form-input
                  v-model="editGroupPoints[groupID]"
                  data-sidebar-edit-group-modal="edit points"
                  :formatter="pointsFormatter"
                  type="number"
                  number
                  min="0"
                />
              </b-form-group>
              <b-form-group
                :label="T.problemCreatorAutomaticPoints"
                :description="T.problemCreatorAutomaticPointsHelperGroup"
              >
                <b-form-checkbox
                  data-sidebar-edit-group-modal="edit autoPoints"
                  :checked="editGroupAutoPoints[groupID]"
                  @change="toggleGroupAutoPoints(groupID)"
                >
                </b-form-checkbox>
              </b-form-group>
            </div>
          </b-modal>
        </b-row>
      </b-card>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue, Watch, Inject } from 'vue-property-decorator';
import problemCreator_LayoutSidebar from './LayoutSidebar.vue';
import DeleteConfirmationForm from './DeleteConfirmationForm.vue';
import { namespace } from 'vuex-class';
import T from '../../../../lang';
import {
  Group,
  GroupID,
  CaseID,
  CaseGroupID,
} from '@/js/omegaup/problem/creator/types';
import JSZip from 'jszip';

const casesStore = namespace('casesStore');

@Component({
  components: {
    'omegaup-problem-creator-layout-sidebar': problemCreator_LayoutSidebar,
    'delete-confirmation-form': DeleteConfirmationForm,
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

  updateGroupInfo(groupID: GroupID) {
    this.updateGroup([
      groupID,
      this.editGroupName[groupID],
      this.editGroupPoints[groupID],
    ]);
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

  @Inject({ default: false }) readonly isEditing!: boolean;
  

  confirmingDelete: {
    type: 'group' | 'case' | null;
    groupID: GroupID | null;
    caseID: CaseID | null;
  } = {
    type: null,
    groupID: null,
    caseID: null,
  };

  startDeleteConfirmation(type: 'group' | 'case', groupID: GroupID, caseID: CaseID = '') {
    this.confirmingDelete = {
      type,
      groupID,
      caseID,
    };
  }

  getItemNameForDelete(): string {
    if (!this.confirmingDelete.type) return '';
    
    if (this.confirmingDelete.type === 'group') {
      const group = this.groups.find(g => g.groupID === this.confirmingDelete.groupID);
      return `grupo: ${group?.name || this.confirmingDelete.groupID}`;
    } else {
      const group = this.groups.find(g => g.groupID === this.confirmingDelete.groupID);
      const caseItem = group?.cases.find(c => c.caseID === this.confirmingDelete.caseID);
      return `caso: ${group?.name || ''}.${caseItem?.name || this.confirmingDelete.caseID}`;
    }
  }

  cancelDelete() {
    this.confirmingDelete = {
      type: null,
      groupID: null,
      caseID: null,
    };
  }

  isConfirmingDelete(type: 'group' | 'case', groupID: GroupID, caseID: CaseID = ''): boolean {
    return (
      this.confirmingDelete.type === type &&
      this.confirmingDelete.groupID === groupID &&
      (type === 'group' || this.confirmingDelete.caseID === caseID)
    );
  }

  handleDeleteClick(type: 'group' | 'case', groupID: GroupID, caseID: CaseID = '') {
    if (this.isEditing) {
      this.startDeleteConfirmation(type, groupID, caseID);
    } else {
      if (type === 'group') {
        this.deleteGroup(groupID);
      } else {
        this.deleteCase({ groupID, caseID });
      }
    }
  }


}
</script>

<style lang="scss" scoped>
.ml-8 {
  margin-left: 8%;
}

.ml-6 {
  margin-left: 6%;
}

.w-84 {
  width: 84%;
}

.w-82 {
  width: 82%;
}
</style>
