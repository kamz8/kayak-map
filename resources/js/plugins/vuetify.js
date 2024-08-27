import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import '@mdi/font/css/materialdesignicons.css'; // Import MDI icons CSS

// Translations provided by Vuetify
import { pl, en } from 'vuetify/locale'

const vuetify = createVuetify({
    components,
    directives,
    locale: {
        locale: 'pl',
        fallback: 'en',
        messages: { pl, en },
    },
    icons: {
        iconfont: 'mdi'
    },
    theme: {
        defaultTheme: 'light',
        themes: {
            light: {
                dark: false,
                colors: {
                    primary: '#4682B4',
                    secondary: '#87CEEB',
                    accent: '#FFD700',
                    error: '#dc1212',
                    warning: '#D19A27',
                    info: '#1E90FF',
                    success: '#32CD32',
                    background: '#FFFFFF',
                    surface: '#ffffff',
                    'on-background': '#000000',
                    'on-surface': '#000000',
                    'on-primary': '#FFFFFF',
                    'on-secondary': '#000000',
                    'on-error': '#FFFFFF',
                    'on-warning': '#000000',
                    'on-info': '#FFFFFF',
                    'on-success': '#FFFFFF',
                    'river-path': '#a5fd1e',
                    'river-path-outline': '#2a4600',
                    'marker-active': '#1A6570',
                    'marker-highlighted': '#2EB3C4',
                    anchor: '#248996',
                    footer: '#006400',

                },
            },
            dark: {
                dark: true,
                colors: {
                    primary: '#3875a2',
                    secondary: '#305d6c',
                    accent: '#FFD700',
                    error: '#FF0000',
                    warning: '#FFA500',
                    info: '#1E90FF',
                    success: '#32CD32',
                    background: '#2C3E50',
                    surface: '#34495E',
                    'on-background': '#FFFFFF',
                    'on-surface': '#FFFFFF',
                    'on-primary': '#FFFFFF',
                    'on-secondary': '#FFFFFF',
                    'on-error': '#FFFFFF',
                    'on-warning': '#000000',
                    'on-info': '#FFFFFF',
                    'on-success': '#FFFFFF',
                    'marker-active': '#5FAAC7',      // Jaśniejszy pomarańczowy dla trybu ciemnego
                    'marker-highlighted': '#A8DAEF', // Jaśniejszy żółty dla trybu ciemnego
                    anchor: '#87CEEB',
                    footer: '#055005',
                },
            },
        },
    }
});

export default vuetify;
