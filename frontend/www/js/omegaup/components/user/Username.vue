<template>
  <span :class="classname" :title="username">
    <omegaup-countryflag
      v-if="country != null"
      :country="country"
    ></omegaup-countryflag>

    <template v-if="linkify">
      <a
        v-if="emitClickEvent"
        :href="href"
        :class="classname"
        :title="username"
        @click="$emit('click', username)"
        >{{ nameWithUsername }}</a
      >
      <a
        v-else
        :class="classname"
        :title="username"
        :href="`/profile/${username}/`"
        >{{ nameWithUsername }}</a
      >
    </template>
    <template v-else>
      <slot name="username-url">
        {{ nameWithUsername }}
      </slot>
    </template>
  </span>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import CountryFlag from '../CountryFlag.vue';

export default defineComponent({
  name: 'Username',
  components: {
    'omegaup-countryflag': CountryFlag,
  },
  props: {
    username: {
      type: String,
      default: '',
    },
    name: {
      type: String as PropType<null | string>,
      default: null,
    },
    classname: {
      type: String,
      default: '',
    },
    linkify: {
      type: Boolean,
      default: false,
    },
    href: {
      type: String,
      default: '#',
    },
    country: {
      type: String as PropType<null | string>,
      default: null,
    },
    emitClickEvent: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['click'],
  computed: {
    nameWithUsername(): string {
      if (this.name) {
        return `${this.name} (${this.username})`;
      }
      return this.username;
    },
  },
});
</script>

<style lang="scss" scope>
@import '../../../../sass/main.scss';

span {
  display: inline-block;
  max-width: 100%;
  overflow-wrap: anywhere;
  word-break: break-word;
}

span a {
  display: inline-block;
  max-width: 100%;
  white-space: normal;
}

.user-rank-unranked,
.user-rank-beginner,
.user-rank-specialist,
.user-rank-expert,
.user-rank-master,
.user-rank-international-master {
  font-weight: bold;
}

.user-rank-beginner {
  color: var(--user-rank-beginner-font-color);
}

.user-rank-specialist {
  color: var(--user-rank-specialist-font-color);
}

.user-rank-expert {
  color: var(--user-rank-expert-font-color);
}

.user-rank-master {
  color: var(--user-rank-master-font-color);
}

.user-rank-international-master {
  color: var(--user-rank-international-master-font-color);
}
</style>
