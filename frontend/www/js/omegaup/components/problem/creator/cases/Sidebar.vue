<template>
  <div>
    <div class="d-flex align-items-center justify-content-between">
      <h5 class="mb-0 d-none d-md-inline">{{ T.problemCreatorGroups }}</h5>
      <div>
        <b-button size="sm" variant="primary" class="mr-2">
          <BIconLayoutSidebar />
        </b-button>
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
        <b-button variant="light" size="sm">
          <BIconThreeDotsVertical />
        </b-button>
      </div>
    </div>
    <div>
      <b-card class="border-0">
        <b-row class="mb-1">
          <b-button
            variant="light"
            data-placement="top"
            :title="T.problemCreatorUngroupedCases"
            class="w-84"
            @click="showUngroupedCases = !showUngroupedCases"
            ><div class="d-flex justify-content-between">
              <div class="mr-2 text-truncate">
                {{ T.problemCreatorUngrouped }}
              </div>
              <div class="d-inline-block text-nowrap">
                <b-badge variant="primary" class="mr-1">{{
                  ungroupedCases.length
                }}</b-badge>
                <b-badge variant="info">
                  {{ Math.round(getTotalPointsForUngroupedCases) }}
                  {{ T.problemCreatorPointsAbbreviation }}</b-badge
                >
              </div>
            </div></b-button
          >
          <b-dropdown variant="light" size="sm" right no-caret>
            <template #button-content>
              <BIconThreeDotsVertical />
            </template>
            <b-dropdown-item disabled
              ><b-row
                ><div class="ml-6">
                  <BIconTrash variant="danger" font-scale=".95" />
                </div>
                <div class="ml-8">
                  {{ T.problemCreatorDeleteGroup }}
                </div></b-row
              >
            </b-dropdown-item>
            <b-dropdown-item @click="deleteUngroupedCases()"
              ><b-row
                ><div class="ml-6">
                  <BIconTrash variant="danger" font-scale=".95" />
                </div>
                <div class="ml-8">
                  {{ T.problemCreatorDeleteCases }}
                </div></b-row
              >
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
                  :title="name"
                  class="w-82"
                  @click="editCase(groupID, cases[0].caseID)"
                  ><div class="d-flex justify-content-between">
                    <div class="mr-2 text-truncate">{{ name }}</div>
                    <div class="d-inline-block text-nowrap">
                      <b-badge variant="info">
                        {{ Math.round(points || 0) }}
                        {{ T.problemCreatorPointsAbbreviation }}</b-badge
                      >
                    </div>
                  </div></b-button
                >
                <b-dropdown variant="light" size="sm" right no-caret>
                  <template #button-content>
                    <BIconThreeDotsVertical />
                  </template>
                  <b-dropdown-item @click="deleteCase({ groupID, caseID: '' })"
                    ><b-row
                      ><div class="ml-6">
                        <BIconTrash variant="danger" font-scale=".95" />
                      </div>
                      <div class="ml-8">
                        {{ T.problemCreatorDeleteCase }}
                      </div></b-row
                    >
                  </b-dropdown-item>
                </b-dropdown>
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
            variant="light"
            data-placement="top"
            :title="name"
            class="w-84"
            @click="showCases[groupID] = !showCases[groupID]"
            ><div class="d-flex justify-content-between">
              <div class="mr-2 text-truncate">{{ name }}</div>
              <div class="d-inline-block text-nowrap">
                <b-badge variant="primary" class="mr-1">{{
                  cases.length
                }}</b-badge>
                <b-badge variant="info"
                  >{{ Math.round(points || 0) }}
                  {{ T.problemCreatorPointsAbbreviation }}</b-badge
                >
              </div>
            </div></b-button
          >
          <b-dropdown variant="light" size="sm" right no-caret>
            <template #button-content>
              <BIconThreeDotsVertical />
            </template>
            <b-dropdown-item @click="deleteGroup(groupID)"
              ><b-row
                ><div class="ml-6">
                  <BIconTrash variant="danger" font-scale=".95" />
                </div>
                <div class="ml-8">
                  {{ T.problemCreatorDeleteGroup }}
                </div></b-row
              >
            </b-dropdown-item>
            <b-dropdown-item @click="deleteGroupCases(groupID)"
              ><b-row
                ><div class="ml-6">
                  <BIconTrash variant="danger" font-scale=".95" />
                </div>
                <div class="ml-8">
                  {{ T.problemCreatorDeleteCases }}
                </div></b-row
              >
            </b-dropdown-item>
          </b-dropdown>
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
                  ><div class="d-flex justify-content-between">
                    <div class="mr-2 text-truncate">{{ caseName }}</div>
                    <div class="d-inline-block text-nowrap">
                      <b-badge variant="info">
                        {{ Math.round(casePoints || 0) }}
                        {{ T.problemCreatorPointsAbbreviation }}</b-badge
                      >
                    </div>
                  </div></b-button
                >
                <b-dropdown variant="light" size="sm" right no-caret>
                  <template #button-content>
                    <BIconThreeDotsVertical />
                  </template>
                  <b-dropdown-item @click="deleteCase({ groupID, caseID })"
                    ><b-row
                      ><div class="ml-6">
                        <BIconTrash variant="danger" font-scale=".95" />
                      </div>
                      <div class="ml-8">
                        {{ T.problemCreatorDeleteCase }}
                      </div></b-row
                    >
                  </b-dropdown-item>
                </b-dropdown>
              </b-row>
            </b-card>
          </b-collapse>
        </b-row>
      </b-card>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue } from 'vue-property-decorator';
import { namespace } from 'vuex-class';
import T from '../../../../lang';
import {
  Group,
  GroupID,
  CaseID,
  CaseGroupID,
} from '@/js/omegaup/problem/creator/types';

const casesStore = namespace('casesStore');

@Component
export default class Sidebar extends Vue {
  T = T;

  @Prop() showWindow!: boolean;

  @casesStore.State('groups') groups!: Group[];
  @casesStore.Getter('getUngroupedCases') ungroupedCases!: Group[];
  @casesStore.Getter('getGroupsButUngroupedCases')
  groupsButUngroupedCases!: Group[];
  @casesStore.Getter('getTotalPointsForUngroupedCases')
  getTotalPointsForUngroupedCases!: number;
  @casesStore.Mutation('deleteGroup') deleteGroup!: (groupID: GroupID) => void;
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

  showUngroupedCases = false;
  showCases: { [key: string]: boolean } = {};

  editCase(groupID: GroupID, caseID: CaseID) {
    this.setSelected({
      groupID: groupID,
      caseID: caseID,
    });
    this.$emit('open-case-edit-window');
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
