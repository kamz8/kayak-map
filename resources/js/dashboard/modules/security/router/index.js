// Security module routes
export default [
  {
    path: '/dashboard/security/change-password',
    name: 'DashboardSecurityChangePassword',
    component: () => import('../Pages/ChangePasswordView.vue'),
    meta: {
      requiresAuth: true,
      title: 'Zmiana hasła - Bezpieczeństwo - Dashboard',
      breadcrumbs: [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Bezpieczeństwo', to: '/dashboard/security' },
        { text: 'Zmiana hasła' }
      ],
      // Navigation metadata for sidebar
      /*navigation: {
        section: 'System',
        icon: 'mdi-shield-lock',
        title: 'Bezpieczeństwo',
        order: 2
      }*/
    }
  }
]
