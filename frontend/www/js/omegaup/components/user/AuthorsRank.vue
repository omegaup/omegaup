<template>
  <div class="card">
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
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';
import user_Username from '../user/Username.vue';
import { types } from '../../api_types';
import CountryFlag from '../CountryFlag.vue';
import common_Paginator from '../common/Paginator.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-user-username': user_Username,
    'omegaup-countryflag': CountryFlag,
    'omegaup-common-paginator': common_Paginator,
  },
})
export default class AuthorsRank extends Vue {
  @Prop() page!: number;
  @Prop() length!: number;
  @Prop() rankingData!: types.AuthorsRank;
  @Prop() pagerItems!: types.PageItem[];

  T = T;
  ui = ui;
}
</script>

<style scoped>
.max-width-rank {
  max-width: 52rem;
  margin: 0 auto;
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
