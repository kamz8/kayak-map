import appConfigFile from "@/config/appConfig.js";
import unitMixin from "@/mixins/UnitMixin.js";
import mapMixin from "@/mixins/MapMixin.js";
export default {
    data () {
      return{
          pageLoading: false
      }
    },
    computed: {
        appConfig() {
            return appConfigFile;
        },
        placeholderImage() {
            return appConfigFile.placeholderImage
        }
    },
    methods: {
    ...unitMixin.methods,
    openGoogleMapsNavigation(lat, lang) {
        console.log("dupa openGoogleMapsNavigation")
        if (!this.region.center?.coordinates) return;

        // Google Maps przyjmuje koordynaty w formacie "lat,lng"
        const destination = `${lat},${lang}`;

        // Tworzymy URL do Google Maps z nawigacją
        const googleMapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${destination}&travelmode=driving`;

        // Otwieramy w nowej karcie
        window.open(googleMapsUrl, '_blank');
    },
        getDifficultyColor(difficulty) {
            switch (difficulty) {
                case 'łatwy': return 'green';
                case 'umiarkowany': return 'orange';
                case 'trudny': return 'red';
                default: return 'grey';
            }
        },
    }
};
