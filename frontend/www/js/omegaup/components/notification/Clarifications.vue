<template>
  <li class="dropdown">
    <a
      aria-expanded="false"
      aria-haspopup="true"
      class="notification-button dropdown-toggle"
      data-toggle="dropdown"
      href="#"
      role="button"
      v-on:click="unread = false"
      ><span class="notification-icon glyphicon glyphicon-bell"></span>
      <span
        class="notification-counter label"
        v-bind:class="{ 'label-danger': unread }"
        v-if="clarifications &amp;&amp; clarifications.length &gt; 0"
        v-model="unread"
        >{{ clarifications.length }}</span
      ></a
    >
    <ul class="dropdown-menu">
      <li class="empty" v-if="!clarifications || clarifications.length === 0">
        {{ T.notificationsNoNewNotifications }}
      </li>
      <li v-else="">
        <ul class="notification-drawer">
          <li v-for="clarification in clarifications">
            <button
              aria-label="Close"
              class="close"
              type="button"
              v-on:click.prevent="onCloseClicked(clarification)"
            >
              <span aria-hidden="true">×</span>
            </button>
            <a v-bind:href="anchor(clarification)"
              ><span>{{ clarification.problem_alias }}</span> —
              <span>{{ clarification.author }}</span>
              <pre>{{ clarification.message }}</pre>
              <hr v-if="clarification.answer" />
              <pre v-if="clarification.answer">{{
                clarification.answer
              }}</pre></a
            >
          </li>
        </ul>
      </li>
      <li
        class="divider"
        role="separator"
        v-if="clarifications &amp;&amp; clarifications.length &gt; 1"
      ></li>
      <li v-if="clarifications &amp;&amp; clarifications.length &gt; 1">
        <a
          class="notification-clear"
          href="#"
          v-on:click.prevent="onMarkAllAsRead"
          ><span class="glyphicon glyphicon-align-right"></span>
          {{ T.notificationsMarkAllAsRead }}</a
        >
      </li>
    </ul>
  </li>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';

@Component
export default class Clarifications extends Vue {
  @Prop() data!: omegaup.Clarification[];
  T = T;

  unread: boolean = true;
  flashInterval: number = 0;
  clarifications: omegaup.Clarification[] = this.data;

  @Watch('data')
  onPropertyChanged(
    newValue: Array<omegaup.Clarification>,
    oldValue: Array<omegaup.Clarification>,
  ): void {
    this.clarifications = newValue;
    this.unread = true;
  }

  @Watch('unread')
  onPropertyChange(newValue: boolean, oldValue: boolean): void {
    let self = this;
    if (newValue) {
      if (self.flashInterval) return;
      self.flashInterval = setInterval(self.flashTitle, 1000);
    } else {
      if (!self.flashInterval) return;
      clearInterval(self.flashInterval);
      self.flashInterval = 0;
      if (document.title.indexOf('!') === 0) {
        document.title = document.title.substring(2);
      }
    }
  }

  anchor(clarification: omegaup.Clarification): string {
    return `#clarifications/clarification-${clarification.clarification_id}`;
  }

  flashTitle(reset: boolean): void {
    if (document.title.indexOf('!') === 0) {
      document.title = document.title.substring(2);
    } else if (!reset) {
      document.title = '! ' + document.title;
    }
  }

  onCloseClicked(clarification: omegaup.Clarification): void {
    let self = this;
    const id = `clarification-${clarification.clarification_id}`;
    self.clarifications = self.clarifications.filter(function(element) {
      return element.clarification_id !== clarification.clarification_id;
    });
    localStorage.setItem(id, Date.now().toString());
  }

  onMarkAllAsRead(): void {
    let self = this;
    for (let key in self.clarifications) {
      const id = `clarification-${self.clarifications[key].clarification_id}`;
      localStorage.setItem(id, Date.now().toString());
    }
    self.clarifications = [];
  }
}
</script>
