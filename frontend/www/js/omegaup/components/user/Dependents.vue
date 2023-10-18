<template>
    <div class="card">
        <h1>HOlas</h1>
    </div>
    <!-- <div class="card">
      <h5
        class="card-header d-flex justify-content-between align-items-center rank-title"
      >
        {{
          ui.formatString(T.authorRankRangeHeader, {
            lowCount: (page - 1) * length + 1,
            highCount: page * length,
          })
        }}
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
            <td class="text-center">
              <omegaup-countryflag
                :country="author.country_id"
              ></omegaup-countryflag>
              <omegaup-user-username
                :classname="author.classname"
                :linkify="true"
                :username="author.username"
              ></omegaup-user-username>
              <span v-if="author.name">
                <br />
                {{ author.name }}
              </span>
            </td>
            <td class="text-center">
              {{ author.author_score.toFixed(2) }}
            </td>
          </tr>
        </tbody>
      </table>
      <div class="card-footer">
        <omegaup-common-paginator
          :pager-items="pagerItems"
        ></omegaup-common-paginator>
      </div>
    </div> -->
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
  
  