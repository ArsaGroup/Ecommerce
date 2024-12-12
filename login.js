
const BASE_URL = "http://127.0.0.1:8000/api/api"; // Update this if needed

// Handle keyup, blur, and focus events for inputs and textareas
document.querySelectorAll('.form input, .form textarea').forEach(function (input) {
    input.addEventListener('keyup', function (e) {
        const label = input.previousElementSibling;
        if (input.value === '') {
            label.classList.remove('active', 'highlight');
        } else {
            label.classList.add('active', 'highlight');
        }
    });

    input.addEventListener('blur', function () {
        const label = input.previousElementSibling;
        if (input.value === '') {
            label.classList.remove('active', 'highlight');
        } else {
            label.classList.remove('highlight');
        }
    });

    input.addEventListener('focus', function () {
        const label = input.previousElementSibling;
        if (input.value === '') {
            label.classList.remove('highlight');
        } else {
            label.classList.add('highlight');
        }
    });
});

// Handle tab clicks
document.querySelectorAll('.tab a').forEach(function (tabLink) {
    tabLink.addEventListener('click', function (e) {
        e.preventDefault();

        // Activate the clicked tab
        const tabItem = tabLink.parentElement;
        tabItem.classList.add('active');
        Array.from(tabItem.parentElement.children).forEach(function (sibling) {
            if (sibling !== tabItem) {
                sibling.classList.remove('active');
            }
        });

        // Show the target content and hide others
        const targetSelector = tabLink.getAttribute('href');
        const targetContent = document.querySelector(targetSelector);
        document.querySelectorAll('.tab-content > div').forEach(function (content) {
            if (content !== targetContent) {
                content.style.display = 'none';
            }
        });

        targetContent.style.display = 'block';
        targetContent.style.opacity = 0;
        setTimeout(() => {
            targetContent.style.transition = 'opacity 0.6s';
            targetContent.style.opacity = 1;
        }, 0);
    });
});

// Sign Up Form Handling
const signupForm = document.querySelector("#signup-form");
signupForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    // Collect the form values with checks for null elements
    const firstNameElement = document.querySelector("[name='first_name']");
    const emailElement = document.querySelector("[name='email']");
    const passwordElement = document.querySelector("[name='password']");
    const confirmPasswordElement = document.querySelector("[name='confirm_password']");

    if (!firstNameElement || !emailElement || !passwordElement || !confirmPasswordElement) {

        console.error("One or more form fields are missing");
        return;
    }

    const firstName = firstNameElement.value;
    const email = emailElement.value;
    const password = passwordElement.value;
    const confirmPassword = confirmPasswordElement.value;

    // Log the values to verify they are correctly collected
    console.log("First Name:", firstName);
    console.log("Email:", email);
    console.log("Password:", password);
    console.log("Confirm Password:", confirmPassword);

    // Validate password match
    if (password !== confirmPassword) {
        alert("Passwords do not match.");
        return;
    }

    // Prepare data object with password_confirmation
    const data = { name: firstName, email, password, password_confirmation: confirmPassword };

    try {
        const response = await fetch(`${BASE_URL}/register`, {


            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(data),
        });


if (response.ok) {
            const result = await response.json();
            document.cookie = `token=${result.token}; path=/;`
            ;
            alert("Registration successful!");
            window.location.href = '/dashboard';
        } else {
            const error = await response.json();
            console.log(error.errors); // Log validation messages
            alert("Registration failed: " + JSON.stringify(error.errors));
        }
    } catch (err) {
        alert("An error occurred: " + err.message);
    }
});

// Log In Form Handling
const loginForm = document.querySelector("#login-form");
loginForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    // Collect form values
    const email = document.querySelector("[name='email']").value;
    const password = document.querySelector("[name='password']").value;

    const data = { email, password };

    try {
        const response = await fetch(`${BASE_URL}/register`, {

            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(data),
        });

        if (response.ok) {
            const result = await response.json();
            document.cookie = `token=${result.token}; path=/;`;
            
            alert("Login successful!");
            window.location.href = '/dashboard';
        } else {
            const error = await response.json();
            alert("Login failed: " + JSON.stringify(error.errors));
        }
    } catch (err) {
        alert("An error occurred: " + err.message);
    }
});

