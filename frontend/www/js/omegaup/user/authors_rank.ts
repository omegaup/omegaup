import authors_Rank from '../components/user/AuthorsRank.vue';
import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.AuthorRankTablePayload();
  const authorsRank = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-author-rank', {
        props: {
          page: payload.page,
          length: payload.length,
          rankingData: payload.ranking,
          pagerItems: payload.pagerItems,
        },
      });
    },
    components: {
      'omegaup-author-rank': authors_Rank,
    },
  });
});
