// js/login.js
document.addEventListener('DOMContentLoaded', function() {
    // العناصر
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const rememberMe = document.getElementById('rememberMe');
    const loginButton = document.getElementById('loginButton');
    const loginText = document.getElementById('loginText');
    const loginLoading = document.getElementById('loginLoading');
    const successAlert = document.getElementById('successAlert');
    const errorAlert = document.getElementById('errorAlert');
    
    // تهيئة الصفحة
    initializePage();
    
    // === دوال التهيئة ===
    function initializePage() {
        // 1. تحميل البريد المحفوظ
        const savedEmail = localStorage.getItem('remembered_email');
        const rememberMeChecked = localStorage.getItem('remember_me') === 'true';
        
        if (savedEmail && rememberMeChecked) {
            emailInput.value = savedEmail;
            rememberMe.checked = true;
        }
        
        // 2. التحقق من جلسة سابقة
        checkExistingSession();
    }
    
    // === معالجة إرسال النموذج ===
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        hideAlerts();
        clearValidationErrors();
        
        const loginData = {
            email: emailInput.value.trim(),
            password: passwordInput.value
        };
        
        const validation = validateLoginData(loginData);
        if (!validation.isValid) {
            showError(validation.message);
            highlightValidationErrors(validation.errors);
            return;
        }
        
        // حفظ البريد إذا كان "تذكرني" مفعل
        if (rememberMe.checked) {
            localStorage.setItem('remembered_email', loginData.email);
            localStorage.setItem('remember_me', 'true');
        } else {
            localStorage.removeItem('remembered_email');
            localStorage.removeItem('remember_me');
        }
        
        setLoadingState(true);
        
        try {
            const response = await fetch('../backend/api/auth/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(loginData)
            });
            
            if (!response.ok) {
                throw new Error(`Server error: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                saveAuthData(result.data, rememberMe.checked);
                showSuccess('Login successful! Redirecting...');
                
                setTimeout(() => {
                    window.location.href = result.data.redirect;
                }, 1500);
                
            } else {
                throw new Error(result.message || 'Login failed');
            }
            
        } catch (error) {
            console.error('Login error:', error);
            handleLoginError(error.message);
            setLoadingState(false);
        }
    });
    
    // === دوال المساعدة ===
    
    function validateLoginData(data) {
        const errors = {};
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!data.email) errors.email = 'Email is required';
        else if (!emailRegex.test(data.email)) errors.email = 'Please enter a valid email';
        
        if (!data.password) errors.password = 'Password is required';
        else if (data.password.length < 6) errors.password = 'Password must be at least 6 characters';
        
        return {
            isValid: Object.keys(errors).length === 0,
            message: Object.keys(errors).length > 0 ? 'Please fix the errors below' : '',
            errors: errors
        };
    }
    
    function saveAuthData(data, remember) {
    const storage = remember ? localStorage : sessionStorage;
    
    // حفظ التوكن JWT مع البريد كمفتاح
    const emailKey = `auth_token_${data.user.email}`;
    storage.setItem(emailKey, data.token);
    
    // حفظ بيانات المستخدم مع البريد كمفتاح
    const userKey = `user_data_${data.user.email}`;
    storage.setItem(userKey, JSON.stringify(data.user));
    
    // حفظ تاريخ انتهاء الصلاحية
    const expiryTime = new Date().getTime() + (data.expires_in * 1000);
    const expiryKey = `token_expiry_${data.user.email}`;
    storage.setItem(expiryKey, expiryTime.toString());
    
    // حفظ آخر بريد مسجل (لتعبئة الحقل)
    if (remember) {
        localStorage.setItem('last_login_email', data.user.email);
        localStorage.setItem('remember_me', 'true');
    } else {
        localStorage.removeItem('last_login_email');
        localStorage.removeItem('remember_me');
    }
    
    setCookie('auth_token', data.token, remember ? 7 : 1);
    setCookie('user_email', data.user.email, remember ? 7 : 1);
    }
    
    function checkExistingSession() {
    // الحصول على آخر بريد مسجل
    const lastEmail = localStorage.getItem('last_login_email');
    
    if (!lastEmail) return;
    
    // البحث عن التوكن الخاص بهذا البريد
    const token = localStorage.getItem(`auth_token_${lastEmail}`) || 
                  sessionStorage.getItem(`auth_token_${lastEmail}`);
    
    if (token) {
        const expiryKey = `token_expiry_${lastEmail}`;
        const expiry = parseInt(localStorage.getItem(expiryKey) || sessionStorage.getItem(expiryKey) || '0');
        
        if (expiry > new Date().getTime()) {
            // عرض خيار للاستمرار أو تسجيل جديد
            showSessionOptions(lastEmail);
        } else {
            // تنظيف البيانات المنتهية
            clearUserAuthData(lastEmail);
        }
    }
}

// دالة جديدة لعرض الخيارات
function showSessionOptions(email) {
    const userData = JSON.parse(localStorage.getItem(`user_data_${email}`) || 
                                sessionStorage.getItem(`user_data_${email}`) || '{}');
    
    const optionsDiv = document.createElement('div');
    optionsDiv.className = 'alert alert-warning mb-4';
    optionsDiv.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-person-circle me-2"></i>
                <strong>Session found for: ${email}</strong>
                <div class="small">(${userData.account_type || 'Unknown'})</div>
            </div>
            <div>
                <button class="btn btn-sm btn-success me-2" id="continueSessionBtn">
                    <i class="bi bi-play-circle"></i> Continue
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="newLoginBtn">
                    <i class="bi bi-person-plus"></i> New Login
                </button>
            </div>
        </div>
    `;
    
    // إضافته قبل النموذج
    const form = document.getElementById('loginForm');
    form.parentNode.insertBefore(optionsDiv, form);
    
    // زر الاستمرار بالجلسة الحالية
    document.getElementById('continueSessionBtn').addEventListener('click', function() {
        redirectBasedOnAccount(userData.account_type);
    });
    
    // زر تسجيل دخول جديد
    document.getElementById('newLoginBtn').addEventListener('click', function() {
        // حذف الجلسة الحالية للسماح بتسجيل جديد
        clearUserAuthData(email);
        optionsDiv.remove();
        showSuccess('Session cleared. You can now login with a different account.');
    });
}

// دالة تنظيف بيانات مستخدم معين
function clearUserAuthData(email) {
    const keys = ['auth_token', 'user_data', 'token_expiry'];
    
    keys.forEach(key => {
        localStorage.removeItem(`${key}_${email}`);
        sessionStorage.removeItem(`${key}_${email}`);
    });
    
    localStorage.removeItem('last_login_email');
}
    
    function redirectBasedOnAccount(accountType) {
        const redirectMap = {
            'student': 'requsets.html',
            'translator': 'Translation_result.html',
            'teacher': 'Courses.html',
            'admin': 'Admin.html'
        };
        window.location.href = redirectMap[accountType] || 'index.html';
    }
    
    function handleLoginError(errorMessage) {
        let userMessage = 'Login failed. Please try again.';
        
        if (errorMessage.includes('البريد الإلكتروني أو كلمة المرور') || errorMessage.includes('Email or password')) {
            userMessage = 'Invalid email or password';
        } else if (errorMessage.includes('الحساب معطل') || errorMessage.includes('account is disabled')) {
            userMessage = 'Your account is disabled. Please contact support';
        } else if (errorMessage.includes('الخادم') || errorMessage.includes('server')) {
            userMessage = 'Server error. Please try again later';
        }
        
        showError(userMessage);
    }
    
    function setLoadingState(isLoading) {
        loginText.style.display = isLoading ? 'none' : 'inline';
        loginLoading.style.display = isLoading ? 'inline' : 'none';
        loginButton.disabled = isLoading;
    }
    
    function showSuccess(message) {
        successAlert.textContent = message;
        successAlert.style.display = 'block';
        errorAlert.style.display = 'none';
    }
    
    function showError(message) {
        errorAlert.textContent = message;
        errorAlert.style.display = 'block';
        successAlert.style.display = 'none';
    }
    
    function hideAlerts() {
        successAlert.style.display = 'none';
        errorAlert.style.display = 'none';
    }
    
    function highlightValidationErrors(errors) {
        if (errors.email) {
            emailInput.classList.add('is-invalid');
            document.getElementById('emailError').textContent = errors.email;
        }
        if (errors.password) {
            passwordInput.classList.add('is-invalid');
            document.getElementById('passwordError').textContent = errors.password;
        }
    }
    
    function clearValidationErrors() {
        emailInput.classList.remove('is-invalid');
        passwordInput.classList.remove('is-invalid');
    }
    
    function setCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "expires=" + date.toUTCString();
        document.cookie = `${name}=${value};${expires};path=/;SameSite=Strict`;
    }
    
    function clearAuthData() {
        ['auth_token', 'user_data', 'token_expiry', 'remember_me'].forEach(key => {
            localStorage.removeItem(key);
            sessionStorage.removeItem(key);
        });
        document.cookie = "auth_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    }
    
    // === التحقق في الوقت الحقيقي ===
    emailInput.addEventListener('input', function() {
        if (this.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.value)) {
                this.classList.add('is-invalid');
                document.getElementById('emailError').textContent = 'Please enter a valid email';
            } else {
                this.classList.remove('is-invalid');
            }
        }
    });
    
    passwordInput.addEventListener('input', function() {
        if (this.value && this.value.length < 6) {
            this.classList.add('is-invalid');
            document.getElementById('passwordError').textContent = 'Password must be at least 6 characters';
        } else {
            this.classList.remove('is-invalid');
        }
    });
    
    console.log('✅ Login system loaded successfully');
});