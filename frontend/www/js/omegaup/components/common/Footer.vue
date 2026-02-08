<template>
  <footer class="common-footer text-center mt-5">
    <div class="container-xl">
      <div class="footer-navigation d-lg-flex align-items-start py-5 m-auto">
        <div class="footer-brand mb-4 mb-lg-0 max-width-logo">
          <img
            class="footer-logo d-block mx-auto mb-1 mt-n6"
            width="120"
            src="/media/logo-main-white.svg"
          />
          <div class="slogan mx-auto">
            {{ T.frontPageFooter }}
          </div>
        </div>
        <div
          class="footer-list-section footer-contact w-50 mb-4 mb-lg-0 mx-auto"
        >
          <h4 class="column-title">{{ T.frontPageFooterContact }}</h4>
          <ul>
            <li class="mt-1">
              <a href="mailto:hello@omegaup.com">hello@omegaup.com</a>
            </li>
          </ul>
          <div
            class="social-icons my-0 mx-auto d-flex flex-md-column flex-sm-row justify-content-center flex-wrap"
          >
            <a
              class="mx-1 pt-2"
              href="https://www.facebook.com/omegaup/"
              target="_blank"
            >
              <font-awesome-icon :icon="['fab', 'facebook']" />
              Facebook
            </a>
            <a
              class="mx-1 pt-2"
              href="https://discord.gg/K3JFd9d3wk"
              target="_blank"
            >
              <font-awesome-icon :icon="['fab', 'discord']" />
              Discord
            </a>
          </div>
        </div>
        <div class="footer-list-section footer-site w-50 mb-4 mb-lg-0 mx-auto">
          <h4 class="column-title">{{ T.frontPageFooterSite }}</h4>
          <ul>
            <li class="mt-1">
              <a href="/arena/">{{ T.navContests }}</a>
            </li>
            <li class="mt-1">
              <a href="/problem/">{{ T.navProblems }}</a>
            </li>
            <li class="mt-1">
              <a href="/rank/">{{ T.navRanking }}</a>
            </li>
            <li class="mt-1">
              <a href="/course/">{{ T.navCourses }} </a>
            </li>
            <li class="mt-1">
              <a :href="OmegaUpBlogURL" target="_blank">{{ T.navBlog }}</a>
            </li>
          </ul>
        </div>
        <div
          class="footer-list-section footer-sponsors w-50 mb-4 mb-lg-0 mx-auto"
        >
          <h4 class="column-title">{{ T.frontPageFooterSponsors }}</h4>
          <ul>
            <li class="mt-4">
              <a
                href="https://news.airbnb.com/2025-community-fund/"
                target="_blank"
              >
                <img
                  class="sponsor-logo"
                  src="/media/homepage/airbnb_logo.svg"
                  alt="AirbnbLogo"
                  width="100"
                />
              </a>
            </li>
          </ul>
        </div>
        <div
          class="footer-list-section footer-organization d-inline-block w-50 mb-4"
        >
          <h4 class="column-title">{{ T.frontPageFooterOrganization }}</h4>
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
        <div
          class="footer-list-section footer-developers d-inline-block w-50 mb-4"
        >
          <h4 class="column-title">{{ T.frontPageDevelopers }}</h4>
          <ul>
            <li class="mt-1">
              <a
                href="https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Development-Environment-Setup-Process.md"
                target="_blank"
                >{{ T.frontPageFooterHelpUs }}</a
              >
            </li>
            <li class="mt-1">
              <a href="https://github.com/omegaup/omegaup" target="_blank">
                <font-awesome-icon :icon="['fab', 'github']" />
                Github
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
      </div>
    </div>
    <div class="copy mt-3">
      <div
        class="container-xl d-md-flex justify-content-between align-items-center py-3"
      >
        <ul
          class="mb-2 m-md-0 list-unstyled d-flex justify-content-around d-md-inline-flex order-md-12"
        >
          <li class="pr-2">
            <a :href="CodeofConductPolicyURL" target="_blank">
              {{ T.frontPageFooterCodeConduct }}
            </a>
          </li>
          <li>
            <a :href="PrivacyPolicyURL" target="_blank">
              {{ T.frontPageFooterPrivacyPolicy }}
            </a>
          </li>
        </ul>
        <div>
          {{
            ui.formatString(T.frontPageFooterCopyright, {
              currentYear: new Date().getFullYear(),
            })
          }}
        </div>
      </div>
    </div>
  </footer>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator';
import * as ui from '../../ui';
import T from '../../lang';
import { reportAnIssueURL } from '../../errors';
import { getBlogUrl } from '../../urlHelper';

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

  get OmegaUpBlogURL(): string {
    // Use the key defined in blog-links-config.json
    return getBlogUrl('OmegaUpBlogURL');
  }

  get PrivacyPolicyURL(): string {
    return getBlogUrl('PrivacyPolicyURL');
  }

  get CodeofConductPolicyURL(): string {
    return getBlogUrl('CodeofConductPolicyURL');
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

@media (min-width: 1000px) {
  .slogan {
    max-width: 10rem;
  }
}

.column-title {
  font-size: 1.28rem;
  letter-spacing: 0.04rem;
  font-weight: 500;
}

.common-footer {
  background-color: $omegaup-primary--darker;
  color: $omegaup-white;

  .footer-navigation {
    .footer-brand {
      max-width: 200px;
      order: -3;

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
      @media only screen and (min-width: 992px) {
        display: block;

        &.footer-contact {
          order: 2;
        }
        &.footer-site {
          order: -1;
        }
        &.footer-sponsors {
          order: -2;
        }
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
              color: var(--footer-link-hover-color);
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
      color: var(--footer-link-hover-color);
    }
  }

  .copy {
    background-color: $omegaup-primary--darkest;
  }
}
</style>
