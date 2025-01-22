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
                    'river-blue': '#263e4a',
                    'river-blue-light': '#385c6d',
                    'river-surface': '#5c8299',
                    'sand': '#eccca2',
                    anchor: '#248996',
                    footer: '#1c3c1c',
                    'sandy-brown': '#F4A460',
                    wheat:'#F5DEB3'

                },
                variables: {
                    fontFamily: 'Popins,  sans-serif', // Własna czcionka
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
        typography: {
            fontFamily: '"Poppins", "Inter", "Nunito", sans-serif'
        }
    },
    defaults: {
        VBtn: {
            fontFamily: 'Nunito'
        },
        VTextField: {
            fontFamily: 'Inter'
        }
    },
    // Nadpisanie domyślnych fontów dla całej aplikacji
    blueprint: {
        typography: {
            fontFamily: 'Inter, Poppins, Nunito, sans-serif',
            'h1': { fontFamily: 'Poppins, sans-serif' },
            'h2': { fontFamily: 'Poppins, sans-serif' },
            'h3': { fontFamily: 'Poppins, sans-serif' },
            'h4': { fontFamily: 'Poppins, sans-serif' },
            'h5': { fontFamily: 'Poppins, sans-serif' },
            'h6': { fontFamily: 'Poppins, sans-serif' },

            // Large Headers
            'h1-lg': { fontFamily: 'Poppins, sans-serif' },
            'h2-lg': { fontFamily: 'Poppins, sans-serif' },
            'h3-lg': { fontFamily: 'Poppins, sans-serif' },
            'h4-lg': { fontFamily: 'Poppins, sans-serif' },
            'h5-lg': { fontFamily: 'Poppins, sans-serif' },
            'h6-lg': { fontFamily: 'Poppins, sans-serif' },

            // Body & Subtitles
            'subtitle-1': { fontFamily: 'Inter, sans-serif' },
            'subtitle-2': { fontFamily: 'Inter, sans-serif' },
            'body-1': { fontFamily: 'Inter, sans-serif' },
            'body-2': { fontFamily: 'Inter, sans-serif' },

            // UI Elements
            'button': { fontFamily: 'Nunito, sans-serif' },
            'caption': { fontFamily: 'Inter, sans-serif' },
            'overline': { fontFamily: 'Inter, sans-serif' }
        }
    }
});

export default vuetify;
