// js/admin_orders.js

// وظائف الجافاسكريبت لإدارة الطلبات

function showOrderDetails(order) {
    const modal = document.getElementById('orderDetailsModal');
    const content = document.getElementById('orderDetailsContent');
    
    const orderDate = new Date(order.order_date);
    const formattedDate = orderDate.toLocaleDateString('ar-SA');
    
    const statusLabels = {
        'pending': 'قيد الانتظار',
        'confirmed': 'مؤكد',
        'shipped': 'تم الشحن',
        'delivered': 'تم التوصيل',
        'cancelled': 'ملغي'
    };
    
    const statusClass = {
        'pending': 'status-pending',
        'confirmed': 'status-confirmed',
        'shipped': 'status-shipped',
        'delivered': 'status-delivered',
        'cancelled': 'status-cancelled'
    };
    
    let itemsTable = '';
    let totalQuantity = 0;
    
    if (order.items && order.items.length > 0) {
        itemsTable = `
            <div class="items-table-container">
                <h4 style="color: #2c3e50; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-box-open"></i>
                    عناصر الطلب (${order.items.length})
                </h4>
                <table class="items-table-details">
                    <thead>
                        <tr>
                            <th>المنتج</th>
                            <th>العلامة التجارية</th>
                            <th>الكمية</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        order.items.forEach(item => {
            totalQuantity += item.quantity;
            itemsTable += `
                <tr>
                    <td>${item.product_description}</td>
                    <td class="brand-name-cell">
                        ${item.brand_names ? item.brand_names : 'غير محدد'}
                    </td>
                    <td><strong>${item.quantity}</strong></td>
                    <td>${item.notes ? item.notes : '---'}</td>
                </tr>
            `;
        });
        
        itemsTable += `
                    </tbody>
                </table>
            </div>
        `;
    } else {
        itemsTable = '<p style="color: #7f8c8d; text-align: center;">لا توجد عناصر</p>';
    }
    
    const detailsHtml = `
        <div class="order-info-grid">
            <div class="info-item">
                <span class="info-label">رقم الطلب</span>
                <span class="info-value">#${order.id}</span>
            </div>
            <div class="info-item">
                <span class="info-label">تاريخ الطلب</span>
                <span class="info-value">${formattedDate}</span>
            </div>
            <div class="info-item">
                <span class="info-label">الشركة</span>
                <span class="info-value">${order.company_name || 'غير معروف'}</span>
            </div>
            <div class="info-item">
                <span class="info-label">العميل</span>
                <span class="info-value">${order.customer_name || 'غير معروف'}</span>
            </div>
            <div class="info-item">
                <span class="info-label">عدد المنتجات</span>
                <span class="info-value">${order.item_count} منتج</span>
            </div>
            <div class="info-item">
                <span class="info-label">الحالة</span>
                <span class="info-value">
                    <span class="status-badge ${statusClass[order.status] || ''}">
                        ${statusLabels[order.status] || order.status}
                    </span>
                </span>
            </div>
        </div>
        
        ${itemsTable}
        
        <div class="total-summary">
            <span style="font-weight: 500;">
                <i class="fas fa-calculator"></i>
                إجمالي الوحدات
            </span>
            <span style="font-size: 1.2rem; font-weight: bold;">
                ${totalQuantity} وحدة
            </span>
        </div>
        
        ${order.notes ? `
        <div style="margin-top: 1.5rem;">
            <h4 style="color: #2c3e50; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-sticky-note"></i>
                ملاحظات عامة
            </h4>
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                <p style="margin: 0; color: #555;">
                    ${order.notes || 'لا توجد ملاحظات'}
                </p>
            </div>
        </div>
        ` : ''}
    `;
    
    content.innerHTML = detailsHtml;
    modal.style.display = 'flex';
}

function closeOrderDetails() {
    document.getElementById('orderDetailsModal').style.display = 'none';
}

// تهيئة الأحداث عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    // إغلاق النافذة عند النقر خارجها
    const modal = document.getElementById('orderDetailsModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderDetails();
            }
        });
    }
    
    // إغلاق النافذة بالزر Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeOrderDetails();
        }
    });
    
    // تأكيد الإجراءات قبل التنفيذ
    document.querySelectorAll('.btn-confirm, .btn-cancel, .btn-ship, .btn-deliver, .btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            if (this.classList.contains('btn-confirm')) {
                return confirm('هل تريد تأكيد هذا الطلب؟');
            } else if (this.classList.contains('btn-cancel')) {
                return confirm('هل تريد إلغاء هذا الطلب؟');
            } else if (this.classList.contains('btn-ship')) {
                return confirm('هل تريد تحديث حالة الطلب إلى تم الشحن؟');
            } else if (this.classList.contains('btn-deliver')) {
                return confirm('هل تريد تأكيد استلام الطلب؟');
            } else if (this.classList.contains('btn-delete')) {
                return confirm('هل أنت متأكد من حذف هذا الطلب؟ هذا الإجراء لا يمكن التراجع عنه.');
            }
        });
    });
});

// تصدير الدوال للاستخدام من ملفات أخرى
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showOrderDetails,
        closeOrderDetails
    };
}