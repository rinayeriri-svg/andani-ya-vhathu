<?php
include 'config/db.php';

if (isset($_GET['q'])) {
    $q = $conn->real_escape_string($_GET['q']);
    // Search by title or description
    $sql = "SELECT * FROM products WHERE (title LIKE '%$q%' OR description LIKE '%$q%') AND status = 'Available'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo '
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title">' . htmlspecialchars($row['title']) . '</h6>
                        <p class="fw-bold text-primary">R' . number_format($row['price'], 2) . '</p>
                        <a href="product.php?id=' . $row['product_id'] . '" class="btn btn-outline-dark btn-sm w-100">View</a>
                    </div>
                </div>
            </div>';
        }
    } else {
        echo "<p class='text-center w-100'>No items found matching '$q'.</p>";
    }
}
?>