<template>
  <div>
    <div class="d-flex align-items-center justify-content-between">
      <h5 class="mb-0 d-none d-md-inline">{{ T.problemCreatorGroups }}</h5>
      <div>
        <b-button size="sm" variant="primary" class="mr-2">
          <BIconLayoutSidebar />
        </b-button>
        <b-button size="sm" variant="success" class="mr-2" @click="openModal">
          <span class="d-none d-xl-inline">{{ T.problemCreatorAdd }}</span>
          <BIconPlusCircle class="d-inline d-xl-none" />
        </b-button>
        <b-button variant="light" size="sm">
          <BIconThreeDotsVertical />
        </b-button>
      </div>
      <!-- Modals -->
      <problem-creator-add-modal ref="add-case-modal" />
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Ref, Vue } from 'vue-property-decorator';
import problemCreatorAddModal from './AddModal.vue';
import T from '../../../../lang';

@Component({
  components: { 'problem-creator-add-modal': problemCreatorAddModal },
})
export default class Sidebar extends Vue {
  T = T;
  @Ref('add-case-modal') addCaseModalRef!: problemCreatorAddModal;

  openModal() {
    this.addCaseModalRef.openModal();
  }
}
</script>
