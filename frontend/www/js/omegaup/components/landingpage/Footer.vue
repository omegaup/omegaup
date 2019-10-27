<template>
  <div class="footer">
    <div class="footer-navigation">
      <div class="brand">
        <!-- <img class="brand-logo" src="../assets/logo-main-white.svg" /> -->
        <div class="slogan">
          INCREASING THE NUMBER OF TALENTED SOFTWARE ENGINEERS IN LATIN AMERICA
        </div>
      </div>
      <div class="footer-list-section">
        <h3 class="section-title">SITIO</h3>
        <ul>
          <li>Arena</li>
          <li>Problemas</li>
          <li>Rank</li>
          <li>Escuelas</li>
          <li>Blog</li>
          <li>Preguntas</li>
        </ul>
      </div>
      <div class="footer-list-section">
        <h3 class="section-title">ORGANIZACION</h3>
        <ul>
          <li>Acerca de Nosotros</li>
          <li>Equipo</li>
        </ul>
      </div>
      <div class="footer-list-section">
        <h3 class="section-title">DESARROLLADORES</h3>
        <ul>
          <li>Programa de Voluntarios</li>
          <li>Github</li>
        </ul>
      </div>
      <div class="footer-list-section">
        <h3 class="section-title">CONTACTO</h3>
        <ul>
          <li>hello@omegaup.com</li>
        </ul>
        <div class="social-icons">
          GITHUB / FACEBOOK
        </div>
      </div>
    </div>
    <div class="copy">
      <div class="copy-content">
        <div class="">
          Copyright © 2019 OmegaUp. All rights reserved.​
        </div>
        <div class="menu">
          <ul>
            <li>
              <a>Privacy</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';

@Component
export default class Footer extends Vue {}

</script>

<style lang="scss" scoped>
$omegaup-white: white ;
$omegaup-primary--darker: #466aaf;
$omegaup-primary--darkest: #3F63AA;
$omegaup-primary--accent: #0275d8;
$omegaup-unit-size: 8px;
$omegaup-unit-1: 8px;
$omegaup-unit-2: 16px;
$omegaup-unit-3: 24px;
$omegaup-unit-4: 32px;
$omegaup-unit-5: 40px;
$omegaup-unit-6: 48px;
$omegaup-unit-7: 56px;
$omegaup-unit-8: 64px;
$omegaup-unit-9: 72px;
$omegaup-unit-10: 80px;

.footer {
  background-color: $omegaup-primary--darker;
  color: $omegaup-white;
  font-weight: 500;

  .footer-navigation {
    width: 80%;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    padding-top: 20px;
    padding-bottom: 20px;

    @media only screen and (max-width: 767px) {
      display: block;
    }
    .brand {
      max-width: 200px;

      @media only screen and (max-width: 767px) {
        max-width: 100%;
      }

      .brand-logo {
        width: 120px;
        margin-top: -40px;
      }

      .slogan {
        margin-top: -40px;
      }
    }

    .footer-list-section {
      @media only screen and (max-width: 767px) {
        width: 50%;
        display: inline-grid;
      }

      ul {
        list-style-type: none;
        padding: 0;

        li {
          margin-top: 8px;
          padding: 0;
        }
      }
    }
  }

  .copy {
    background-color: $omegaup-primary--darkest;

    .copy-content {
      width: 80%;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      padding-top: 20px;
      padding-bottom: 20px;

      .menu {
        ul {
          margin: 0;
          li {
            list-style-type: none;
          }
        }
      }
    }
  }
}
</style>
