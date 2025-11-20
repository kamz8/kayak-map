/**
 * Trail Status Composable
 *
 * Provides status configuration and helper methods for trail status management
 */

export const STATUS_CONFIG = {
  active: {
    variant: 'success',
    color: 'success',
    label: 'Aktywny',
    description: 'Publiczny',
    icon: 'mdi-check-decagram'
  },
  hidden: {
    variant: 'default',
    color: 'primary',
    label: 'Ukryty',
    description: 'Tylko z linkiem',
    icon: 'mdi-eye-off-outline'
  },
  draft: {
    variant: 'secondary',
    color: 'secondary',
    label: 'Szkic',
    description: 'Prywatny edycja',
    icon: 'mdi-pencil-circle'
  },
  archived: {
    variant: 'destructive',
    color: 'error',
    label: 'Archiwalny',
    description: 'Nieaktualna trasa',
    icon: 'mdi-archive'
  }
}

export function useTrailStatus() {
  /**
   * Get status configuration
   */
  const getStatusConfig = (status) => {
    return STATUS_CONFIG[status] || {
      variant: 'secondary',
      label: status,
      description: 'Nieznany status',
      icon: 'mdi-help-circle-outline'
    }
  }

  /**
   * Get status variant (for UiBadge variant prop)
   */
  const getStatusVariant = (status) => {
    return getStatusConfig(status).variant
  }

  /**
   * Get status color (for v-icon color prop)
   */
  const getStatusColor = (status) => {
    return getStatusConfig(status).color
  }

  /**
   * Get status label
   */
  const getStatusLabel = (status) => {
    return getStatusConfig(status).label
  }

  /**
   * Get status description
   */
  const getStatusDescription = (status) => {
    return getStatusConfig(status).description
  }

  /**
   * Get status icon
   */
  const getStatusIcon = (status) => {
    return getStatusConfig(status).icon
  }

  /**
   * Get all status options for v-select
   */
  const getStatusOptions = () => {
    return Object.keys(STATUS_CONFIG).map(key => ({
      value: key,
      title: STATUS_CONFIG[key].label
    }))
  }

  return {
    STATUS_CONFIG,
    getStatusConfig,
    getStatusVariant,
    getStatusColor,
    getStatusLabel,
    getStatusDescription,
    getStatusIcon,
    getStatusOptions
  }
}
