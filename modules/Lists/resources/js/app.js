import i18n from '~/Core/resources/js/i18n'
import SettingsBrands from './components/SettingsLists.vue'
import CreateList from './views/CreateList.vue'
import EditList from './views/EditList.vue'


if (window.Innoclapps) {
    Innoclapps.booting(function (Vue, router) {

        router.addRoute('settings', {
            path: '/settings/lists',
            component: SettingsBrands,
            meta: { title: i18n.t('lists::list.lists') },
        })
        router.addRoute('settings', {
            path: '/settings/lists/create',
            component: CreateList,
            name: 'create-list',
        })
        router.addRoute('settings', {
            path: '/settings/lists/:id/edit',
            component: EditList,
            name: 'edit-list',
        })
    })
}
