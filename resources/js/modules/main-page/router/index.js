import NotFound from "@/modules/main-page/pages/NotFound.vue";

const ExamplePage = () => import("@/modules/main-page/pages/ExamplePage.vue");

const Home = () => import('../../main-page/pages/Home.vue')

export default [
    {
        path: '/',
        name: 'home',
        component: Home,
        meta: {
            layout: 'MainLayout',
            title: 'WARTKI NURT - Znajdź najlepsze trasy kajakowe',
            metaTags: [
                {
                    name: 'description',
                    content: 'Odkryj najpiękniejsze szlaki kajakowe w Polsce dzięki aplikacji WARTKI NURT. Znajdź trasy dopasowane do Twoich umiejętności i zaplanuj niezapomnianą przygodę!'
                },
                {
                    property: 'og:description',
                    content: 'Odkryj najpiękniejsze szlaki kajakowe w Polsce dzięki aplikacji WARTKI NURT. Znajdź trasy dopasowane do Twoich umiejętności i zaplanuj niezapomnianą przygodę!'
                },
                {
                    property: 'og:image',
                    content: '/storage/assets/logo.svg'
                },
                {
                    property: 'og:title',
                    content: 'WARTKI NURT - Znajdź najlepsze trasy kajakowe'
                }
            ]
        }
    },
    {
        path: '/about',
        name: 'about',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'O aplikacji WARTKI NURT - Twój przewodnik po szlakach kajakowych',
            metaTags: [
                {
                    name: 'description',
                    content: 'Dowiedz się więcej o aplikacji WARTKI NURT, która pomaga odkrywać najpiękniejsze trasy kajakowe w Polsce. Nasza misja to promocja turystyki kajakowej i ułatwianie planowania spływów.'
                },
                {
                    property: 'og:description',
                    content: 'Dowiedz się więcej o aplikacji WARTKI NURT, która pomaga odkrywać najpiękniejsze trasy kajakowe w Polsce. Nasza misja to promocja turystyki kajakowej i ułatwianie planowania spływów.'
                },
                {
                    property: 'og:image',
                    content: '/storage/assets/logo.svg'
                },
                {
                    property: 'og:title',
                    content: 'O aplikacji WARTKI NURT - Twój przewodnik po szlakach kajakowych'
                }
            ]
        }
    },
    {
        path: '/team',
        name: 'team',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'Zespół WARTKI NURT - Pasjonaci kajakarstwa',
            metaTags: [
                {
                    name: 'description',
                    content: 'Poznaj zespół pasjonatów stojący za aplikacją WARTKI NURT. Łączy nas miłość do kajakarstwa i chęć dzielenia się wiedzą o najlepszych trasach.'
                },
                {
                    property: 'og:description',
                    content: 'Poznaj zespół pasjonatów stojący za aplikacją WARTKI NURT. Łączy nas miłość do kajakarstwa i chęć dzielenia się wiedzą o najlepszych trasach.'
                },
                {
                    property: 'og:image',
                    content: '/storage/assets/logo.svg'
                },
                {
                    property: 'og:title',
                    content: 'Zespół WARTKI NURT - Pasjonaci kajakarstwa'
                }
            ]
        }
    },
    {
        path: '/contact',
        name: 'contact',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'Kontakt z zespołem WARTKI NURT',
            metaTags: [
                {
                    name: 'description',
                    content: 'Masz pytanie lub sugestię dotyczącą aplikacji WARTKI NURT? Skontaktuj się z nami, chętnie poznamy Twoją opinię!'
                },
                {
                    property: 'og:description',
                    content: 'Masz pytanie lub sugestię dotyczącą aplikacji WARTKI NURT? Skontaktuj się z nami, chętnie poznamy Twoją opinię!'
                },
                {
                    property: 'og:image',
                    content: '/storage/assets/logo.svg'
                },
                {
                    property: 'og:title',
                    content: 'Kontakt z zespołem WARTKI NURT'
                }
            ]
        }
    },
    {
        path: '/privacy-policy',
        name: 'privacy',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'Polityka prywatności aplikacji WARTKI NURT',
            metaTags: [
                {
                    name: 'description',
                    content: 'Zapoznaj się z polityką prywatności aplikacji WARTKI NURT. Dbamy o bezpieczeństwo Twoich danych i szanujemy Twoją prywatność.'
                },
                {
                    property: 'og:description',
                    content: 'Zapoznaj się z polityką prywatności aplikacji WARTKI NURT. Dbamy o bezpieczeństwo Twoich danych i szanujemy Twoją prywatność.'
                },
                {
                    property: 'og:image',
                    content: '/storage/assets/logo.svg'
                },
                {
                    property: 'og:title',
                    content: 'Polityka prywatności aplikacji WARTKI NURT'
                }
            ]
        }
    },
    {
        path: '/terms',
        name: 'terms',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'Regulamin aplikacji WARTKI NURT',
            metaTags: [
                {
                    name: 'description',
                    content: 'Przeczytaj regulamin korzystania z aplikacji WARTKI NURT. Dowiedz się jakie są zasady i warunki używania naszej aplikacji.'
                },
                {
                    property: 'og:description',
                    content: 'Przeczytaj regulamin korzystania z aplikacji WARTKI NURT. Dowiedz się jakie są zasady i warunki używania naszej aplikacji.'
                },
                {
                    property: 'og:image',
                    content: '/storage/assets/logo.svg'
                },
                {
                    property: 'og:title',
                    content: 'Regulamin aplikacji WARTKI NURT'
                }
            ]
        }
    },
    {
        path: '/cookies',
        name: 'cookies',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'Polityka cookies w aplikacji WARTKI NURT',
            metaTags: [
                {
                    name: 'description',
                    content: 'Sprawdź w jaki sposób aplikacja WARTKI NURT wykorzystuje pliki cookies. Dowiedz się do czego służą i jak nimi zarządzać.'
                },
                {
                    property: 'og:description',
                    content: 'Sprawdź w jaki sposób aplikacja WARTKI NURT wykorzystuje pliki cookies. Dowiedz się do czego służą i jak nimi zarządzać.'
                },
                {
                    property: 'og:image',
                    content: '/storage/assets/logo.svg'
                },
                {
                    property: 'og:title',
                    content: 'Polityka cookies w aplikacji WARTKI NURT'
                }
            ]
        }
    },
    {
        path: '/support',
        name: 'support',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'Wsparcie aplikacji WARTKI NURT',
            metaTags: [
                {
                    name: 'description',
                    content: 'Potrzebujesz pomocy z aplikacją WARTKI NURT? Znajdziesz tu odpowiedzi na najczęstsze pytania i możliwość kontaktu z naszym zespołem.'
                },
                {
                    property: 'og:description',
                    content: 'Potrzebujesz pomocy z aplikacją WARTKI NURT? Znajdziesz tu odpowiedzi na najczęstsze pytania i możliwość kontaktu z naszym zespołem.'
                },
                {
                    property: 'og:image',
                    content: '/storage/assets/logo.svg'
                },
                {
                    property: 'og:title',
                    content: 'Wsparcie aplikacji WARTKI NURT'
                }
            ]
        }
    },
    {
        path: '/report',
        name: 'report',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'Zgłoś problem z aplikacją WARTKI NURT',
            metaTags: [
                {
                    name: 'description',
                    content: 'Napotkałeś problem podczas korzystania z aplikacji WARTKI NURT? Zgłoś go nam, a postaramy się jak najszybciej go rozwiązać.'
                },
                {
                    property: 'og:description',
                    content: 'Napotkałeś problem podczas korzystania z aplikacji WARTKI NURT? Zgłoś go nam, a postaramy się jak najszybciej go rozwiązać.'
                },
                {
                    property: 'og:image',
                    content: '/storage/assets/logo.svg'
                },
                {
                    property: 'og:title',
                    content: 'Zgłoś problem z aplikacją WARTKI NURT'
                }
            ]
        }
    },
    {
        path: '/faq',
        name: 'faq',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'Najczęstsze pytania o aplikację WARTKI NURT',
            metaTags: [
                {
                    name: 'description',
                    content: 'Masz pytania dotyczące aplikacji WARTKI NURT? Sprawdź odpowiedzi na najczęściej zadawane pytania w naszej sekcji FAQ.'
                },
                {
                    property: 'og:description',
                    content: 'Masz pytania dotyczące aplikacji WARTKI NURT? Sprawdź odpowiedzi na najczęściej zadawane pytania w naszej sekcji FAQ.'
                },
                {
                    property: 'og:image',
                    content: '/storage/assets/logo.svg'
                },
                {
                    property: 'og:title',
                    content: 'Najczęstsze pytania o aplikację WARTKI NURT'
                }
            ]
        }
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFound,
        meta: {
            layout: 'MainLayout',
            title: '404 - Nie znaleziono strony',
            metaTags: [
                {
                    name: 'description',
                    content: 'Nie znaleziono strony o podanym adresie. Sprawdź poprawność wpisanego adresu URL lub wróć na stronę główną aplikacji WARTKI NURT.'
                },
                {
                    property: 'og:description',
                    content: 'Nie znaleziono strony o podanym adresie. Sprawdź poprawność wpisanego adresu URL lub wróć na stronę główną aplikacji WARTKI NURT.'
                },
                {
                    property: 'og:image',
                    content: '/storage/assets/logo.svg'
                },
                {
                    property: 'og:title',
                    content: '404 - Nie znaleziono strony'
                }
            ]
        }
    }
];
