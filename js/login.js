const BASE_URL = "http://127.0.0.1:8000/api/api"; // آدرس API

// فعال کردن انیمیشن برای فیلدهای فرم
document.querySelectorAll('.form input').forEach(input => {
    input.addEventListener('focus', () => {
        input.previousElementSibling?.classList.add('highlight');
    });

    input.addEventListener('blur', () => {
        const label = input.previousElementSibling;
        if (label && input.value === '') {
            label.classList.remove('highlight', 'active');
        } else {
            label?.classList.remove('highlight');
        }
    });

    input.addEventListener('keyup', () => {
        const label = input.previousElementSibling;
        if (label) {
            label.classList[input.value ? 'add' : 'remove']('active');
        }
    });
});

// مدیریت تب‌ها
document.querySelectorAll('.tab a').forEach(tabLink => {
    tabLink.addEventListener('click', e => {
        e.preventDefault();
        const targetSelector = tabLink.getAttribute('href');
        const targetContent = document.querySelector(targetSelector);

        document.querySelectorAll('.tab-content > div').forEach(content => {
            content.style.display = 'none';
        });

        document.querySelectorAll('.tab li').forEach(tab => {
            tab.classList.remove('active');
        });

        tabLink.parentElement.classList.add('active');
        targetContent.style.display = 'block';
    });
});

// ثبت‌نام
document.querySelector("#signup-form").addEventListener("submit", async function (e) {
    e.preventDefault();

    const firstName = document.querySelector("#signup-first-name").value.trim();
    const email = document.querySelector("#signup-email").value.trim().toLowerCase();
    const password = document.querySelector("#signup-password").value.trim();
    const confirmPassword = document.querySelector("#signup-confirm-password").value.trim();

    if (!firstName || !email || !password || !confirmPassword) {
        alert("لطفاً تمام فیلدها را پر کنید.");
        return;
    }

    if (password !== confirmPassword) {
        alert("رمز عبور و تایید آن یکسان نیست.");
        return;
    }

    try {
        const response = await fetch(`${BASE_URL}/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: firstName,
                email,
                password,
                password_confirmation: confirmPassword,
            }),
        });

        if (response.ok) {
            const result = await response.json();
            document.cookie = `token=${result.token}; path=/; max-age=${60 * 60 * 24 * 30}`;
            alert("ثبت‌نام موفقیت‌آمیز بود!");
            window.location.href = "/dashboard";
        } else {
            const error = await response.json();
            alert("خطا در ثبت‌نام: " + (error.message || "خطای ناشناخته"));
        }
    } catch (err) {
        alert("خطا: " + err.message);
    }
});

// لاگین
document.querySelector("#login-form").addEventListener("submit", async function (e) {
    e.preventDefault();

    const email = document.querySelector("#login-email").value.trim().toLowerCase();
    const password = document.querySelector("#login-password").value.trim();

    if (!email || !password) {
        alert("لطفاً تمام فیلدها را پر کنید.");
        return;
    }

    try {
        const response = await fetch(`${BASE_URL}/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password }),
        });

        if (response.ok) {
            const result = await response.json();
            document.cookie = `token=${result.token}; path=/; max-age=${60 * 60 * 24 * 30}`;
            alert("ورود موفقیت‌آمیز بود!");
            window.location.href = "/dashboard";
        } else {
            const error = await response.json();
            alert("خطا در ورود: " + (error.message || "اعتبارنامه نامعتبر"));
        }
    } catch (err) {
        alert("خطا: " + err.message);
    }
});
