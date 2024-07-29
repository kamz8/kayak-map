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
        defaultTheme: 'light',
        themes: {
            light: {
                dark: false,
                colors: {
                    primary: '#4682B4',
                    secondary: '#87CEEB',
                    accent: '#FFD700',
                    error: '#FF0000',
                    warning: '#FFA500',
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
                    anchor: '#87CEEB',
                    footer: '#006400',
                },
            },
        },
    }
});

export default vuetify;
