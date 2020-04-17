<template>
  <div class="card">
    <h5 class="card-header">
      {{
        isIndex
          ? UI.formatString(T.rankHeader, {
              count: length,
            })
          : UI.formatString(T.rankRangeHeader, {
              lowCount: (page - 1) * length + 1,
              highCount: page * length,
            })
      }}
    </h5>
    <div class="card-body" v-if="!isIndex">
      <label
        ><omegaup-autocomplete
          class="form-control"
          v-bind:init="el => typeahead.userTypeahead(el)"
          v-model="searchedUsername"
        ></omegaup-autocomplete
      ></label>
      <button class="btn btn-primary" type="button" v-on:click="onSubmit">
        {{ T.searchUser }}
      </button>
      <template v-if="page &gt; 1">
        <a class="prev" v-bind:href="prevPageFilter">{{ T.wordsPrevPage }}</a>
        <span class="delimiter" v-show="shouldShowNextPage">|</span>
      </template>
      <a
        class="next"
        v-bind:href="nextPageFilter"
        v-show="shouldShowNextPage"
        >{{ T.wordsNextPage }}</a
      >
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
        <span class="label label-info">{{ T.mustLoginToFilterUsers }}</span>
      </template>
      <template v-else-if="!isIndex">
        <span class="label label-info">{{
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
        <tr v-bind:key="index" v-for="(rank, index) in ranking">
          <th scope="row">{{ rank.ranking }}</th>
          <td>
            <omegaup-countryflag
              v-bind:country="rank.country"
            ></omegaup-countryflag>
            <omegaup-user-username
              v-bind:classname="rank.classname"
              v-bind:linkify="true"
              v-bind:username="rank.username"
            ></omegaup-user-username>
            <span v-if="rank.name && length !== 5"
              ><br />
              {{ rank.name }}</span
            >
          </td>
          <td class="text-right">{{ rank.score }}</td>
          <td class="text-right" v-if="!isIndex">
            {{ rank.problemsSolvedUser }}
          </td>
        </tr>
      </tbody>
    </table>
    <div class="card-footer">
      <template v-if="isIndex">
        <a href="/rank/">{{ T.rankViewFull }}</a>
      </template>
      <template v-else="">
        <template v-if="page &gt; 1">
          <a class="prev" v-bind:href="prevPageFilter">{{ T.wordsPrevPage }}</a>
          <span class="delimiter" v-show="shouldShowNextPage"
            >|</span
          > </template
        ><a
          class="next"
          v-bind:href="nextPageFilter"
          v-show="shouldShowNextPage"
          >{{ T.wordsNextPage }}</a
        >
      </template>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

import { OmegaUp } from '../../omegaup';
import T from '../../lang';
import * as UI from '../../ui';
import * as typeahead from '../../typeahead';
import Autocomplete from '../Autocomplete.vue';
import CountryFlag from '../CountryFlag.vue';
import user_Username from '../user/Username.vue';

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

  T = T;
  UI = UI;
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
    window.location.search = UI.buildURLQuery(queryParameters);
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

  get shouldShowNextPage(): boolean {
    return this.length * this.page < this.resultTotal;
  }
}
</script>
