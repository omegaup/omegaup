<template>
  <footer class="common-footer text-center mt-5">
    <div class="container-xl">
      <div class="footer-navigation d-lg-flex align-items-start py-5 m-auto">
        <div class="footer-brand mb-4 mb-lg-0 max-width-logo text-center">
          <img
            class="footer-logo d-block mx-auto mb-1 mt-n6"
            width="120"
            src="/media/logo-main-white.svg"
          />
          <div class="slogan mx-auto mt-2">
            {{ T.frontPageFooter }}
          </div>
        </div>
        <div
          class="footer-list-section footer-contact mb-4 mb-lg-0 mx-auto text-center"
        >
          <h4 class="column-title">{{ T.frontPageFooterContact }}</h4>
          <ul class="list-unstyled">
            <li class="mt-2">
              <a href="mailto:hello@omegaup.com" class="font-weight-bold">hello@omegaup.com</a>
            </li>
          </ul>
          <div
            class="social-icons mt-3 d-flex flex-row justify-content-center flex-wrap"
          >
            <a
              class="mx-2"
              href="https://www.facebook.com/omegaup/"
              target="_blank"
              title="Facebook"
            >
              <font-awesome-icon :icon="['fab', 'facebook']" size="lg" />
            </a>
            <a
              class="mx-2"
              href="https://discord.gg/K3JFd9d3wk"
              target="_blank"
              title="Discord"
            >
              <font-awesome-icon :icon="['fab', 'discord']" size="lg" />
            </a>
          </div>
        </div>
        <div class="footer-list-section footer-site mb-4 mb-lg-0 mx-auto text-center">
          <h4 class="column-title">{{ T.frontPageFooterSite }}</h4>
          <ul class="list-unstyled">
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
          class="footer-list-section footer-sponsors mb-4 mb-lg-0 mx-auto text-center"
        >
          <h4 class="column-title">{{ T.frontPageFooterSponsors }}</h4>
          <ul class="list-unstyled">
            <li class="mt-4 d-flex justify-content-center">
              <a
                href="https://news.airbnb.com/2025-community-fund/"
                target="_blank"
                class="sponsor-link"
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
          class="footer-list-section footer-organization mb-4 mb-lg-0 mx-auto text-center"
        >
          <h4 class="column-title">{{ T.frontPageFooterOrganization }}</h4>
          <ul class="list-unstyled">
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
          class="footer-list-section footer-developers d-inline-block mb-4 mb-lg-0 mx-auto text-center"
        >
          <h4 class="column-title">{{ T.frontPageDevelopers }}</h4>
          <ul class="list-unstyled">
            <li class="mt-1">
              <a
                href="https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Development-Environment-Setup-Process.md"
                target="_blank"
                >{{ T.frontPageFooterHelpUs }}</a
              >
            </li>
            <li class="mt-1">
              <a href="https://github.com/omegaup/omegaup" target="_blank">
                <font-awesome-icon :icon="['fab', 'github']" class="mr-1" />
                Github
              </a>
            </li>
            <li class="mt-1">
              <a
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
    <div class="copy border-top border-white-50 mt-3">
      <div
        class="container-xl d-md-flex justify-content-between align-items-center py-3"
      >
        <ul
          class="mb-2 m-md-0 list-unstyled d-flex justify-content-around d-md-inline-flex order-md-12"
        >
          <li class="px-3">
            <a :href="CodeofConductPolicyURL" target="_blank">
              {{ T.frontPageFooterCodeConduct }}
            </a>
          </li>
          <li class="px-3 border-left border-white-50">
            <a :href="PrivacyPolicyURL" target="_blank">
              {{ T.frontPageFooterPrivacyPolicy }}
            </a>
          </li>
        </ul>
        <div class="copyright-text opacity-75 small">
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

.common-footer {
  background-color: $omegaup-vibrant-blue;
  color: white;
  flex-grow: 1;

  .footer-navigation {
    .footer-brand {
      max-width: 200px;
      order: -3;

      @media only screen and (max-width: 991px) {
        max-width: 100%;
        margin-bottom: 3rem;
      }

      .footer-logo {
        margin-top: -1.5rem;
      }

      .slogan {
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.05em;
        opacity: 0.9;
        line-height: 1.4;
      }
    }

    .footer-list-section {
      @media only screen and (min-width: 992px) {
        display: block;

        &.footer-contact { order: 2; }
        &.footer-site { order: -1; }
        &.footer-sponsors { order: -2; }
      }

      .column-title {
        font-size: 1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 1.5rem;
        opacity: 0.95;
      }

      ul {
        padding: 0;
        margin: 0;
        text-align: center;

        li {
          margin-top: 10px;
          
          a {
            text-decoration: none;
            color: rgba(255, 255, 255, 0.85);
            transition: color 0.2s ease;
            font-size: 0.9rem;

            &:hover {
              color: white;
            }
          }
        }
      }

      .sponsor-logo {
        filter: brightness(0) invert(1);
        opacity: 0.9;
        transition: opacity 0.3s ease;
        &:hover {
          opacity: 1;
        }
      }
      
      .social-icons {
        a {
          color: rgba(255, 255, 255, 0.9);
          transition: all 0.3s ease;
          &:hover {
            color: white;
            transform: translateY(-2px);
          }
        }
      }
    }
  }

  .copy {
    background-color: transparent;
    border-top: 1px solid rgba(255, 255, 255, 0.15);
    
    a {
      color: rgba(255, 255, 255, 0.8);
      font-size: 0.85rem;
      &:hover {
        color: white;
      }
    }
    
    .copyright-text {
      color: rgba(255, 255, 255, 0.7);
    }
  }

  .border-white-50 {
    border-color: rgba(255, 255, 255, 0.2) !important;
  }
}

@media (min-width: 1000px) {
  .slogan {
    max-width: 10rem;
  }
}


</style>
