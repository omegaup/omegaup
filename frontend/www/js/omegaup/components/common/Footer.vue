<template>
  <footer class="common-footer text-center">
    <div class="container-xl">
      <div
        class="footer-navigation d-table d-lg-flex justify-content-between py-5"
      >
        <div class="footer-brand mb-4 mb-lg-0">
          <img
            class="footer-logo d-block mx-auto"
            width="120"
            src="/media/logo-main-white.svg"
          />
          <div class="slogan">
            {{ T.frontPageFooter }}
          </div>
        </div>
        <div class="footer-list-section w-50 mb-4 w-md-auto mb-lg-0">
          <h4>{{ T.frontPageFooterSponsors }}</h4>
          <ul>
            <li class="mt-1">
              <a href="https://www.aboutamazon.com/" target="_blank">
                <img
                  class="sponsor-logo"
                  src="/media/homepage/amazon_logo.png"
                  alt="AmazonLogo"
                />
              </a>
            </li>
            <li class="mt-1">
              <a href="https://www.f5.com/" target="_blank">
                <img
                  class="sponsor-logo"
                  src="/media/homepage/f5_logo.png"
                  alt="F5Logo"
                />
              </a>
            </li>
            <li class="mt-1">
              <a href="https://replit.com/" target="_blank">
                <img
                  class="sponsor-logo"
                  src="/media/homepage/replit_logo.png"
                  alt="ReplitLogo"
                />
              </a>
            </li>
          </ul>
        </div>
        <div class="footer-list-section w-50 mb-4 w-md-auto mb-lg-0">
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
              <a href="https://blog.omegaup.com" target="_blank">{{
                T.navBlog
              }}</a>
            </li>
          </ul>
        </div>
        <div class="footer-list-section w-50 mb-4 w-md-auto mb-lg-0">
          <h4>{{ T.frontPageFooterOrganization }}</h4>
          <ul>
            <li class="mt-1">
              <a href="https://omegaup.org/#about" target="_blank">{{
                T.frontPageFooterAboutUs
              }}</a>
            </li>
            <li class="mt-1">
              <a href="https://omegaup.org/#team" target="_blank">{{
                T.frontPageFooterTeam
              }}</a>
            </li>
          </ul>
        </div>
        <div class="footer-list-section w-50 mb-4 w-md-auto mb-lg-0">
          <h4>{{ T.frontPageDevelopers }}</h4>
          <ul>
            <li class="mt-1">
              <a
                href="https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-empezar-a-desarrollar"
                target="_blank"
                >{{ T.frontPageFooterHelpUs }}</a
              >
            </li>
            <li class="mt-1">
              <a href="https://github.com/omegaup/omegaup" target="_blank">
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
        <div class="footer-list-section w-50 mb-4 w-md-auto mb-lg-0">
          <h4>{{ T.frontPageFooterContact }}</h4>
          <ul>
            <li class="mt-1">
              <a href="mailto:hello@omegaup.com">hello@omegaup.com</a>
            </li>
          </ul>
          <div class="social-icons my-0 mx-auto">
            <a
              class="text-nowrap"
              href="https://github.com/omegaup/omegaup/"
              target="_blank"
            >
              <font-awesome-icon :icon="['fab', 'github']" />
              GitHub
            </a>
            |
            <a
              class="text-nowrap"
              href="https://www.facebook.com/omegaup/"
              target="_blank"
            >
              <font-awesome-icon :icon="['fab', 'facebook']" />
              Facebook
            </a>
            |
            <a
              class="text-nowrap"
              href="https://discord.gg/K3JFd9d3wk"
              target="_blank"
            >
              <font-awesome-icon :icon="['fab', 'discord']" />
              Discord
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
          {{
            ui.formatString(T.frontPageFooterCopyright, {
              currentYear: new Date().getFullYear(),
            })
          }}
        </div>
        <ul class="m-0 list-unstyled text-right">
          <li>
            <a
              href="https://blog.omegaup.com/codigo-de-conducta-en-omegaup/"
              target="_blank"
              >{{ T.frontPageFooterCodeConduct }}</a
            >
          </li>
          <li>
            <a
              href="https://blog.omegaup.com/privacy-policy/"
              target="_blank"
              >{{ T.frontPageFooterPrivacyPolicy }}</a
            >
          </li>
        </ul>
      </div>
    </div>
  </footer>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator';
import * as ui from '../../ui';
import T from '../../lang';
import { reportAnIssueURL } from '../../errors';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faFacebook,
  faGithub,
  faDiscord,
} from '@fortawesome/free-brands-svg-icons';
library.add(faFacebook, faGithub, faDiscord);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class Footer extends Vue {
  @Prop() isLoggedIn!: boolean;
  @Prop() omegaUpLockDown!: boolean;

  T = T;
  ui = ui;
  reportAnIssueURL = reportAnIssueURL;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.common-footer {
  background-color: $omegaup-primary--darker;
  color: $omegaup-white;
  flex-grow: 1;

  .footer-navigation {
    .footer-brand {
      max-width: 200px;

      @media only screen and (max-width: 991px) {
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
      // On medium sizes, this will work as an inline grid (not 100% width)
      @media only screen and (max-width: 991px) {
        display: inline-grid;
      }

      ul {
        list-style-type: none;
        padding: 0;
        margin: 0 auto;
        text-align: center;

        li {
          margin-top: 8px;
          padding: 0;

          a {
            text-decoration: none;
            color: white;

            &:hover {
              color: gray;
            }
          }
        }
      }

      img.sponsor-logo {
        width: 120px;
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
