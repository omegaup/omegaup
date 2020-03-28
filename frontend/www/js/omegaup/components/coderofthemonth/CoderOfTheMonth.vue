<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">{{ T.index }}</h3>
    </div>
    <div class="panel-body">
      <div class="rss_element">
        <h4 class="text-center">
          <omegaup-user-username
            v-bind:classname="classname"
            v-bind:linkify="true"
            v-bind:username="username"
            v-bind:country="country_id"
          ></omegaup-user-username>
        </h4>
        <div class="text-center">
          <a v-bind:href="`/profile/${username}/`">
            <img v-bind:src="gravatar_92" />
          </a>
        </div>
        <div>{{ name }}</div>
        <div>{{ school }}</div>
        <div v-if="state && country !== 'xx'">{{ state }}, {{ country }}</div>
      </div>
    </div>
    <div class="panel-body">
      <a href="/coderofthemonth/">{{ T.coderOfTheMonthFullList }}</a>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import user_Username from '../user/Username.vue';

@Component({
  components: {
    'omegaup-user-username': user_Username,
  },
})
export default class CoderOfTheMonth extends Vue {
  @Prop() username!: string;
  @Prop() classname!: string;
  @Prop() name!: string;
  @Prop() country!: string;
  @Prop() country_id!: string;
  @Prop() state!: string;
  @Prop() school!: string;
  @Prop() gravatar_92!: string;

  T = T;
}
</script>
