<template>
  <div class="card">
    <h5
      class="card-header d-flex justify-content-between align-items-center rank-title"
    >
      Acceso parental
    </h5>
    <table class="table mb-0">
      <thead>
        <tr>
          <th scope="col" class="text-center">#</th>
          <th scope="col" class="text-center">Usuario</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(dependent, index) in dependents" :key="index">
          <th scope="row" class="text-center">{{ index + 1 }}</th>
          <td class="text-center">{{ dependent.name }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
//   import * as ui from '../../ui';

@Component
export default class UserDependents extends Vue {
  @Prop() dependents!: types.UserDependentsPayload[];
  T = T;
  // ui = ui;
}
</script>
