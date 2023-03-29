<template>
  <div class="container-lg p-md-5">
    <div class="card">
      <h5 class="card-header">
        {{
          ui.formatString(T.authorRankRangeHeader, {
            lowCount: (page - 1) * length + 1,
            highCount: page * length,
          })
        }}
      </h5>
      <table class="table mb-0 table-responsive-sm">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">{{ T.contentCreator }}</th>
            <th scope="col" class="text-right">{{ T.rankScore }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(author, index) in rankingData.ranking" :key="index">
            <th scope="row">{{ author.author_ranking || index }}</th>
            <td>
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
            <td class="text-right">
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

@Component({
  components: {
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
