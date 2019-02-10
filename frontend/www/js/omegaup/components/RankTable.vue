<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <template v-if="!isIndex">
        <div class="text-right">
          <form v-on:submit.prevent="onSubmit">
            <div class="form-inline">
              <div class="form-group">
                     <omegaup-autocomplete class="form-control"
                     v-model="Searched_user"
                     v-bind:init=
                     "el =&gt; UI.userTypeahead(el)">
                    </omegaup-autocomplete>
              </div>
              <input class="btn btn-primary btn-lg active"
                     type="submit"
                     value="Search User">
            </div>
          </form>
        </div>
        <h3 class="panel-title">{{ UI.formatString(T.rankRangeHeader,
        {lowCount:(page-1)*length+1,highCount:page*length}) }}</h3>
        <template v-if="page &gt; 1">
          <a class="prev"
                    v-bind:href="prevPageFilter">{{ T.wordsPrevPage }}</a> <span class="delimiter"
                    v-show="shouldShowNextPage">|</span>
        </template><a class="next"
                  v-bind:href="nextPageFilter"
                  v-show="shouldShowNextPage">{{ T.wordsNextPage }}</a>
        <template v-if="Object.keys(availableFilters).length &gt; 0">
          <select class="filter"
                    v-model="filter"
                    v-on:change="filterChange">
            <option value="">
              {{ T.wordsSelectFilter }}
            </option>
            <option v-bind:value="key"
                    v-for="(item,key,index) in availableFilters">
              {{ item }}
            </option>
          </select>
        </template>
      </template>
      <template v-else="">
        <h3 class="panel-title">{{ UI.formatString(T.rankHeader,{count:length}) }}</h3>
      </template>
    </div>
    <div class="panel-body no-padding">
      <div class="table-responsive">
        <table class="table table-striped table-hover no-margin">
          <thead>
            <tr>
              <th>#</th>
              <th colspan="2">{{ T.wordsUser }}</th>
              <th class="numericColumn">{{ T.rankScore }}</th>
              <th class="numericColumn"
                  v-if="!isIndex">{{ T.rankSolved }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="rank in ranking">
              <td>{{ rank.rank }}</td>
              <td><img height="11"
                   v-bind:src="flagURL(rank)"
                   v-bind:title="rank.country"
                   v-if="rank.country"
                   width="16"></td>
              <td class="forcebreaks forcebreaks-top-5"><strong><a v-bind:href=
              "`/profile/${rank.username}`">{{ rank.username }}</a></strong><span v-if=
              "rank.name == null || length == 5">&nbsp;</span> <span v-else=""><br>
              {{ rank.name }}</span></td>
              <td class="numericColumn">{{ rank.score }}</td>
              <td class="numericColumn"
                  v-if="!isIndex">{{ rank.problemsSolvedUser }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="container-fluid">
        <div class="col-xs-12 vertical-padding">
          <template v-if="isIndex">
            <a href="/rank/">{{ T.rankViewFull }}</a>
          </template>
          <template v-else="">
            <template v-if="page &gt; 1">
              <a class="prev"
                        v-bind:href="prevPageFilter">{{ T.wordsPrevPage }}</a> <span class=
                        "delimiter"
                        v-show="shouldShowNextPage">|</span>
            </template><a class="next"
                      v-bind:href="nextPageFilter"
                      v-show="shouldShowNextPage">{{ T.wordsNextPage }}</a>
          </template><br>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {T} from '../omegaup.js';
import UI from '../ui.js';
import {OmegaUp} from '../omegaup.js';
import Autocomplete from './Autocomplete.vue';

export default {
  props: {
    page: Number,
    length: Number,
    isIndex: Boolean,
    availableFilters: undefined,
    filter: String,
    ranking: Array,
    resultTotal: Number,
  },
  data: function() {
    return { T: T, UI: UI,Searched_user:'',}
  },
  methods: {
    onSubmit: function() {
      var Searched_user_url='/profile/'+this.Searched_user
      window.location = Searched_user_url;
     },

    filterChange: function() {
      // change url parameters with jquery
      // https://samaxes.com/2011/09/change-url-parameters-with-jquery/
      var queryParameters = {}, queryString = location.search.substring(1),
          re = /([^&=]+)=([^&]*)/g, m;
      while (m = re.exec(queryString)) {
        queryParameters[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
      }
      if (this.filter !== '') {
        queryParameters['filter'] = this.filter;
      } else {
        delete queryParameters['filter'];
      }
      var url = Object.keys(queryParameters)
                    .map(function(k) {
                      return encodeURIComponent(k) + '=' +
                             encodeURIComponent(queryParameters[k])
                    })
                    .join('&');
      window.location.search = url;
    },
    flagURL(rank) {
      if (!rank.country) return '';
      return `/media/flags/${rank.country.toLowerCase()}.png`;
    },
  },
  computed: {
    nextPageFilter: function() {
      if (this.filter)
        return `/rank?page=${this.page + 1}&filter=${encodeURIComponent(this.filter)}`;
      else
        return `/rank?page=${this.page + 1}`;
    },
    prevPageFilter: function() {
      if (this.filter)
        return `/rank?page=${this.page - 1}&filter=${encodeURIComponent(this.filter)}`;
      else
        return `/rank?page=${this.page - 1}`;
    },
    shouldShowNextPage: function() {
      return this.length * this.page < this.resultTotal;
    }
  },
  components: {
    'omegaup-autocomplete': Autocomplete,
  },
};
</script>
