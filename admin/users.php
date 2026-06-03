<?php
include '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- DYNAMICALLY RESOLVE USER SCHEMA COLUMNS TO PREVENT SQL CRASHES ---
$user_pk_column = "id";       // Default fallback
$role_column    = "role";     // Default fallback
$has_created_at = false;      // Flag to track timestamp existence

$columns_check = $conn->query("SHOW COLUMNS FROM users");
if ($columns_check) {
    while ($col = $columns_check->fetch_assoc()) {
        $field_name = strtolower($col['Field']);
        if ($field_name === 'user_id') {
            $user_pk_column = "user_id";
        } elseif ($field_name === 'role_id') {
            $role_column = "role_id";
        } elseif ($field_name === 'user_type') {
            $role_column = "user_type";
        } elseif ($field_name === 'created_at') {
            $has_created_at = true;
        }
    }
}

// Handle safe profile removals using the dynamically resolved primary key
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['terminate_user_id'])) {
    $target_user = intval($_POST['terminate_user_id']);
    
    // Prevent self-deletion lockouts by evaluating session user identity flags
    if ($target_user !== intval($_SESSION['user_id'])) {
        $drop_stmt = $conn->prepare("DELETE FROM users WHERE $user_pk_column = ? AND $role_column != 3");
        $drop_stmt->bind_param("i", $target_user);
        $drop_stmt->execute();
        $drop_stmt->close();
    }
    header("Location: users.php");
    exit;
}

// Build the SELECT fields string based on column existence matching arrays
$select_fields = "$user_pk_column, username, email, $role_column";
if ($has_created_at) {
    $select_fields .= ", created_at";
}

// Fetch general platform membership layers using the dynamic schema configurations
$users_result = $conn->query("SELECT $select_fields FROM users WHERE $role_column != 3 AND LOWER($role_column) != 'admin' ORDER BY $user_pk_column DESC");

include 'includes/admin_header.php';
?>

<div class="container">
    <div class="panel-card">
        <div style="border-bottom: 1px solid #edf2f7; padding-bottom: 15px; margin-bottom: 25px;">
            <h2 style="font-weight: 800; color: #0f172a; font-size: 1.4rem;"><i class="bi bi-people-fill" style="color: #4338ca; margin-right: 6px;"></i> User Account Management Registry</h2>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 4px;">Monitor account activations and audit active platform user registration maps.</p>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table-view">
                <thead>
                    <tr>
                        <th>Account ID</th>
                        <th>Username Identity</th>
                        <th>Email Contact Field</th>
                        <th>Account Scope Group</th>
                        <th>Registration Date</th>
                        <th style="text-align: center;">Account Status Controls</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users_result && $users_result->num_rows > 0): ?>
                        <?php while ($row = $users_result->fetch_assoc()): ?>
                            <?php 
                                $uid  = $row[$user_pk_column];
                                $u_role = $row[$role_column];
                                
                                // Map roles explicitly for clean presentation output
                                $role_label = "Member";
                                if ($u_role == 2 || strtolower($u_role) === 'seller') {
                                    $role_label = "Seller";
                                }
                            ?>
                            <tr>
                                <td style="font-weight: 700; color: #64748b;">#<?php echo $uid; ?></td>
                                <td style="font-weight: 700; color: #1e293b;">@<?php echo htmlspecialchars($row['username']); ?></td>
                                <td style="font-weight: 500; color: #475569;"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <span style="background-color: #e0e7ff; color: #4338ca; padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem; text-transform: uppercase;">
                                        <?php echo $role_label; ?>
                                    </span>
                                </td>
                                <td style="color: #64748b; font-size: 0.9rem;">
                                    <?php 
                                    if ($has_created_at && !empty($row['created_at'])) {
                                        echo date('d M Y, H:i', strtotime($row['created_at']));
                                    } else {
                                        echo '<span style="color: #94a3b8; font-style: italic;">Active</span>';
                                    }
                                    ?>
                                </td>
                                <td style="text-align: center;">
                                    <form action="users.php" method="POST" onsubmit="return confirm('Completely delete this account record? This will purge all associated marketplace inventory assets.');">
                                        <input type="hidden" name="terminate_user_id" value="<?php echo $uid; ?>">
                                        <button type="submit" style="background: none; border: 1px solid #fee2e2; background-color: #fff5f5; color: #dc3545; padding: 6px 14px; border-radius: 6px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;">
                                            <i class="bi bi-person-x-fill"></i> Terminate Account
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="padding: 40px; text-align: center; color: #94a3b8; font-weight: 600;">
                                No external member account entries are currently mapped into the database index layers.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>