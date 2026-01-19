document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.querySelector('form');
    
    if (!registerForm) {
        console.error('لم يتم العثور على نموذج التسجيل');
        return;
    }
    
    // دالة معالجة إرسال النموذج
    registerForm.addEventListener('submit', async function(event) {
        event.preventDefault(); // منع الإرسال العادي للنموذج
        
        // جمع البيانات من الحقول
        const formData = {
            name: document.getElementById('name').value.trim(),
            email: document.getElementById('email').value.trim(),
            password: document.getElementById('password').value,
            account_type: getSelectedAccountType()
        };
        
        // التحقق من البيانات قبل الإرسال
        const validation = validateFormData(formData);
        if (!validation.isValid) {
            showError(validation.message);
            return;
        }
        
        // إظهار حالة التحميل
        const submitButton = event.target.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.textContent = 'جاري التسجيل...';
        submitButton.disabled = true;
        
        try {
            // إرسال البيانات إلى الباك إند
            const response = await sendRegistrationData(formData);
            
            if (response.success) {
                showSuccess(response.message);
                // توجيه المستخدم إلى صفحة الدخول بعد 2 ثانية
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 2000);
            } else {
                showError(response.message, response.data?.errors);
            }
        } catch (error) {
            console.error('حدث خطأ في الاتصال:', error);
            showError('حدث خطأ في الاتصال بالخادم. يرجى المحاولة مرة أخرى.');
        } finally {
            // إعادة حالة الزر إلى الأصل
            submitButton.textContent = originalText;
            submitButton.disabled = false;
        }
    });
    
    // دالة للحصول على نوع الحساب المحدد
    function getSelectedAccountType() {
        const selectedRadio = document.querySelector('input[name="accountType"]:checked');
        return selectedRadio ? selectedRadio.value : 'student';
    }
    
    // دالة للتحقق من صحة البيانات
    function validateFormData(data) {
        if (!data.name || data.name.length < 2) {
            return { isValid: false, message: 'الاسم يجب أن يكون حرفين على الأقل' };
        }
        
        if (!data.email || !isValidEmail(data.email)) {
            return { isValid: false, message: 'البريد الإلكتروني غير صالح' };
        }
        
        if (!data.password || data.password.length < 6) {
            return { isValid: false, message: 'كلمة المرور يجب أن تكون 6 أحرف على الأقل' };
        }
        
        if (!data.account_type) {
            return { isValid: false, message: 'يرجى اختيار نوع الحساب' };
        }
        
        return { isValid: true, message: '' };
    }
    
    // دالة للتحقق من صحة البريد الإلكتروني
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // دالة لإرسال البيانات إلى الباك إند
    async function sendRegistrationData(data) {
        const apiUrl = '../backend/api/auth/register.php'; // رابط تجريبي
        
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error(`خطأ في الخادم: ${response.status}`);
        }
        
        return await response.json();
    }
    
    // دالة لعرض رسالة النجاح
    function showSuccess(message) {
        // يمكن استبدال alert بنافذة جميلة لاحقاً
        alert('✅ ' + message);
        
        // أو إضافة رسالة في الصفحة نفسها
        const successDiv = document.createElement('div');
        successDiv.className = 'alert alert-success mt-3';
        successDiv.textContent = message;
        successDiv.style.cssText = 'text-align: center; padding: 10px; border-radius: 5px;';
        
        const formCard = document.querySelector('.register-card');
        formCard.appendChild(successDiv);
        
        // إخفاء الرسالة بعد 5 ثواني
        setTimeout(() => successDiv.remove(), 5000);
    }
    
    // دالة لعرض رسالة الخطأ
    function showError(message, errors = null) {
        let errorMessage = '❌ ' + message;
        
        // إضافة أخطاء التحقق إذا وجدت
        if (errors) {
            errorMessage += '\n\n';
            for (const [field, error] of Object.entries(errors)) {
                errorMessage += `• ${error}\n`;
            }
        }
        
        // يمكن استبدال alert بنافذة جميلة لاحقاً
        alert(errorMessage);
        
        // أو إضافة رسالة خطأ في الصفحة
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger mt-3';
        errorDiv.textContent = errorMessage.replace('❌ ', '');
        errorDiv.style.cssText = 'text-align: center; padding: 10px; border-radius: 5px; color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb;';
        
        // إزالة أي رسائل خطأ سابقة
        const existingError = document.querySelector('.alert-danger');
        if (existingError) {
            existingError.remove();
        }
        
        const formCard = document.querySelector('.register-card');
        formCard.appendChild(errorDiv);
        
        // إخفاء الرسالة بعد 5 ثواني
        setTimeout(() => errorDiv.remove(), 5000);
    }
    
    // إضافة CSS للرسائل
    const style = document.createElement('style');
    style.textContent = `
        .alert {
            transition: all 0.3s ease;
        }
        .form-control.error {
            border-color: #dc3545;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 5px;
        }
    `;
    document.head.appendChild(style);
    
    // إضافة تحقق في الوقت الحقيقي (اختياري)
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    if (nameInput && emailInput && passwordInput) {
        // تحقق من الاسم أثناء الكتابة
        nameInput.addEventListener('input', function() {
            if (this.value.length < 2 && this.value.length > 0) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });
        
        // تحقق من البريد الإلكتروني أثناء الكتابة
        emailInput.addEventListener('input', function() {
            if (!isValidEmail(this.value) && this.value.length > 0) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });
        
        // تحقق من كلمة المرور أثناء الكتابة
        passwordInput.addEventListener('input', function() {
            if (this.value.length < 6 && this.value.length > 0) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });
    }
    
    // تسجيل لأغراض التصحيح
    console.log('✅ تم تحميل ملف register.js بنجاح');
});
