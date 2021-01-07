<template>
  <footer class="common-footer text-center">
    <div class="container-xl">
      <div
        class="footer-navigation d-table d-md-flex justify-content-between py-5"
      >
        <div class="footer-brand mb-4 mb-md-0">
          <img
            class="footer-logo d-block mx-auto"
            width="120"
            src="/media/logo-main-white.svg"
          />
          <div class="slogan">
            {{ T.frontPageFooter }}
          </div>
        </div>
        <div class="footer-list-section w-50 mb-4 w-md-auto mb-md-0">
          <h4>{{ T.frontPageFooterSite }}</h4>
          <ul>
            <li class="mt-1">
              <a href="/arena/">{{ T.navContests }}</a>
            </li>
            <li class="mt-1">
              <a href="/course/">{{ T.navCourses }} </a>
            </li>
            <li class="mt-1">
              <a href="/problem/">{{ T.navProblems }}</a>
            </li>
            <li class="mt-1">
              <a href="/rank/">{{ T.navRanking }}</a>
            </li>
            <li class="mt-1">
              <a href="https://blog.omegaup.com">{{ T.navBlog }}</a>
            </li>
          </ul>
        </div>
        <div class="footer-list-section w-50 mb-4 w-md-auto mb-md-0">
          <h4>{{ T.frontPageFooterOrganization }}</h4>
          <ul>
            <li class="mt-1">
              <a href="https://omegaup.org/#about">{{
                T.frontPageFooterAboutUs
              }}</a>
            </li>
            <li class="mt-1">
              <a href="https://omegaup.org/#team">{{
                T.frontPageFooterTeam
              }}</a>
            </li>
          </ul>
        </div>
        <div class="footer-list-section w-50 mb-4 w-md-auto mb-md-0">
          <h4>{{ T.frontPageDevelopers }}</h4>
          <ul>
            <li class="mt-1">
              <a
                href="https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-empezar-a-desarrollar"
                >{{ T.frontPageFooterHelpUs }}</a
              >
            </li>
            <li class="mt-1">
              <a href="https://github.com/omegaup/omegaup">
                <font-awesome-icon :icon="['fab', 'github']" />
              </a>
            </li>
            <li class="mt-1">
              <a
                v-if="!omegaUpLockDown && isLoggedIn"
                href="https://github.com/omegaup/omegaup/issues/new"
                target="_blank"
                rel="nofollow"
                @click="$event.target.href = reportAnIssueURL()"
                >{{ T.reportAnIssue }}</a
              >
            </li>
          </ul>
        </div>
        <div class="footer-list-section w-50 mb-4 w-md-auto mb-md-0">
          <h4>{{ T.frontPageFooterContact }}</h4>
          <ul>
            <li class="mt-1">
              <a href="mailto:hello@omegaup.com">hello@omegaup.com</a>
            </li>
          </ul>
          <div class="social-icons my-0 mx-auto">
            <a href="https://github.com/omegaup/omegaup/">
              <font-awesome-icon :icon="['fab', 'github']" />
              GitHub
            </a>
            |
            <a href="https://www.facebook.com/omegaup/">
              <font-awesome-icon :icon="['fab', 'facebook']" />
              Facebook
            </a>
          </div>
        </div>
      </div>
    </div>
    <div class="copy">
      <div
        class="container-xl d-flex flex-wrap justify-content-between align-items-center py-3"
      >
        <div>
          {{ T.wordsCopyright }}
        </div>
        <div class="d-none d-md-block">
          <ul class="m-0">
            <li>
              <a
                href="https://blog.omegaup.com/codigo-de-conducta-en-omegaup/"
                >{{ T.frontPageFooterCodeConduct }}</a
              >
            </li>
            <li>
              <a href="https://blog.omegaup.com/privacy-policy/">{{
                T.frontPageFooterPrivacyPolicy
              }}</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </footer>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { reportAnIssueURL } from '../../errors';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faFacebook, faGithub } from '@fortawesome/free-brands-svg-icons';
library.add(faFacebook, faGithub);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class Footer extends Vue {
  @Prop() isLoggedIn!: boolean;
  @Prop() omegaUpLockDown!: boolean;

  T = T;
  reportAnIssueURL = reportAnIssueURL;
}
</script>

<style lang="scss">
@import '../../../../sass/main.scss';

.common-footer {
  background-color: $omegaup-primary--darker;
  color: $omegaup-white;

  .footer-navigation {
    .footer-brand {
      max-width: 200px;
      @media only screen and (max-width: 767px) {
        max-width: 100%;
      }

      .footer-logo,
      .slogan {
        margin-top: -2.5rem;
      }

      .slogan {
        text-transform: uppercase;
      }
    }

    .footer-list-section {
      // On small sizes, this will work as an inline grid (not 100% width)
      @media only screen and (max-width: 767px) {
        display: inline-grid;
      }
    }
  }

  a {
    text-decoration: none;
    color: white;

    &:hover {
      color: gray;
    }
  }

  .copy {
    background-color: $omegaup-primary--darkest;
  }
}
</style>
