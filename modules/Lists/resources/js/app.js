import i18n from '~/Core/resources/js/i18n'
import SettingsBrands from './components/SettingsLists.vue'


if (window.Innoclapps) {
    Innoclapps.booting(function (Vue, router) {

        router.addRoute('settings', {
            path: '/settings/lists',
            component: SettingsBrands,
            meta: { title: i18n.t('lists::list.lists') },
        })
        router.addRoute('settings', {
            path: '/settings/brands/create',
            component: CreateBrand,
            name: 'create-brand',
        })
        router.addRoute('settings', {
            path: '/settings/brands/:id/edit',
            component: EditBrand,
            name: 'edit-brand',
        })
    })
}
