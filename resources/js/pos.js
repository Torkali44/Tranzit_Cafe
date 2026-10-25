export function createPosApp({
    categories,
    allItems,
    initialOrder,
    routes,
    csrf,
    servicePercent,
    currency,
    availableTables = [],
    tablesUrl = '/pos/tables',
    pendingTableId = null,
    pendingTableName = null,
}) {
    return {
        categories,
        allItems,
        servicePercent,
        currency,
        routes,
        csrf,
        availableTables,
        tablesUrl,
        pendingTableId,
        pendingTableName,
        categoryId: 'all',
        search: '',
        categorySearch: '',
        order: initialOrder,
        loading: false,
        error: '',
        success: '',
        addonModal: false,
        selectedItem: null,
        selectedAddons: {},
        discountType: initialOrder?.discount_type || '',
        discountValue: initialOrder?.discount_value || 0,
        payModal: false,
        amountPaid: '',
        invoicePreview: null,
        invoiceModal: false,
        cartOpen: false,
        tableModal: false,
        selectedTableId: '',
        cancelModal: false,

        get filteredCategories() {
            const q = this.categorySearch.trim();
            if (!q) return this.categories;
            return this.categories.filter((c) => c.name.includes(q));
        },

        get filteredItems() {
            let items = this.allItems;
            if (this.categoryId !== 'all') {
                items = items.filter((i) => String(i.category_id) === String(this.categoryId));
            }
            const q = this.search.trim();
            if (q) {
                items = items.filter((i) => i.name.includes(q));
            }
            return items;
        },

        get itemsCount() {
            return (this.order?.items || []).reduce((n, i) => n + i.quantity, 0);
        },

        money(v) {
            return `${this.currency} ${Number(v || 0).toFixed(2)}`;
        },

        async api(url, options = {}) {
            this.loading = true;
            this.error = '';
            try {
                const res = await fetch(url, {
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    ...options,
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    const err = new Error(data.message || 'حدث خطأ');
                    err.payload = data;
                    throw err;
                }
                return data;
            } catch (e) {
                this.error = e.message;
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async ensureOrder() {
            if (this.order?.id) return this.order;

            const payload = this.pendingTableId
                ? { type: 'dine_in', cafe_table_id: Number(this.pendingTableId) }
                : { type: 'takeaway' };

            const data = await this.api(this.routes.createOrder, {
                method: 'POST',
                body: JSON.stringify(payload),
            });
            this.order = data.order;
            this.pendingTableId = null;
            this.patchOrderInUrl();
            return this.order;
        },

        patchOrderInUrl() {
            const url = new URL(window.location.href);
            if (this.order?.id) {
                url.searchParams.set('order', this.order.id);
                url.searchParams.delete('table');
            } else {
                url.searchParams.delete('order');
            }
            window.history.replaceState({}, '', url);
        },

        validateHasItems() {
            if (!this.order?.items?.length) {
                this.error = 'أضف صنفاً واحداً على الأقل قبل المتابعة.';
                return false;
            }
            this.error = '';
            return true;
        },

        async onItemClick(item) {
            if (item.addons && item.addons.length) {
                this.selectedItem = item;
                this.selectedAddons = {};
                item.addons.forEach((a) => {
                    this.selectedAddons[a.id] = false;
                });
                this.addonModal = true;
                return;
            }
            await this.addToOrder(item, []);
        },

        async confirmAddons() {
            const addons = (this.selectedItem.addons || [])
                .filter((a) => this.selectedAddons[a.id])
                .map((a) => ({ id: a.id, name: a.name, price: Number(a.price) }));
            await this.addToOrder(this.selectedItem, addons);
            this.addonModal = false;
            this.selectedItem = null;
        },

        async addToOrder(item, addons) {
            await this.ensureOrder();
            const data = await this.api(this.routes.addItem.replace('__ORDER__', this.order.id), {
                method: 'POST',
                body: JSON.stringify({
                    item_id: item.id,
                    quantity: 1,
                    addons,
                }),
            });
            this.order = data.order;
            this.syncDiscountFields();
            if (window.matchMedia('(max-width: 1023px)').matches) {
                this.cartOpen = true;
            }
        },

        async changeQty(orderItem, quantity) {
            if (quantity < 1) return;
            const data = await this.api(
                this.routes.updateItem
                    .replace('__ORDER__', this.order.id)
                    .replace('__ITEM__', orderItem.id),
                {
                    method: 'PATCH',
                    body: JSON.stringify({ quantity }),
                }
            );
            this.order = data.order;
        },

        async removeItem(orderItem) {
            const data = await this.api(
                this.routes.removeItem
                    .replace('__ORDER__', this.order.id)
                    .replace('__ITEM__', orderItem.id),
                { method: 'DELETE' }
            );
            this.order = data.order;
        },

        syncDiscountFields() {
            this.discountType = this.order?.discount_type || '';
            this.discountValue = this.order?.discount_value || 0;
        },

        async applyDiscount() {
            if (!this.order?.id) return;
            const data = await this.api(this.routes.discount.replace('__ORDER__', this.order.id), {
                method: 'POST',
                body: JSON.stringify({
                    discount_type: this.discountType || null,
                    discount_value: Number(this.discountValue || 0),
                }),
            });
            this.order = data.order;
            this.syncDiscountFields();
        },

        async saveOrder() {
            if (!this.order?.id) {
                this.error = 'ابدأ بإضافة أصناف أولاً.';
                return;
            }
            if (!this.validateHasItems()) return;

            if (!this.order.table?.id) {
                this.selectedTableId = this.pendingTableId || '';
                this.tableModal = true;
                this._completeAfterTable = false;
                return;
            }

            await this.persistSave();
        },

        async confirmTableAndSave() {
            if (!this.selectedTableId) {
                this.error = 'اختَر ترابيزة للطلب.';
                return;
            }

            try {
                const assigned = await this.api(
                    this.routes.assignTable.replace('__ORDER__', this.order.id),
                    {
                        method: 'POST',
                        body: JSON.stringify({ cafe_table_id: Number(this.selectedTableId) }),
                    }
                );
                this.order = assigned.order;
                this.tableModal = false;

                if (this._completeAfterTable) {
                    this._completeAfterTable = false;
                    await this.completeOrder();
                } else {
                    await this.persistSave();
                }
            } catch (e) {
                // error already set
            }
        },

        async persistSave() {
            try {
                const data = await this.api(this.routes.save.replace('__ORDER__', this.order.id), {
                    method: 'POST',
                    body: '{}',
                });
                this.order = data.order;
                this.success = data.message || 'تم الحفظ على الطاولة';
                this.cartOpen = false;
                setTimeout(() => {
                    window.location.href = data.redirect || this.tablesUrl;
                }, 700);
            } catch (e) {
                if (e.payload?.needs_table) {
                    this.tableModal = true;
                }
            }
        },

        async completeOrder() {
            if (!this.order?.id) {
                this.error = 'ابدأ بإضافة أصناف للطلب.';
                return;
            }
            if (!this.validateHasItems()) return;

            // سفري → ادفع مباشرة
            if (!this.order.table?.id && !this.pendingTableId) {
                this.openPay();
                return;
            }

            // مربوط بطاولة أو في انتظار اختيارها
            if (!this.order.table?.id) {
                this.selectedTableId = this.pendingTableId || '';
                this.tableModal = true;
                this._completeAfterTable = true;
                return;
            }

            try {
                const data = await this.api(this.routes.complete.replace('__ORDER__', this.order.id), {
                    method: 'POST',
                    body: '{}',
                });
                this.success = data.message;
                setTimeout(() => {
                    window.location.href = data.redirect || this.tablesUrl;
                }, 600);
            } catch (e) {
                if (e.payload?.needs_table) {
                    this.tableModal = true;
                    this._completeAfterTable = true;
                } else if (e.payload?.needs_pay) {
                    this.openPay();
                }
            }
        },

        openPay() {
            if (!this.validateHasItems()) return;
            const paid = Number(this.amountPaid);
            this.amountPaid = Number.isFinite(paid) && paid > 0
                ? paid.toFixed(2)
                : Number(this.order.total).toFixed(2);
            this.payModal = true;
        },

        async confirmPay() {
            if (!this.validateHasItems()) return;

            const amount = Number(this.amountPaid);
            if (!Number.isFinite(amount) || amount < 0) {
                this.error = 'أدخل مبلغاً صحيحاً.';
                return;
            }
            if (amount + 0.001 < Number(this.order.total)) {
                this.error = 'المبلغ المدفوع أقل من المطلوب.';
                return;
            }

            const data = await this.api(this.routes.pay.replace('__ORDER__', this.order.id), {
                method: 'POST',
                body: JSON.stringify({ amount_paid: amount }),
            });
            this.payModal = false;
            this.invoicePreview = data.invoice;
            this.invoiceModal = true;
            this.order = null;
            this.patchOrderInUrl();
        },

        askCancel() {
            if (!this.order?.id) return;
            this.cancelModal = true;
        },

        async confirmCancel() {
            this.cancelModal = false;
            await this.api(this.routes.cancel.replace('__ORDER__', this.order.id), {
                method: 'POST',
                body: '{}',
            });
            this.order = null;
            this.patchOrderInUrl();
        },

        printInvoice() {
            if (!this.invoicePreview?.id) return;
            window.open(this.routes.print.replace('__INVOICE__', this.invoicePreview.id), '_blank');
        },

        newOrder() {
            this.invoiceModal = false;
            this.invoicePreview = null;
            this.order = null;
        },
    };
}
