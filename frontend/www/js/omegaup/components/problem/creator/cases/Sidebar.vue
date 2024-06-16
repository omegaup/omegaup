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
      <b-card style="border: none">
        <b-row class="mb-2">
          <b-button
            variant="light"
            data-placement="top"
            title="Ungrouped cases"
            style="width: 84%"
            @click="visible[0] = !visible[0]"
            ><div style="float: left">ungrouped&nbsp;</div>
            <div style="float: right">
              <b-badge variant="primary">{{ ungroupedCases.length }}</b-badge
              >&nbsp;
              <b-badge variant="info">
                {{ Math.round(getTotalPointsForUngroupedCases) }} pts</b-badge
              >
            </div></b-button
          >
          <b-button variant="light" size="sm">
            <BIconThreeDotsVertical />
          </b-button>
          <b-collapse v-model="visible[0]" style="width: 100%">
            <b-card style="border: none; width: 100%">
              <b-row
                v-for="{ name, points, groupID } in ungroupedCases"
                :key="groupID"
                class="mb-2"
              >
                <b-button
                  variant="light"
                  data-placement="top"
                  :title="name"
                  style="width: 70%"
                  ><div style="float: left">{{ truncate(name) }}&nbsp;</div>
                  <div style="float: right">
                    <b-badge variant="info">
                      {{ Math.round(points || 0) }}</b-badge
                    >
                  </div></b-button
                >
                <b-button variant="light" size="sm">
                  <BIconThreeDotsVertical />
                </b-button>
              </b-row>
            </b-card>
          </b-collapse>
        </b-row>
        <b-row
          v-for="{ name, groupID, cases, points } in groupsButUngroupedCases"
          :key="groupID"
          class="mb-2"
        >
          <b-button
            variant="light"
            data-placement="top"
            :title="name"
            style="width: 84%"
            @click="visible[groupID] = !visible[groupID]"
            ><div style="float: left">{{ truncate(name) }} &nbsp;</div>
            <div style="float: right">
              <b-badge variant="primary">{{ cases.length }}</b-badge
              >&nbsp;
              <b-badge variant="info"
                >{{ Math.round(points || 0) }} pts</b-badge
              >
            </div></b-button
          >
          <b-button variant="light" size="sm">
            <BIconThreeDotsVertical />
          </b-button>
          <b-collapse v-model="visible[groupID]" style="width: 100%">
            <b-card style="border: none; width: 100%">
              <b-row
                v-for="{ name: caseName, points: casePoints, caseID } in cases"
                :key="caseID"
                class="mb-2"
              >
                <b-button
                  variant="light"
                  data-placement="top"
                  :title="caseName"
                  style="width: 70%"
                  ><div style="float: left">{{ truncate(caseName) }}&nbsp;</div>
                  <div style="float: right">
                    <b-badge variant="info">
                      {{ Math.round(casePoints || 0) }}</b-badge
                    >
                  </div></b-button
                >
                <b-button variant="light" size="sm">
                  <BIconThreeDotsVertical />
                </b-button>
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
import { Group } from '@/js/omegaup/problem/creator/types';

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

  visible: any = {};

  truncate(str: string) {
    const truncatedStr = str.slice(0, 7);
    if (truncatedStr === str) return str;
    return truncatedStr + '...';
  }
}
</script>
