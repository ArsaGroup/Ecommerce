document.addEventListener("DOMContentLoaded", function () {
    fetchProducts();
});

function fetchProducts() {
    const url = "http://127.0.0.1:8000/products"; // Backend API endpoint

    fetch(url, {
        method: "GET",
        headers: {
            "Content-Type": "application/json"
        }
    })
    .then(response => response.json()) // Convert response to JSON
    .then(data => {
        console.log(data); // Debugging: Log the fetched data
        displayProducts(data); // Call function to display data
    })
    .catch(error => {
        console.error("Error fetching products:", error);
        alert("Failed to load products.");
    });
}

function displayProducts(products) {
    const container = document.getElementById("product-list"); // Assuming there's a container for products

    if (!container) {
        console.error("No container element found for product list.");
        return;
    }

    container.innerHTML = ""; // Clear any existing content

    if (products.length === 0) {
        container.innerHTML = "<p>No products found.</p>";
        return;
    }

    // Loop through products and create HTML elements dynamically
    products.forEach(product => {
        const productItem = document.createElement("div");
        productItem.classList.add("product-item"); // Add styling class if needed

        productItem.innerHTML = `
            <h3>${product.name}</h3>
            <p>Price: $${product.price}</p>
            <p>${product.description}</p>
            <img src="${product.image_url}" alt="${product.name}" width="150">
            <button onclick="addToCart(${product.id})">Add to Cart</button>
        `;

        container.appendChild(productItem);
    });
}

function addToCart(productId) {
    alert(`Product ${productId} added to cart!`); // Placeholder function for cart logic
}
