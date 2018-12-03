<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <template v-if="!is_index">
        <h3 class="panel-title">{{ UI.formatString(T.rankRangeHeader,
        {lowCount:(page-1)*length+1,highCount:page*length}) }}</h3>
        <template v-if="page &gt; 1">
          <a class="prev"
                    v-bind:href="`/rank/?page=${page-1}`">{{ T.wordsPrevPage }}</a> <span class=
                    "delimiter">|</span>
        </template><a class="next"
                  v-bind:href="`/rank/?page=${page+1}`">{{ T.wordsNextPage }}</a>
        <template v-if="Object.keys(availableFilters).length &gt; 0">
          <select class="filter">
            <option value="">
              {{ T.wordsSelectFilter }}
            </option>
            <option v-bind:selected="filter_selected(key)"
                    v-bind:value="index"
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
        <table class="table table-striped table-hover no-margin"
               v-bind:data-filter="data_filter"
               v-bind:data-length="length"
               v-bind:data-page="page"
               v-bind:is-index="is_index">
          <thead>
            <tr>
              <th>#</th>
              <th colspan="2">{{ T.wordsUser }}</th>
              <th class="numericColumn">{{ T.rankScore }}</th>
              <th class="numericColumn"
                  v-if="!is_index">{{ T.rankSolved }}</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="container-fluid">
        <div class="col-xs-12 vertical-padding">
          <template v-if="is_index">
            <a href='/rank/'>{{ T.rankViewFull }}</a>
          </template>
          <template v-else="">
            <template v-if="page &gt; 1">
              <a class="prev"
                        v-bind:href="prev_page_filter">{{ T.wordsPrevPage }}</a> <span class=
                        "delimiter">|</span>
            </template><a class="next"
                      v-bind:href="next_page_filter">{{ T.wordsNextPage }}</a>
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
    is_index: Boolean,
    availableFilters: Array,
    filter: String,
  },
  data: function() {
    return { T: T, UI: UI, }
  },
  mounted: function() {
    var self = this;
    var problemsSolved = self.$el.querySelector("table");
    var length = parseInt(problemsSolved.getAttribute("data-length"));
    var page = parseInt(problemsSolved.getAttribute("data-page"));
    var filter = problemsSolved.getAttribute("data-filter");
    var isIndex = (problemsSolved.getAttribute('is-index') === true);
    var rowTemplate =
        '<tr>' +
        '<td>%(rank)</td><td class="flagColumn">%(flag)</td>' +
        '<td class="forcebreaks forcebreaks-top-5"><strong>' +
        '<a href="/profile/%(username)">%(username)</a></strong>' +
        '%(name)</td>' +
        '<td class="numericColumn">%(score)</td>' +
        '%(problemsSolvedRow)' +
        '</tr>';
    omegaup.API.User.rankByProblemsSolved(
                        {offset: page, rowcount: length, filter: filter})
        .then(function(result) {
          var html = '';
          for (var i = 0; i < result.rank.length; ++i) {
            var user = result.rank[i];
            var problemsSolvedRow = '';
            if (!isIndex) {
              problemsSolvedRow =
                  "<td class='numericColumn'>" + user.problems_solved + '</td>';
            }
            html += omegaup.UI.formatString(rowTemplate, {
              rank: user.rank,
              flag: omegaup.UI.getFlag(user.country_id),
              username: user.username,
              name: (user.name == null || length == 5 ? '&nbsp;' :
                                                        ('<br/>' + user.name)),
              score: user.score,
              problemsSolvedRow: problemsSolvedRow,
            });
          }
          problemsSolved.querySelector('tbody').innerHTML = html;
          if (length * page >= result.total) {
            self.$el.querySelector('.next,.delimiter').style.display = "none"
          }
        })
        .fail(omegaup.UI.apiError);
  },
  methods: {
    filter_selected: function(key) {
      if (this.filter == key) return "selected";
    },
  },
  computed: {
    next_page_filter: function() {
      if (this.filter != null)
        return "/rank/?page=" + (this.page + 1).toString() + "&filter=" +
               this.filter;
      else
        return "/rank/?page=" + (this.page + 1).toString();
    },
    prev_page_filter: function() {
      if (this.filter != null)
        return "/rank/?page=" + (this.page - 1).toString() + "&filter=" +
               this.filter;
      else
        return "/rank/?page=" + (this.page - 1).toString();
    },
    data_filter: function() {
      if (this.filter != null) return this.filter
    },
  },
};
</script>
