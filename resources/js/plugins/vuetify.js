import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import '@mdi/font/css/materialdesignicons.css'; // Import MDI icons CSS

const vuetify = createVuetify({
    components,
    directives,
    icons: {
        iconfont: 'mdi'
    },
    theme: {
        themes: {
            light: {
                primary: '#4682B4', // Steel Blue
                secondary: '#87CEEB', // Sky Blue
                accent: '#FFD700', // Gold
                error: '#FF0000', // Strong Red
                warning: '#FFA500', // Orange
                info: '#1E90FF', // Dodger Blue
                success: '#32CD32', // Lime Green
                background: '#FFFFFF', // White
                surface: '#F4A460', // Sandy Brown
                anchor: '#4682B4', // Link color (Steel Blue)
                footer: '#006400', // Dark Green
            },
            dark: {
                primary: '#4682B4', // Steel Blue
                secondary: '#87CEEB', // Sky Blue
                accent: '#FFD700', // Gold
                error: '#FF0000', // Strong Red
                warning: '#FFA500', // Orange
                info: '#1E90FF', // Dodger Blue
                success: '#32CD32', // Lime Green
                background: '#2C3E50', // Dark background
                surface: '#34495E', // Dark surface
                anchor: '#87CEEB', // Link color (Sky Blue)
                footer: '#006400', // Dark Green
            },
        },
    },
});

export default vuetify;
