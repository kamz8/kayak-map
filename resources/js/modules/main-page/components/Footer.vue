<template>
  <v-footer app color="footer" class="pa-0">
    <v-container class="pa-8 mx-auto" max-width="1240px">
      <v-row justify="start" class="mb-8">
        <v-col cols="12" class="text-center">
          <figure class="d-flex align-center text-decoration-none" style="width: auto">
            <v-img src="/storage/assets/logo.svg" alt="Logo" height="80" width="80" max-width="80px"
                   class="mb-2 invert-img" contain></v-img>
            <div class="text-h4 font-weight-bold logo-text text-white">WARTKI NURT</div>
          </figure>
        </v-col>
      </v-row>

      <v-row>
        <v-col cols="12" sm="6" md="3" v-for="(section, index) in footerSections" :key="index">
          <h3 class="text-h6 font-weight-bold text-white mb-4">{{ section.title }}</h3>
          <v-list nav dense color="transparent" variant="plain" lines="two" class="text-white p-0" tag="nav">
            <v-list-item v-for="(item, i) in section.items"
                         :key="i" :to="item.to"
                         link
                         class="pa-0"
                         :hover="false"
                         :ripple="false">
              <v-list-item-title class="text-white text-subtitle-1">{{ item.title }}</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-col>
      </v-row>

      <v-row class="mt-12">
        <v-col cols="12" md="6">
          <h3 class="text-h6 font-weight-bold white--text mb-4">Aplikacja dla miłośników kajakarstwa</h3>
          <div class="d-flex">
<!--            <v-btn variant="text" href="#" target="_blank" class="pa-0 mr-4">
              <v-img src="/storage/assets/app-store-badge.svg" alt="Download on App Store" min-width="135px" min-height="40px" contain></v-img>
            </v-btn>-->
            <v-btn
                variant="text"
                href="#"
                target="_blank"
                class="pa-0"
                height="40"
            >
              <v-img
                  src="/storage/assets/google-play-badge.svg"
                  alt="Get it on Google Play"
                  width="135"
                  height="40"
                  contain
              ></v-img>
            </v-btn>
          </div>
        </v-col>
        <v-col cols="12" md="6" class="d-flex justify-end align-end">
          <v-btn v-for="icon in socialIcons" :key="icon.name" icon class="mx-1" color="white">
            <v-icon>{{ icon.icon }}</v-icon>
          </v-btn>
        </v-col>
      </v-row>

      <v-divider color="white" class="my-4"></v-divider>

      <v-row>
        <v-col cols="12" class="text-center">
          <span class="white--text">© 2024 WartkiNurt. Wszelkie prawa zastrzeżone.</span>
        </v-col>
      </v-row>
    </v-container>
  </v-footer>
</template>

<script>
export default {
  name: 'Footer',
  data: () => ({
    footerSections: [
      {
        title: 'O nas',
        items: [
          {title: 'O projekcie', to: '/about'},
          {title: 'Zespół', to: '/team'},
          {title: 'Kontakt', to: '/contact'},
        ]
      },
      {
        title: 'Informacje',
        items: [
          {title: 'Polityka prywatności', to: '/privacy-policy'},
          {title: 'Regulamin', to: '/terms'},
          {title: 'Cookies', to: '/cookies'},
        ]
      },
      {
        title: 'Wsparcie',
        items: [
          {title: 'Wsparcie techniczne', to: '/support'},
          {title: 'Zgłoś problem', to: '/report'},
          {title: 'FAQ', to: '/faq'},
        ]
      },
      {
        title: 'Aplikacja',
        items: [
          {title: 'Odkrywaj', to: '/explore'},
          {title: 'Regiony', to: '/regions'},
        ]
      }
    ],
/*    socialIcons: [
      {name: 'Facebook', icon: 'mdi-facebook', href: '#'},
      {name: 'Instagram', icon: 'mdi-instagram',href: '#'},
      {name: 'YouTube', icon: 'mdi-youtube',href: '#'},
      {name: 'Twitter', icon: 'mdi-twitter',href: '#'},
      {name: 'LinkedIn', icon: 'mdi-linkedin',href: '#'},
    ],*/
  }),
}
</script>

<style scoped>
.invert-img {
  filter: contrast(90%) invert(100%);
}

.v-list-item {
  min-height: 32px;
}

.v-btn--icon.v-size--default {
  height: 36px;
  width: 36px;
}

.v-list {
  background-color: transparent !important;
}

.v-list-item:hover {
  background-color: transparent !important;
}

.v-list-item:hover .v-list-item__title .v-list-item-title {
  text-decoration: underline;
}

.v-list-item {
  cursor: pointer;
}

.v-list-item::before {
  display: none;
}

.v-list-item:hover {
  background-color: transparent !important;
}

</style>
