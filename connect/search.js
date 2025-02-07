document.addEventListener("DOMContentLoaded", function () {
    document.querySelector("form").addEventListener("submit", function (e) {
        e.preventDefault(); // Prevent default form submission

        // Get the search query value
        const searchQuery = document.querySelector("input[name='search']").value;
        const token = document.querySelector("input[name='_token']").value; // CSRF token

        // The URL for the search request
        const url = `http://127.0.0.1:8000/product_search?search=${encodeURIComponent(searchQuery)}`;

        // Send the request using fetch
        fetch(url, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": token // Include CSRF token if required
            }
        })
        .then(response => response.json()) // Parse response as JSON
        .then(data => {
            console.log(data); // Debugging: log the response
            // Handle the response (update the UI with search results)
            displaySearchResults(data);
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while searching.");
        });
    });
});

// Function to display search results dynamically
function displaySearchResults(data) {
    let resultsContainer = document.getElementById("search-results");
    if (!resultsContainer) {
        resultsContainer = document.createElement("div");
        resultsContainer.id = "search-results";
        document.body.appendChild(resultsContainer);
    }
    
    resultsContainer.innerHTML = ""; // Clear previous results

    if (data.length === 0) {
        resultsContainer.innerHTML = "<p>No results found.</p>";
        return;
    }

    // Loop through search results and display them
    data.forEach(item => {
        const resultItem = document.createElement("p");
        resultItem.textContent = item.name; // Adjust based on API response
        resultsContainer.appendChild(resultItem);
    });
}
