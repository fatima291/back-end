// js/logout.js - معالجة تسجيل الخروج
document.addEventListener('DOMContentLoaded', function() {
    // إضافة زر الخروج إذا كان موجوداً في الصفحة
    const logoutButtons = document.querySelectorAll('.logout-btn, [data-action="logout"]');
    
    logoutButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // تأكيد من المستخدم
            if (confirm('Are you sure you want to logout?')) {
                AuthSystem.logout();
            }
        });
    });
    
    // تحديث واجهة المستخدم
    if (typeof AuthSystem !== 'undefined') {
        AuthSystem.updateUserUI();
    }
});