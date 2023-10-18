<template>
  <div class="card">
    <h5
      class="card-header d-flex justify-content-between align-items-center rank-title"
    >
      klwlgledglk
    </h5>
    <table class="table mb-0">
      <thead>
        <tr>
          <th scope="col" class="text-center">#</th>
          <th scope="col" class="text-center">{{ T.contentCreator }}</th>
          <th scope="col" class="text-center">{{ T.rankScore }}</th>
          <th scope="col" class="text-center"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(author, index) in rankingData.ranking" :key="index">
          <th scope="row" class="text-center">
            {{ author.author_ranking || index }}
          </th>
          <td class="text-center"></td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';
//   import * as ui from '../../ui';

@Component
export default class UserDependents extends Vue {
  T = T;
  // ui = ui;
}
</script>
