import problem_creator from "../../components/problem/creator/Creator.vue"
import {omegaup, OmegaUp} from "../../omegaup"
import T from '../../lang'
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const creator = new Vue({
    el: '#main-container',
    components: {
      'creator-main': problem_creator,
    },
    render: function(createElement) {
      return createElement('creator-main')
    }
  })
})