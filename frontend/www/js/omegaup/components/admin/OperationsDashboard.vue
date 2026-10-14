<template>
  <omegaup-admin-layout>
    <div class="card">
      <div class="text-white bg-primary card-header">
        <div class="card-title h4">
          {{ T.omegaupTitleAdminOperations }}
        </div>
      </div>
      <div class="card-body">
        <p>Esqueleto funcionando</p>
      </div>
    </div>
  </omegaup-admin-layout>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';
import admin_AdminLayout from './AdminLayout.vue';

@Component({
  components: {
    'omegaup-admin-layout': admin_AdminLayout,
  },
})
export default class OperationsDashboard extends Vue {
  T = T;
}
</script>
