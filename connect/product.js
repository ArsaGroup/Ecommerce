// Assuming the product details are available through an API at '/api/product/{id}'
document.addEventListener("DOMContentLoaded", function () {
    const productId = 7; // The product ID (you can change it as needed)

    // Fetch product details from the backend
    fetch(`http://127.0.0.1:8000/api/product/${productId}`)
        .then(response => response.json())
        .then(product => {
            // Populate the product details on the page
            document.getElementById('product-name').innerText = product.name;
            document.getElementById('product-discount-price').innerText = `Discount Price: $${product.discountPrice}`;
            document.getElementById('product-price').innerText = `Price: $${product.originalPrice}`;
            document.getElementById('product-category').innerText = `Category: ${product.category}`;
            document.getElementById('product-description').innerText = `Description: ${product.description}`;
            document.getElementById('product-quantity').innerText = `Quantity: ${product.quantity}`;
            document.getElementById('product-img').src = product.imageUrl;

            // If needed, update the form action dynamically based on the product ID or other properties
            const formAction = `http://127.0.0.1:8000/add_cart/${product.id}`;
            document.querySelector('form').action = formAction;
        })
        .catch(error => {
            console.error('Error fetching product details:', error);
        });
});
