/**
 * Centralized Permission Helpers
 */

export const ADMIN_ROLE_NAMES = ['admin', 'administrator'];

/**
 * Normalizes any value into a strict boolean enabled state.
 * Handles true, 1, '1', 'true', etc.
 */
export function permissionEnabled(value) {
  return (
    value === true ||
    value === 1 ||
    value === '1' ||
    value === 'true'
  );
}

/**
 * Converts a checkbox value into a clean boolean for payload storage.
 */
export function permissionValue(value) {
  return !!value;
}

/**
 * Safely extracts permission capability from perms object.
 */
export function getPermission(perms, feature, cap) {
  if (!perms || typeof perms !== 'object') return false;
  return permissionEnabled(perms[feature]?.[cap]);
}

/**
 * Safely sets permission capability on perms object.
 */
export function setPermission(perms, feature, cap, checked) {
  if (!perms || typeof perms !== 'object') return;
  if (!perms[feature] || typeof perms[feature] !== 'object') {
    perms[feature] = {};
  }
  perms[feature][cap] = permissionValue(checked);
}

/**
 * Helper to robustly determine if a role/staff combination is an Admin role.
 */
export function isUserAdminRole(roleObject, staffRoleSlug) {
  const slug = roleObject?.slug?.toLowerCase();
  const name = roleObject?.name?.toLowerCase();
  const staffRole = (staffRoleSlug || '').toLowerCase();

  return (
    ADMIN_ROLE_NAMES.includes(slug) ||
    ADMIN_ROLE_NAMES.includes(name) ||
    ADMIN_ROLE_NAMES.includes(staffRole)
  );
}

/**
 * Safely extracts human readable role name from user or role string/object.
 */
export function getRoleName(user) {
  if (!user) return 'Staff Member';
  
  if (typeof user.role === 'object' && user.role !== null && user.role.name) {
    return user.role.name;
  }
  
  if (user.role_data && typeof user.role_data === 'object' && user.role_data.name) {
    return user.role_data.name;
  }

  if (typeof user.role === 'string' && user.role.trim() !== '') {
    if (user.role.startsWith('{')) {
      try {
        const parsed = JSON.parse(user.role);
        if (parsed && parsed.name) return parsed.name;
      } catch (e) {}
    } else {
      return user.role.replace(/[-_]/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }
  }

  return 'Staff Member';
}
