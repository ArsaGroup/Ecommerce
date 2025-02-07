document.addEventListener("DOMContentLoaded", function() {
    // Fetch data from the backend API
    fetch('/path/to/your/api') // Change to your backend API endpoint
        .then(response => response.text())  // Assuming the response is text (CSV data)
        .then(content => {
            // Split the content into rows
            const rows = content.split("\n");
            
            // Parse the rows into a 2D array (semicolons as delimiter)
            const table = rows.map(row => row.split(";"));
            
            // Display the parsed table (Example: output first 5 rows)
            const output = document.getElementById("output");
            output.innerHTML = table.slice(0, 5).map(row => `<pre>${row.join(", ")}</pre>`).join('');
            
            // Optionally, log the table for debugging
            console.log(table);
        })
        .catch(error => {
            console.error("Error fetching data:", error);
        });
});
