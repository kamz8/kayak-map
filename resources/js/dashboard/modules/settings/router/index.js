// Settings module routes
export default [
  {
    path: '/dashboard/settings',
    name: 'DashboardSettings',
    component: () => import('../Pages/SettingsView.vue'),
    meta: {
      requiresAuth: true,
      title: 'Ustawienia - Dashboard',
      breadcrumbs: [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Ustawienia' }
      ],
      // Navigation metadata for dropdown
      navigation: {
        section: 'System',
        icon: 'mdi-cog',
        title: 'Ustawienia',
        order: 1
      }
    }
  },
  {
    path: '/dashboard/settings/profile',
    name: 'DashboardSettingsProfile',
    component: () => import('../Pages/ProfileView.vue'),
    meta: {
      requiresAuth: true,
      title: 'Profil - Ustawienia - Dashboard',
      breadcrumbs: [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Ustawienia', to: '/dashboard/settings' },
        { text: 'Profil' }
      ]
    }
  }
]