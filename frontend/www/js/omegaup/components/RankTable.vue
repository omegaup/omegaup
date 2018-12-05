<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <template v-if="!isIndex">
        <h3 class="panel-title">{{ UI.formatString(T.rankRangeHeader,
        {lowCount:(page-1)*length+1,highCount:page*length}) }}</h3>
        <template v-if="page &gt; 1">
          <a class="prev"
                    v-bind:href="`/rank/?page=${page-1}`">{{ T.wordsPrevPage }}</a> <span class=
                    "delimiter">|</span>
        </template><a class="next"
                  v-bind:href="`/rank/?page=${page+1}`">{{ T.wordsNextPage }}</a>
        <template v-if="Object.keys(availableFilters).length &gt; 0">
          <select class="filter"
                    v-model="filter_key"
                    v-on:change="filterChange">
            <option value="">
              {{ T.wordsSelectFilter }}
            </option>
            <option v-bind:selected="filterSelected(key)"
                    v-bind:value="key"
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
            <tr v-for="rank in ranks">
              <td>{{ rank.rank }}</td>
              <td>{{ rank.flag }}</td>
              <td class="forcebreaks forcebreaks-top-5"><strong><a v-bind:href=
              "`/profile/${rank.username}`">{{ rank.username }}</a></strong></td>
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
            <a href='/rank/'>{{ T.rankViewFull }}</a>
          </template>
          <template v-else="">
            <template v-if="page &gt; 1">
              <a class="prev"
                        v-bind:href="prevPageFilter">{{ T.wordsPrevPage }}</a> <span class=
                        "delimiter">|</span>
            </template><a class="next"
                      v-bind:href="nextPageFilter">{{ T.wordsNextPage }}</a>
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

export default {
  props: {
    page: Number,
    length: Number,
    isIndex: Boolean,
    availableFilters: undefined,
    filter: String,
  },
  data: function() {
    return { T: T, UI: UI, filter_key: this.filter, ranks:[] }
  },
  mounted: function() {
    var self = this;
    var problemsSolved = self.$el.querySelector("table");
    var length = self.length;
    var page = self.page;
    var filter = self.filter;
    var isIndex = (self.isIndex === true);

    omegaup.API.User.rankByProblemsSolved(
                        {offset: page, rowcount: length, filter: filter})
        .then(function(result) {
          for (var i = 0; i < result.rank.length; ++i) {
            var user = result.rank[i];
            var problemsSolvedUser = undefined;
            if (!isIndex) {
              problemsSolvedUser = user.problems_solved;
            }
            self.ranks.add({
              rank: user.rank,
              flag: omegaup.UI.getFlag(user.country_id),
              username: user.username,
              name: (user.name == null || length == 5 ? '&nbsp;' :
                                                        ('<br/>' + user.name)),
              score: user.score,
              problemsSolvedUser: problemsSolvedUser,
            });
          }
          if (length * page >= result.total) {
            var temp = self.$el.querySelectorAll('.next,.delimiter');
            for (var i = 0; i < temp.length; i++) {
              temp[i].style.display = "none";
            }
          }
          self.$forceUpdate();
        })
        .fail(omegaup.UI.apiError);
  },
  methods: {
    filterSelected: function(key) {
      if (this.filter == key) return "selected";
    },
    filterChange: function() {
      // change url parameters with jquery
      // https://samaxes.com/2011/09/change-url-parameters-with-jquery/
      var queryParameters = {}, queryString = location.search.substring(1),
          re = /([^&=]+)=([^&]*)/g, m;
      while (m = re.exec(queryString)) {
        queryParameters[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
      }
      if (this.filter_key !== '') {
        queryParameters['filter'] = this.filter_key;
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
  },
  computed: {
    nextPageFilter: function() {
      if (this.filter != null)
        return `/rank?page=${this.page + 1}&filter=${encodeURIComponent(this.filter)}`;
      else
        return `/rank?page=${this.page + 1}`;
    },
    prevPageFilter: function() {
      if (this.filter != null)
        return `/rank?page=${this.page - 1}&filter=${encodeURIComponent(this.filter)}`;
      else
        return `/rank?page=${this.page - 1}`;
    },
  },
};
</script>
