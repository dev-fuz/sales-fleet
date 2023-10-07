/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.1
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2023 KONKORD DIGITAL
 */
import i18n from '~/Core/i18n'
import RolesCreate from '~/Core/views/Roles/RolesCreate.vue'
import RolesEdit from '~/Core/views/Roles/RolesEdit.vue'
import RolesIndex from '~/Core/views/Roles/RolesIndex.vue'

import SettingsManageUsers from './components/SettingsManageUsers.vue'
import UsersCreate from './views/UsersCreate.vue'
import UsersEdit from './views/UsersEdit.vue'
import UsersInvite from './views/UsersInvite.vue'
import UsersManageTeams from './views/UsersManageTeams.vue'

export default [
  {
    path: 'users',
    component: SettingsManageUsers,
    name: 'users-index',
    meta: { title: i18n.t('users::user.users') },
    children: [
      {
        path: 'create',
        name: 'create-user',
        components: {
          createEdit: UsersCreate,
        },
        meta: { title: i18n.t('users::user.create') },
      },
      {
        path: ':id/edit',
        name: 'edit-user',
        components: {
          createEdit: UsersEdit,
        },
        meta: { title: i18n.t('users::user.edit') },
      },
      {
        path: 'invite',
        name: 'invite-user',
        components: {
          invite: UsersInvite,
        },
        meta: { title: i18n.t('users::user.invite') },
      },
      {
        path: 'roles',
        name: 'role-index',
        components: {
          roles: RolesIndex,
        },
        meta: {
          title: i18n.t('core::role.roles'),
        },
        children: [
          {
            path: 'create',
            name: 'create-role',
            component: RolesCreate,
            meta: { title: i18n.t('core::role.create') },
          },
          {
            path: ':id/edit',
            name: 'edit-role',
            component: RolesEdit,
            meta: { title: i18n.t('core::role.edit') },
          },
        ],
      },
      {
        path: 'teams',
        name: 'manage-teams',
        components: {
          teams: UsersManageTeams,
        },
        meta: {
          title: i18n.t('users::team.teams'),
        },
      },
    ],
  },
]
