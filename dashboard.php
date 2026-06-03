<?php 
include 'config/db.php'; 
include 'includes/header.php'; // Ensure you have a header file
?>

<div class="container-fluid mt-4">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar border-end">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link active fw-bold" href="#">All Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="become_seller.php">Sell an Item</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">My Watchlist</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Purchase History</a></li>
                </ul>
            </div>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Featured Listings</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="become_seller.php" class="btn btn-sm btn-outline-primary">List an Item</a>
                </div>
            </div>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
                <?php
                $sql = "SELECT * FROM products WHERE status = 'Available' ORDER BY product_id DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm">
                                <img src="https://via.placeholder.com/200" class="card-img-top" alt="Product">
                                <div class="card-body">
                                    <h6 class="card-title text-truncate">' . htmlspecialchars($row['title']) . '</h6>
                                    <p class="card-text fw-bold mb-1">R' . number_format($row['price'], 2) . '</p>
                                    <p class="text-muted small mb-3">Buy It Now</p>
                                    <div class="d-grid">
                                        <a href="product.php?id=' . $row['product_id'] . '" class="btn btn-primary btn-sm">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo "<p class='text-muted'>No active listings found. Be the first to sell!</p>";
                }
                ?>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>