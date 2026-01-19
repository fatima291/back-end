// js/auth.js - نظام المصادقة المركزي
class AuthSystem {
    // الحصول على بيانات المستخدم الحالي
    static getCurrentUser() {
        const lastEmail = localStorage.getItem('last_login_email');
        if (!lastEmail) return null;
        
        const userData = localStorage.getItem(`user_data_${lastEmail}`) || 
                        sessionStorage.getItem(`user_data_${lastEmail}`);
        
        return userData ? JSON.parse(userData) : null;
    }
    
    // التحقق من صحة الجلسة
    static isValidSession() {
        const user = this.getCurrentUser();
        if (!user || !user.email) return false;
        
        const expiryKey = `token_expiry_${user.email}`;
        const expiry = parseInt(localStorage.getItem(expiryKey) || sessionStorage.getItem(expiryKey) || '0');
        
        return expiry > new Date().getTime();
    }
    
    // تسجيل الخروج
    static logout() {
        const user = this.getCurrentUser();
        if (user && user.email) {
            this.clearUserData(user.email);
        }
        
        // تنظيف عام
        localStorage.removeItem('last_login_email');
        localStorage.removeItem('remember_me');
        sessionStorage.clear();
        
        // حذف الكوكيز
        document.cookie = "auth_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie = "user_email=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        
        // توجيه لصفحة الدخول
        window.location.href = "login.html";
    }
    
    // تنظيف بيانات مستخدم معين
    static clearUserData(email) {
        ['auth_token', 'user_data', 'token_expiry'].forEach(key => {
            localStorage.removeItem(`${key}_${email}`);
            sessionStorage.removeItem(`${key}_${email}`);
        });
    }
    
    // توجيه إذا لم يكن مسجل دخول
    static requireAuth() {
        if (!this.isValidSession()) {
            window.location.href = "login.html?redirect=" + encodeURIComponent(window.location.pathname);
            return false;
        }
        return true;
    }
    
    // تحديث شريط المستخدم في الصفحات
    static updateUserUI() {
        const user = this.getCurrentUser();
        if (!user) return;
        
        // تحديث عناصر الصفحة
        const elements = {
            'userName': user.name || user.email,
            'userEmail': user.email,
            'userType': user.account_type,
            'welcomeMessage': `Welcome, ${user.name || user.email}!`
        };
        
        Object.keys(elements).forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = elements[id];
            }
        });
    }
}