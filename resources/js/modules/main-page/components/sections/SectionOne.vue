<template>
  <section class="section-one">
    <!-- Canvas for animated waves -->
    <v-container>
      <v-row justify="center" class="text-center pt-md-5 pt-sm-3">
        <v-col cols="12" md="8">
          <h2 class="text-h3 font-weight-bold mb-6 text-white">Odkryj Najlepsze Szlaki Kajakowe</h2>
          <p class="text-h6 font-weight-regular text-white">
            Wybierz z ponad <span class="font-weight-bold">120 tras</span> w całej Polsce. Planuj wycieczki z przewodnikiem lub odkrywaj nowe szlaki samodzielnie.
          </p>
        </v-col>
      </v-row>

      <v-row>
        <v-col v-for="(feature, i) in features" :key="i" cols="12" md="4">
          <v-sheet elevation="1" rounded class="feature-card pa-6 text-white rounded-xl" :class="{'on-hover': hover}" @mouseenter="hover = true" @mouseleave="hover = false">
              <h3 class="text-h5 mb-4">{{ feature.title }}</h3>
              <p class="text-body-1">{{ feature.description }}</p>
              <v-btn
                  :color="feature.color"
                  variant="text"
                  class="mt-4"
                  @click="handleFeatureClick(feature)"
              >
                Dowiedz się więcej
                <v-icon end>mdi-arrow-right</v-icon>
              </v-btn>
          </v-sheet>
        </v-col>
      </v-row>

      <v-row justify="center" class="mt-12">
        <v-col cols="12" sm="6" md="4" class="text-center">
<!--          <base-btn-progress
              :progress="progress"
              color="white"
              size="x-large"
          >
            Rozpocznij Planowanie
          </base-btn-progress>-->
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>

<script>
import BaseBtnProgress from "@/components/BaseBtnProgress.vue";

export default {
  name: "SectionOne",
  components: { BaseBtnProgress },
  data() {
    return {
      progress: 0,
      hover: false,
      features: [
        {
          icon: 'mdi-map-search',
          title: 'Inteligentne Wyszukiwanie',
          description: 'Znajdź idealne trasy dopasowane do Twojego poziomu zaawansowania i preferencji.',
          color: 'var(--dodger-blue)'
        },
        {
          icon: 'mdi-compass',
          title: 'Szczegółowa Nawigacja',
          description: 'Dokładne mapy, punkty orientacyjne i wskazówki nawigacyjne na całej trasie.',
          color: 'var(--gold)'
        },
        {
          icon: 'mdi-weather-sunny',
          title: 'Warunki na Szlaku',
          description: 'Aktualne informacje o pogodzie, stanie wody i warunkach na trasie.',
          color: 'var(--lime-green)'
        }
      ]
    };
  },
  methods: {
    handleFeatureClick(feature) {
      console.log(`Clicked feature: ${feature.title}`);
    },

  },
};
</script>

<style scoped>
.section-one {
  position: relative;
  height: 70vh;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' version='1.1' xmlns:xlink='http://www.w3.org/1999/xlink' xmlns:svgjs='http://svgjs.dev/svgjs' width='1440' height='560' preserveAspectRatio='none' viewBox='0 0 1440 560'%3e%3cg mask='url(%26quot%3b%23SvgjsMask1071%26quot%3b)' fill='none'%3e%3crect width='1440' height='560' x='0' y='0' fill='rgba(21%2c 67%2c 96%2c 1)'%3e%3c/rect%3e%3cpath d='M0%2c352.122C78.422%2c350.623%2c168.289%2c406.423%2c230.453%2c358.591C293.659%2c309.957%2c264.049%2c207.473%2c287.181%2c131.151C306.346%2c67.918%2c361.625%2c14.716%2c354.589%2c-50.982C347.492%2c-117.249%2c285.025%2c-159.911%2c249.938%2c-216.573C206.642%2c-286.491%2c204.092%2c-403.476%2c124.209%2c-423.017C43.056%2c-442.869%2c-8.029%2c-323.86%2c-87.854%2c-299.203C-165.841%2c-275.114%2c-265.939%2c-337.445%2c-327.254%2c-283.567C-387.118%2c-230.965%2c-368.286%2c-132.619%2c-368.124%2c-52.928C-367.982%2c17.243%2c-347.387%2c82.099%2c-326.376%2c149.051C-302.3%2c225.772%2c-304.456%2c325.029%2c-236.183%2c367.509C-167.811%2c410.05%2c-80.511%2c353.661%2c0%2c352.122' fill='%23103248'%3e%3c/path%3e%3cpath d='M1440 1080.3220000000001C1543.013 1075.365 1647.868 1073.8899999999999 1737.705 1023.238 1830.525 970.904 1893.011 882.969 1949.867 792.848 2014.22 690.843 2093.839 587.218 2088.808 466.71500000000003 2083.628 342.654 2017.97 221.36599999999999 1922.683 141.752 1832.519 66.418 1702.464 83.17099999999999 1589.811 49.79199999999997 1476.792 16.30499999999995 1370.761-93.66300000000001 1259.493-54.75 1147.476-15.576000000000022 1121.059 130.00900000000001 1063.575 233.82600000000002 1017.784 316.524 982.9359999999999 399.58299999999997 957.5129999999999 490.629 928.363 595.023 873.511 700.745 904.168 804.706 936.0219999999999 912.725 1023.149 1001.2909999999999 1123.295 1052.8029999999999 1219.474 1102.275 1331.968 1085.52 1440 1080.3220000000001' fill='%231a5478'%3e%3c/path%3e%3c/g%3e%3cdefs%3e%3cmask id='SvgjsMask1071'%3e%3crect width='1440' height='560' fill='white'%3e%3c/rect%3e%3c/mask%3e%3c/defs%3e%3c/svg%3e");
  background-size: cover;
  background-position: center;
  overflow: hidden;
  z-index: 1;
}

.feature-card {
  height: 100%;
  transition: all 0.3s ease;
  text-align: center;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  z-index: 2;
}

.feature-card.on-hover {
  transform: translateY(-5px);
  background: rgba(255, 255, 255, 0.15);
}

.content {
  position: relative;
  z-index: 2;
  color: white;
  text-align: center;
  padding: 50px;
}

h1 {
  font-size: 2.5rem;
}

p {
  font-size: 1.2rem;
}
</style>
