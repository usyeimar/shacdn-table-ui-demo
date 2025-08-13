import { computed, ref } from 'vue'

interface Permission {
  name: string
  granted: boolean
}

interface UserPermissions {
  permissions: Permission[]
  roles: string[]
}

export function usePermissions() {
  const userPermissions = ref<UserPermissions>({
    permissions: [],
    roles: []
  })

  const hasPermission = (permissionName: string): boolean => {
    const permission = userPermissions.value.permissions.find(
      p => p.name === permissionName
    )
    return permission?.granted ?? false
  }

  const hasRole = (roleName: string): boolean => {
    return userPermissions.value.roles.includes(roleName)
  }

  const hasAnyPermission = (permissionNames: string[]): boolean => {
    return permissionNames.some(permission => hasPermission(permission))
  }

  const hasAllPermissions = (permissionNames: string[]): boolean => {
    return permissionNames.every(permission => hasPermission(permission))
  }

  const setPermissions = (permissions: Permission[]) => {
    userPermissions.value.permissions = permissions
  }

  const setRoles = (roles: string[]) => {
    userPermissions.value.roles = roles
  }

  const clearPermissions = () => {
    userPermissions.value = {
      permissions: [],
      roles: []
    }
  }

  return {
    userPermissions: computed(() => userPermissions.value),
    hasPermission,
    hasRole,
    hasAnyPermission,
    hasAllPermissions,
    setPermissions,
    setRoles,
    clearPermissions
  }
} 