import './bootstrap';
import Alpine from 'alpinejs';
import { createPosApp } from './pos';

window.createPosApp = createPosApp;
window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('posApp', () => {
        const configEl = document.getElementById('pos-config');
        if (! configEl) {
            console.error('POS config missing (#pos-config)');
            return {
                error: 'إعدادات نقطة البيع غير موجودة — أعد تحميل الصفحة',
                order: null,
                categories: [],
                allItems: [],
                filteredItems: [],
                filteredCategories: [],
                itemsCount: 0,
                cartOpen: false,
                money: (v) => String(v ?? 0),
            };
        }

        try {
            return createPosApp(JSON.parse(configEl.textContent));
        } catch (e) {
            console.error('POS init failed', e);
            return {
                error: 'فشل تشغيل نقطة البيع: ' + e.message,
                order: null,
                categories: [],
                allItems: [],
                filteredItems: [],
                filteredCategories: [],
                itemsCount: 0,
                cartOpen: false,
                money: (v) => String(v ?? 0),
            };
        }
    });
});

Alpine.start();
