document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('main-search');
    const resultsContainer = document.getElementById('product-display-area');

    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            let query = this.value;

            if (query.length > 1) {
                // Fetch results from search.php
                fetch('search.php?q=' + query)
                    .then(response => response.text())
                    .then(data => {
                        resultsContainer.innerHTML = data;
                    });
            } else if (query.length === 0) {
                location.reload(); // Reset to original listings if cleared
            }
        });
    }
});