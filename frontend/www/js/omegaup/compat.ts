import Vue from 'vue';
import VueCookies from 'vue-cookies';
import Clipboard from 'v-clipboard';
import T from './lang';
import * as ui from './ui';
import * as time from './time';
import { omegaup } from './omegaup';
import { ContestClarificationType } from './arena/clarifications';

Vue.configureCompat({ MODE: 2 });
Vue.use(VueCookies, { expires: -1 });
Vue.use(Clipboard);
Vue.mixin({
  created() {
    this.T = T;
    this.ui = ui;
    this.time = time;
    this.omegaup = omegaup;
    this.ContestClarificationType = ContestClarificationType;
  },
});
