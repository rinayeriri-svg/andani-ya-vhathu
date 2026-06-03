<?php
include '../config/db.php';
include 'includes/admin_header.php';

// Handle Deletion or Dismissal
if (isset($_GET['action'])) {
    $r_id = intval($_GET['id']);
    if ($_GET['action'] == 'delete') {
        // Delete the product itself
        $conn->query("DELETE FROM products WHERE product_id = (SELECT product_id FROM reports WHERE report_id = $r_id)");
    }
    $conn->query("DELETE FROM reports WHERE report_id = $r_id");
}

$reports = $conn->query("SELECT r.*, p.title, p.product_id FROM reports r JOIN products p ON r.product_id = p.product_id");
?>

<div class="container">
    <div class="panel-card">
        <h2>Reported Content Queue</h2>
        <table class="admin-table-view">
            <thead>
                <tr>
                    <th>Product Title</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $reports->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td>
                        <a href="reports.php?action=delete&id=<?php echo $row['report_id']; ?>" style="color: red;">Delete Product</a> | 
                        <a href="reports.php?action=dismiss&id=<?php echo $row['report_id']; ?>">Dismiss</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>