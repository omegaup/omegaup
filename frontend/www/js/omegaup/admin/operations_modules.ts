export enum AvailableModules {
  Home = 'home',
  Settings = 'settings',
}

export enum AccessLevel {
  Support = 'support',
  Admin = 'admin',
}

export const MODULE_ACCESS_LEVELS: Record<AvailableModules, AccessLevel> = {
  [AvailableModules.Home]: AccessLevel.Support,
  // TODO: Settings should require AccessLevel.Admin again once the
  // permissions of the Operations Dashboard are redefined.
  [AvailableModules.Settings]: AccessLevel.Support,
};

// TODO: The backend should send the access level in the dashboard payload
// (together with the Maintenance Mode data) and this function should be
// removed.
export function getAccessLevel(isAdmin: boolean): AccessLevel {
  return isAdmin ? AccessLevel.Admin : AccessLevel.Support;
}

export function hasAccessLevel(
  userLevel: AccessLevel,
  requiredLevel: AccessLevel,
): boolean {
  return userLevel === AccessLevel.Admin || userLevel === requiredLevel;
}

export function getModuleFromHash(hash: string): AvailableModules {
  const hashModule = hash.substring(1).split('#')[0];
  return Object.values(AvailableModules).includes(
    hashModule as AvailableModules,
  )
    ? (hashModule as AvailableModules)
    : AvailableModules.Home;
}
