// Settings Module Routes
const SettingsIndex = () => import('../Pages/SettingsView.vue')
const SettingsProfile = () => import('../Pages/ProfileView.vue')

const settingsRoutes = [
  {
    path: '/dashboard/settings',
    name: 'SettingsIndex',
    component: SettingsIndex,
    meta: {
      title: 'Ustawienia systemu - Kayak Map Dashboard',
      breadcrumbs: [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Ustawienia', to: '/dashboard/settings' }
      ],
      navigation: {
        section: 'Administracja',
        title: 'Ustawienia',
        icon: 'mdi-cog',
        order: 140,
        disabled: false
      },
      permissions: ['settings.view']
    }
  },
  {
    path: '/dashboard/settings/profile',
    name: 'SettingsProfile',
    component: SettingsProfile,
    meta: {
      title: 'Profil - Ustawienia - Dashboard',
      breadcrumbs: [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Ustawienia', to: '/dashboard/settings' },
        { text: 'Profil' }
      ]
    }
  },
]

export default settingsRoutes
