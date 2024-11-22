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
