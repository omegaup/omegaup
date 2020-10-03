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
    <div class="card-body" v-if="!isIndex">
      <label
        ><omegaup-autocomplete
          class="form-control"
          v-bind:init="(el) => typeahead.userTypeahead(el)"
          v-model="searchedUsername"
        ></omegaup-autocomplete
      ></label>
      <button class="btn btn-primary" type="button" v-on:click="onSubmit">
        {{ T.searchUser }}
      </button>
      <template v-if="Object.keys(availableFilters).length &gt; 0">
        <select class="filter" v-model="filter" v-on:change="onFilterChange">
          <option value="">
            {{ T.wordsSelectFilter }}
          </option>
          <option
            v-bind:key="index"
            v-bind:value="key"
            v-for="(item, key, index) in availableFilters"
          >
            {{ item }}
          </option>
        </select>
      </template>
      <template v-else-if="!isLogged &amp;&amp; !isIndex">
        <span class="badge badge-info">{{ T.mustLoginToFilterUsers }}</span>
      </template>
      <template v-else-if="!isIndex">
        <span class="badge badge-info">{{
          T.mustUpdateBasicInfoToFilterUsers
        }}</span>
      </template>
    </div>
    <table class="table mb-0">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">{{ T.wordsUser }}</th>
          <th scope="col" class="text-right">{{ T.rankScore }}</th>
          <th scope="col" class="text-right" v-if="!isIndex">
            {{ T.rankSolved }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-bind:key="index" v-for="(user, index) in ranking">
          <th scope="row">{{ user.rank }}</th>
          <td>
            <omegaup-countryflag
              v-bind:country="user.country"
            ></omegaup-countryflag>
            <omegaup-user-username
              v-bind:classname="user.classname"
              v-bind:linkify="true"
              v-bind:username="user.username"
            ></omegaup-user-username>
            <span v-if="user.name && length !== 5"
              ><br />
              {{ user.name }}</span
            >
          </td>
          <td class="text-right">{{ user.score }}</td>
          <td class="text-right" v-if="!isIndex">
            {{ user.problems_solved }}
          </td>
        </tr>
      </tbody>
    </table>
    <div class="card-footer" v-if="isIndex">
      <a href="/rank/">{{ T.wordsSeeGeneralRanking }}</a>
    </div>
    <div class="card-footer" v-else>
      <omegaup-common-paginator
        v-bind:pagerItems="pagerItems"
      ></omegaup-common-paginator>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import * as typeahead from '../../typeahead';
import Autocomplete from '../Autocomplete.vue';
import CountryFlag from '../CountryFlag.vue';
import user_Username from '../user/Username.vue';
import common_Paginator from '../common/Paginatorv2.vue';

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
    'omegaup-autocomplete': Autocomplete,
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

  T = T;
  ui = ui;
  typeahead = typeahead;
  searchedUsername = '';

  onSubmit(): void {
    window.location.href = `/profile/${encodeURIComponent(
      this.searchedUsername,
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
