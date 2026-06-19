(function () {
    const packs = {
        'zh-CN': {
            order: {
                title: '\u8ba2\u5355\u7ba1\u7406',
                loading: '\u52a0\u8f7d\u4e2d...',
                createOrder: '\u521b\u5efa\u8ba2\u5355',
                totalOrders: '\u5171 {count} \u4e2a\u8ba2\u5355',
                empty: '\u6682\u65e0\u8ba2\u5355',
                id: 'ID',
                orderNo: '\u8ba2\u5355\u53f7',
                product: '\u5546\u54c1',
                quantity: '\u6570\u91cf',
                energy: '\u80fd\u91cf',
                status: '\u72b6\u6001',
                buyer: '\u4e0b\u5355\u4eba',
                buyerInfo: '\u4e0b\u5355\u4eba\u4fe1\u606f',
                userId: '\u7528\u6237ID',
                noUser: '\u65e0\u7528\u6237\u4fe1\u606f',
                expiresAt: '\u5931\u6548\u65f6\u95f4',
                createdAt: '\u521b\u5efa\u65f6\u95f4',
                payTime: '\u652f\u4ed8\u65f6\u95f4',
                deliverTime: '\u53d1\u8d27\u65f6\u95f4',
                deliveryContent: '\u53d1\u8d27\u5185\u5bb9',
                noContent: '\u6682\u65e0\u5185\u5bb9',
                actions: '\u64cd\u4f5c',
                detail: '\u8be6\u60c5',
                detailTitle: '\u8ba2\u5355\u8be6\u60c5',
                edit: '\u7f16\u8f91',
                editOrder: '\u7f16\u8f91\u8ba2\u5355',
                setPaid: '\u8bbe\u5df2\u652f\u4ed8',
                setExpired: '\u8bbe\u5df2\u5931\u6548',
                deliver: '\u53d1\u8d27',
                delete: '\u5220\u9664',
                close: '\u5173\u95ed',
                confirmDeliver: '\u786e\u5b9a\u7ed9\u8fd9\u4e2a\u8ba2\u5355\u53d1\u8d27\u5417\uff1f',
                confirmDelete: '\u786e\u5b9a\u5220\u9664\u8fd9\u4e2a\u8ba2\u5355\u5417\uff1f',
                productRequired: '\u8bf7\u5148\u521b\u5efa\u5546\u54c1',
                buyerEmail: '\u4e70\u5bb6\u90ae\u7bb1',
                contact: '\u8054\u7cfb\u65b9\u5f0f',
                remark: '\u5907\u6ce8',
                optional: '\u53ef\u9009',
                cancel: '\u53d6\u6d88',
                save: '\u4fdd\u5b58',
                create: '\u521b\u5efa',
                modalCreate: '\u521b\u5efa\u8ba2\u5355',
                modalEdit: '\u7f16\u8f91\u8ba2\u5355',
                remaining: '\u5269\u4f59{hours}\u5c0f\u65f6{minutes}\u5206',
                expiredSuffix: '\u5df2\u5931\u6548',
                statusPending: '\u5f85\u652f\u4ed8',
                statusPaid: '\u5df2\u652f\u4ed8',
                statusDelivered: '\u5df2\u53d1\u8d27',
                statusRefunded: '\u5df2\u9000\u6b3e',
                statusExpired: '\u5df2\u5931\u6548',
                statusCancelled: '\u5df2\u53d6\u6d88'
            }
        },
        'en-US': {
            order: {
                title: 'Order Management',
                loading: 'Loading...',
                createOrder: 'Create Order',
                totalOrders: '{count} orders',
                empty: 'No orders',
                id: 'ID',
                orderNo: 'Order No.',
                product: 'Product',
                quantity: 'Quantity',
                energy: 'Energy',
                status: 'Status',
                buyer: 'Buyer',
                buyerInfo: 'Buyer Info',
                userId: 'User ID',
                noUser: 'No user info',
                expiresAt: 'Expires At',
                createdAt: 'Created At',
                payTime: 'Pay Time',
                deliverTime: 'Deliver Time',
                deliveryContent: 'Delivery Content',
                noContent: 'No content',
                actions: 'Actions',
                detail: 'Detail',
                detailTitle: 'Order Detail',
                edit: 'Edit',
                editOrder: 'Edit Order',
                setPaid: 'Set Paid',
                setExpired: 'Set Expired',
                deliver: 'Deliver',
                delete: 'Delete',
                close: 'Close',
                confirmDeliver: 'Deliver this order?',
                confirmDelete: 'Delete this order?',
                productRequired: 'Please create a product first',
                buyerEmail: 'Buyer Email',
                contact: 'Contact',
                remark: 'Remark',
                optional: 'Optional',
                cancel: 'Cancel',
                save: 'Save',
                create: 'Create',
                modalCreate: 'Create Order',
                modalEdit: 'Edit Order',
                remaining: '{hours}h {minutes}m left',
                expiredSuffix: 'Expired',
                statusPending: 'Pending',
                statusPaid: 'Paid',
                statusDelivered: 'Delivered',
                statusRefunded: 'Refunded',
                statusExpired: 'Expired',
                statusCancelled: 'Cancelled'
            }
        }
    };

    function getByPath(source, path) {
        return String(path || '').split('.').reduce((current, key) => current && current[key] !== undefined ? current[key] : undefined, source);
    }

    function format(template, replacements) {
        return String(template || '').replace(/\{([a-zA-Z0-9_]+)\}/g, function (_, key) {
            return replacements && replacements[key] !== undefined ? String(replacements[key]) : '';
        });
    }

    window.AdminLang = {
        current: localStorage.getItem('admin_lang') || 'zh-CN',
        packs: packs,
        t: function (path, replacements) {
            const currentPack = packs[this.current] || packs['zh-CN'];
            const fallbackPack = packs['en-US'];
            const template = getByPath(currentPack, path) || getByPath(fallbackPack, path) || path;
            return format(template, replacements || {});
        }
    };
})();
