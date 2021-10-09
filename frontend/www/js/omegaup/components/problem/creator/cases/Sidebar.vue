<template>
  <div>
    <div class="d-flex align-items-center justify-content-between">
      <h5 class="mb-0 d-none d-md-inline">Grupos</h5>
      <div>
        <b-button
          v-b-toggle.layout-drawer
          size="sm"
          variant="primary"
          class="mr-2"
        >
          <BIconLayoutSidebar />
        </b-button>
        <b-button size="sm" variant="success" class="mr-2" @click="openModal">
          <span class="d-none d-xl-inline">Agregar</span>
          <BIconPlusCircle class="d-inline d-xl-none" />
        </b-button>
        <b-button variant="light" class="mt-2" size="sm">
          <BIconThreeDotsVertical />
        </b-button>
      </div>
      <!-- Modal-->
      <AddModal />
      <!-- Drawer -->
      <!-- <LayoutDrawer /> -->
    </div>
    <Groups />
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import AddModal from './AddModal.vue';
import Groups from './Groups.vue';

@Component({
  components: { AddModal, Groups },
})
export default class Sidebar extends Vue {
  visible = false;

  openModal() {
    this.$bvModal.show('add-problem');
  }
}
</script>
