<template>
  <div class="card">
    <h5 class="card-header">
      {{
        isIndex
          ? ui.formatString(T.userRankOfTheMonthHeader, {
              count: length,
            })
          : ui.formatString(T.rankRangeHeader, {
              lowCount: (page - 1) * length + 1,
              highCount: page * length,
            })
      }}
    </h5>
    <div v-if="!isIndex" class="card-body form-row">
      <omegaup-common-typeahead
        class="col-md-4"
        :existing-options="searchResultUsers"
        :value.sync="searchedUsername"
        :max-results="10"
        @update-existing-options="
          (query) => $emit('update-search-result-users', query)
        "
      ></omegaup-common-typeahead>
      <template v-if="Object.keys(availableFilters).length > 0">
        <select
          v-model="filter"
          class="filter form-control col-md-4"
          @change="onFilterChange"
        >
          <option value="">
            {{ T.wordsSelectFilter }}
          </option>
          <option
            v-for="(item, key, index) in availableFilters"
            :key="index"
            :value="key"
          >
            {{ item }}
          </option>
        </select>
      </template>
      <template v-else-if="!isLogged &amp;&amp; !isIndex">
        <span
          class="badge badge-info col-md-5 d-flex align-items-center justify-content-center"
          >{{ T.mustLoginToFilterUsers }}</span
        >
      </template>
      <template v-else-if="!isIndex">
        <span
          class="badge badge-info col-md-5 d-flex align-items-center justify-content-center"
          >{{ T.mustUpdateBasicInfoToFilterUsers }}</span
        >
      </template>
      <button
        class="btn btn-primary form-control col-md-2 ml-auto"
        type="button"
        @click="onSubmit"
      >
        {{ T.searchUser }}
      </button>
    </div>
    <table class="table mb-0 table-responsive-sm">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">{{ T.contestParticipant }}</th>
          <th scope="col" class="text-right">{{ T.rankScore }}</th>
          <th v-if="!isIndex" scope="col" class="text-right">
            {{ T.rankSolved }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(user, index) in ranking" :key="index">
          <th scope="row">{{ user.rank }}</th>
          <td>
            <omegaup-countryflag :country="user.country"></omegaup-countryflag>
            <omegaup-user-username
              :classname="user.classname"
              :linkify="true"
              :username="user.username"
            ></omegaup-user-username>
            <span v-if="user.name && length !== 5"
              ><br />
              {{ user.name }}</span
            >
          </td>
          <td class="text-right">{{ user.score }}</td>
          <td v-if="!isIndex" class="text-right">
            {{ user.problems_solved }}
          </td>
        </tr>
      </tbody>
    </table>
    <div v-if="isIndex" class="card-footer">
      <a href="/rank/">{{ T.wordsSeeGeneralRanking }}</a>
    </div>
    <div v-else class="card-footer">
      <omegaup-common-paginator
        :pager-items="pagerItems"
      ></omegaup-common-paginator>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import common_Typeahead from '../common/Typeahead.vue';
import CountryFlag from '../CountryFlag.vue';
import user_Username from '../user/Username.vue';
import common_Paginator from '../common/Paginator.vue';

interface Rank {
  country: string;
  classname?: string;
  username: string;
  name?: string;
  score: number;
  problemsSolvedUser: number;
}

@Component({
  components: {
    'omegaup-common-typeahead': common_Typeahead,
    'omegaup-countryflag': CountryFlag,
    'omegaup-user-username': user_Username,
    'omegaup-common-paginator': common_Paginator,
  },
})
export default class UserRank extends Vue {
  @Prop() page!: number;
  @Prop() length!: number;
  @Prop() isIndex!: boolean;
  @Prop() isLogged!: boolean;
  @Prop() availableFilters!: { [key: string]: string };
  @Prop() filter!: string;
  @Prop() ranking!: Rank[];
  @Prop() resultTotal!: number;
  @Prop() pagerItems!: types.PageItem[];
  @Prop() searchResultUsers!: types.ListItem[];

  T = T;
  ui = ui;
  searchedUsername: null | types.ListItem = null;

  onSubmit(): void {
    if (!this.searchedUsername) return;
    window.location.href = `/profile/${encodeURIComponent(
      this.searchedUsername.key,
    )}`;
  }

  onFilterChange(): void {
    // change url parameters with jquery
    // https://samaxes.com/2011/09/change-url-parameters-with-jquery/
    let queryParameters: { [key: string]: string } = {};
    const re = /([^&=]+)=([^&]*)/g;
    const queryString = location.search.substring(1);
    let m: string[] | null = null;
    while ((m = re.exec(queryString))) {
      queryParameters[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
    }
    if (this.filter !== '') {
      queryParameters['filter'] = this.filter;
    } else {
      delete queryParameters['filter'];
    }
    window.location.search = ui.buildURLQuery(queryParameters);
  }

  get nextPageFilter(): string {
    if (this.filter)
      return `/rank?page=${this.page + 1}&filter=${encodeURIComponent(
        this.filter,
      )}`;
    else return `/rank?page=${this.page + 1}`;
  }

  get prevPageFilter(): string {
    if (this.filter)
      return `/rank?page=${this.page - 1}&filter=${encodeURIComponent(
        this.filter,
      )}`;
    else return `/rank?page=${this.page - 1}`;
  }
}
</script>
