<template>
  <div>
    <b-row class="bottom-divider my-2 py-2">
      <b-col
        class="group d-flex justify-content-between align-items-center py-2"
        @click="toggleOpen"
      >
        <div>
          <BIconChevronDown v-if="isOpen" />
          <BIconChevronUp v-if="!isOpen" />
          <span class="ml-2">{{ name }}</span>
        </div>
        <b-badge
          v-if="groupId !== NIL"
          data-testid="group-points"
          size="sm"
          :variant="defined ? 'success' : 'primary'"
          >{{ points.toFixed(2) }} PTS
        </b-badge>
      </b-col>
      <b-col cols="2" class="p-0 my-auto">
        <b-dropdown size="sm" variant="outline" no-caret>
          <template #button-content>
            <!-- <BIconThreeDotsVertical font-scale="1" /> -->
          </template>
          <div v-if="groupId !== NIL">
            <b-dropdown-item
              class="menu-item d-flex align-items-center"
              @click="openEditModal"
            >
              <BIconPencilSquare font-scale="1" />
              <span class="ml-2">{{ T.problemCreatorEditGroup }}</span>
              <EditGroup
                ref="editGroupRef"
                :name="name"
                :points="points"
                :defined="defined"
                :group-id="groupId"
              />
            </b-dropdown-item>
            <b-dropdown-item class="menu-item" @click="openDeleteModal">
              <BIconTrash2 />
              <span class="ml-2">{{ T.problemCreatorDeleteGroup }}</span>
              <b-modal
                ref="deleteGroupRef"
                :title="`${T.problemCreatorDeleteGroup} | ${name}`"
                ok-variant="danger"
                :ok-title="T.problemCreatorDelete"
                :cancel-title="T.problemCreatorCancel"
                @ok="handleDeleteGroup"
              >
                {{ T.problemCreatorDeleteGroupConfirmation }}
                {{ T.problemCreatorCantUndo }}
              </b-modal>
            </b-dropdown-item>
          </div>
          <b-dropdown-item class="menu-item" @click="openDeleteGroupCasesModal">
            <BIconFileArrowDown />
            <span class="ml-2">{{ T.problemCreatorDeleteAllCases }}</span>
            <b-modal
              ref="deleteGroupCasesRef"
              :title="`${T.problemCreatorDeleteAllCases} | ${name}`"
              ok-variant="danger"
              :ok-title="T.problemCreatorDelete"
              :cancel-title="T.problemCreatorCancel"
              @ok="handleDeleteGroupCases"
            >
              {{ T.problemCreatorDeleteGroupCasesConfirmation }}
              {{ T.problemCreatorAllGroupCasesWillBeDeleted }}.
              {{ T.problemCreatorCantUndo }}
            </b-modal>
          </b-dropdown-item>
        </b-dropdown>
      </b-col>
    </b-row>
    <div v-if="isOpen" class="">
      <div
        v-for="_case in getCasesFromGroup(groupId)"
        :key="_case.caseId"
        :class="_case.groupId === NIL ? 'd-block' : 'd-inline-block'"
      >
        <Case
          :case-id="_case.caseId"
          :group-id="groupId"
          :name="_case.name"
          :points="_case.points"
          :defined="_case.defined"
          :no-group="_case.groupId === NIL"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Ref, Vue } from 'vue-property-decorator';
import { types } from '../../../../problem/creator/types';
import { namespace } from 'vuex-class';
import { NIL } from 'uuid';
import EditGroup from './EditGroup.vue';
import Case from './Case.vue';
import T from '../../../../lang';

const caseStore = namespace('casesStore');

@Component({
  components: { EditGroup, Case },
})
export default class Group extends Vue {
  @caseStore.Mutation('deleteGroup') deleteGroup!: (payload: string) => void;
  @caseStore.Mutation('deleteGroupCases') deleteGroupCases!: (
    payload: string,
  ) => void;

  @Prop() readonly name!: string;
  @Prop() readonly points!: number;
  @Prop() readonly defined!: boolean;
  @Prop() readonly groupId!: string;

  @Ref('editGroupRef') editGroupRef!: EditGroup;
  @Ref('deleteGroupRef') deleteGroupRef!: any;
  @Ref('deleteGroupCasesRef') deleteGroupCasesRef!: any;

  @caseStore.Getter('getCasesFromGroup') getCasesFromGroup!: (
    groupId: string,
  ) => types.Case[];

  isOpen = true;
  NIL = NIL;

  T = T;

  handleDeleteGroup() {
    this.deleteGroup(this.groupId);
  }
  handleDeleteGroupCases() {
    this.deleteGroupCases(this.groupId);
  }
  toggleOpen() {
    this.isOpen = !this.isOpen;
  }

  openEditModal() {
    this.editGroupRef.modal.show();
    // this.$bvModal.show(`edit-group-${this.groupId}`);
  }

  openDeleteModal() {
    this.deleteGroupRef.show();
    // this.$bvModal.show(`delete-modal-${this.groupId}`);
  }

  openDeleteGroupCasesModal() {
    this.deleteGroupCasesRef.show();
    // this.$bvModal.show(`delete-group-cases-modal-${this.groupId}`);
  }
}
</script>

<style lang="scss" scoped>
.bottom-divider {
  margin: 0 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.2);
}

.not-defined {
  opacity: 50%;
}

.group {
  cursor: pointer;
  padding-right: 0;
  &:hover {
    border-left: 2px solid rgba(0, 0, 0, 0.1);
  }
}

.menu-item {
  font-size: 14px;
  //margin: 5px 0;
}
</style>
