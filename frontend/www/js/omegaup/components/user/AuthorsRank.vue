<template>
  <omegaup-authors-rank-skeleton v-if="loading"></omegaup-authors-rank-skeleton>
  <div v-else class="card" data-authors-rank>
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
    <div class="card-body form-row">
      <omegaup-common-typeahead
        class="col col-md-4 pl-0 pr-2"
        :existing-options="searchResultUsers"
        :value.sync="searchedUsername"
        :max-results="10"
        @update-existing-options="
          (query) => $emit('update-search-result-users', query)
        "
      ></omegaup-common-typeahead>
      <button
        class="btn btn-primary form-control col-4 col-md-2"
        type="button"
        @click="onSubmit"
      >
        {{ T.searchUser }}
      </button>
    </div>
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
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import CountryFlag from '../CountryFlag.vue';
import common_Paginator from '../common/Paginator.vue';
import common_Typeahead from '../common/Typeahead.vue';
import user_Username from '../user/Username.vue';
import AuthorsRankSkeleton from './AuthorsRankSkeleton.vue';

@Component({
  components: {
    'omegaup-user-username': user_Username,
    'omegaup-countryflag': CountryFlag,
    'omegaup-common-paginator': common_Paginator,
    'omegaup-common-typeahead': common_Typeahead,
    'omegaup-authors-rank-skeleton': AuthorsRankSkeleton,
  },
})
export default class AuthorsRank extends Vue {
  @Prop() page!: number;
  @Prop() length!: number;
  @Prop() rankingData!: types.AuthorsRank;
  @Prop() pagerItems!: types.PageItem[];
  @Prop() searchResultUsers!: types.ListItem[];
  @Prop({ default: false }) loading!: boolean;

  T = T;
  ui = ui;
  searchedUsername: null | types.ListItem = null;

  onSubmit(): void {
    if (!this.searchedUsername) return;
    window.location.href = `/profile/${encodeURIComponent(
      this.searchedUsername.key,
    )}`;
  }
}
</script>

<style scoped>
[data-authors-rank] {
  max-width: 52rem;
  margin: 0 auto;
}

[data-authors-rank] .tags-input-wrapper-default {
  padding: 0.35rem 0.25rem 0.7rem 0.25rem;
}

.rank-title {
  font-size: 1.25rem;
  text-align: center;
}

.table th.text-center:nth-child(1),
.table td.text-center:nth-child(1),
.table th.text-center:nth-child(3),
.table td.text-center:nth-child(3) {
  width: 20%;
}

.table th.text-center:nth-child(2),
.table td.text-center:nth-child(2) {
  width: 60%;
}
</style>
