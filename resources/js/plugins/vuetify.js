import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

// Definicja niestandardowego motywu
const vuetify = createVuetify({
    components,
    directives,
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
                background: '#F5DEB3', // Wheat
                surface: '#F4A460', // Sandy Brown
            },
            dark: {
                primary: '#4682B4', // Steel Blue
                secondary: '#87CEEB', // Sky Blue
                accent: '#FFD700', // Gold
                error: '#FF0000', // Strong Red
                warning: '#FFA500', // Orange
                info: '#1E90FF', // Dodger Blue
                success: '#32CD32', // Lime Green
                background: '#F5DEB3', // Wheat
                surface: '#F4A460', // Sandy Brown
            },
        },
    },
});

export default vuetify;
