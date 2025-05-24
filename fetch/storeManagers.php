<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../mail/Mailer.php';

use Ulid\Ulid;
use ZzimbaOnline\Mail\Mailer;

header('Content-Type: application/json');

$isLoggedIn = isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'];
$currentUser = $isLoggedIn ? $_SESSION['user']['user_id'] : null;

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getStoreManagers':
            getStoreManagers($pdo, $_GET['store_id'] ?? null, $currentUser);
            break;

        case 'inviteManager':
            requireLogin();
            inviteManager($pdo, $currentUser);
            break;

        case 'updateManagerStatus':
            requireLogin();
            updateManagerStatus($pdo, $currentUser);
            break;

        case 'updateManagerRole':
            requireLogin();
            updateManagerRole($pdo, $currentUser);
            break;

        case 'removeManager':
            requireLogin();
            removeManager($pdo, $currentUser);
            break;

        case 'checkEmailAvailability':
            requireLogin();
            checkEmailAvailability($pdo, $_GET['email'] ?? '', $_GET['store_id'] ?? '', $currentUser);
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);
            break;
    }
} catch (Exception $e) {
    error_log('Error in storeManagers.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

ob_end_flush();

// -----------------------------------------------------------------------------
// HELPER FUNCTIONS
// -----------------------------------------------------------------------------
function requireLogin()
{
    if (empty($_SESSION['user']['logged_in'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required', 'session_expired' => true]);
        exit;
    }
}

function isValidUlid(string $id): bool
{
    return (bool) preg_match('/^[0-9A-Z]{26}$/i', $id);
}

function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// -----------------------------------------------------------------------------
// STORE OWNERSHIP HELPERS
// -----------------------------------------------------------------------------
function isStoreOwner(PDO $pdo, string $storeId, ?string $userId): bool
{
    if (!$userId)
        return false;
    $stmt = $pdo->prepare("SELECT 1 FROM vendor_stores WHERE id = ? AND owner_id = ? LIMIT 1");
    $stmt->execute([$storeId, $userId]);
    return (bool) $stmt->fetchColumn();
}

function canManageStore(PDO $pdo, string $storeId, ?string $userId): bool
{
    if (!$userId)
        return false;
    if (isStoreOwner($pdo, $storeId, $userId)) {
        return true;
    }
    $stmt = $pdo->prepare("SELECT 1 FROM store_managers WHERE store_id = ? AND user_id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$storeId, $userId]);
    return (bool) $stmt->fetchColumn();
}

// -----------------------------------------------------------------------------
// EMAIL NOTIFICATION FUNCTIONS
// -----------------------------------------------------------------------------
function sendManagerInvitationEmail(string $email, string $firstName, string $lastName, string $storeName, string $role): bool
{
    $roleLabels = [
        'manager' => 'Manager (Full Access)',
        'inventory_manager' => 'Inventory Manager',
        'sales_manager' => 'Sales Manager',
        'content_manager' => 'Content Manager'
    ];

    $roleName = $roleLabels[$role] ?? $role;
    $subject = "You've Been Invited to Manage a Store - Zzimba Online";

    $content = '
        <div style="padding:20px 0;">
            <h2>Store Manager Invitation</h2>
            <p>Hello ' . htmlspecialchars($firstName . ' ' . $lastName) . ',</p>
            <p>You have been invited to manage the store <strong>' . htmlspecialchars($storeName) . '</strong> on Zzimba Online.</p>
            
            <div style="margin:20px 0;padding:15px;background-color:#f5f5f5;border-radius:5px;">
                <h3 style="margin-top:0;color:#D92B13;">Role Details:</h3>
                <p><strong>Role:</strong> ' . htmlspecialchars($roleName) . '</p>
            </div>
            
            <h3>Next Steps:</h3>
            <ol style="line-height:1.8;">
                <li>Log in to your Zzimba Online account using your email address</li>
                <li>Navigate to the "Zzimba Stores" section in your dashboard</li>
                <li>Find the invitation and either approve or deny the request</li>
            </ol>
            
            <p>If you don\'t have a Zzimba Online account yet, please register first using this email address.</p>
            
            <div style="margin:20px 0;text-align:center;">
                <a href="https://zzimbaonline.com/login" style="display:inline-block;padding:12px 24px;background-color:#D92B13;color:#ffffff;text-decoration:none;font-weight:500;border-radius:4px;">
                    Log In to Zzimba Online
                </a>
            </div>
            
            <p>If you have any questions or did not expect this invitation, please contact our support team.</p>
        </div>';

    return Mailer::sendMail($email, $subject, $content);
}

function sendManagerStatusChangeEmail(string $email, string $firstName, string $lastName, string $storeName, string $newStatus): bool
{
    $statusText = $newStatus === 'active' ? 'activated' : 'deactivated';
    $statusColor = $newStatus === 'active' ? '#10B981' : '#F59E0B';

    $subject = "Your Store Manager Status Has Changed - Zzimba Online";

    $content = '
        <div style="padding:20px 0;">
            <h2>Store Manager Status Update</h2>
            <p>Hello ' . htmlspecialchars($firstName . ' ' . $lastName) . ',</p>
            <p>Your status as a manager for the store <strong>' . htmlspecialchars($storeName) . '</strong> has been updated.</p>
            
            <div style="margin:20px 0;padding:15px;background-color:#f5f5f5;border-radius:5px;text-align:center;">
                <h3 style="margin-top:0;">Your account has been <span style="color:' . $statusColor . ';">' . $statusText . '</span></h3>
                <p>New status: <strong style="color:' . $statusColor . ';">' . ucfirst($newStatus) . '</strong></p>
            </div>';

    if ($newStatus === 'active') {
        $content .= '
            <p>You now have access to manage the store according to your assigned role. You can log in to your account and access the store management features.</p>
            
            <div style="margin:20px 0;text-align:center;">
                <a href="https://zzimbaonline.com/login" style="display:inline-block;padding:12px 24px;background-color:#D92B13;color:#ffffff;text-decoration:none;font-weight:500;border-radius:4px;">
                    Log In to Manage Store
                </a>
            </div>';
    } else {
        $content .= '
            <p>Your access to manage this store has been temporarily suspended. If you believe this is an error, please contact the store owner.</p>';
    }

    $content .= '
            <p>If you have any questions about this change, please contact the store owner or our support team.</p>
        </div>';

    return Mailer::sendMail($email, $subject, $content);
}

function sendManagerRoleChangeEmail(string $email, string $firstName, string $lastName, string $storeName, string $newRole): bool
{
    $roleLabels = [
        'manager' => 'Manager (Full Access)',
        'inventory_manager' => 'Inventory Manager',
        'sales_manager' => 'Sales Manager',
        'content_manager' => 'Content Manager'
    ];

    $roleName = $roleLabels[$newRole] ?? $newRole;
    $subject = "Your Store Manager Role Has Changed - Zzimba Online";

    $content = '
        <div style="padding:20px 0;">
            <h2>Store Manager Role Update</h2>
            <p>Hello ' . htmlspecialchars($firstName . ' ' . $lastName) . ',</p>
            <p>Your role as a manager for the store <strong>' . htmlspecialchars($storeName) . '</strong> has been updated.</p>
            
            <div style="margin:20px 0;padding:15px;background-color:#f5f5f5;border-radius:5px;">
                <h3 style="margin-top:0;color:#D92B13;">New Role Details:</h3>
                <p><strong>Role:</strong> ' . htmlspecialchars($roleName) . '</p>
            </div>
            
            <p>This change may affect your permissions and responsibilities within the store management system. Please log in to your account to see the updated access and features available to you.</p>
            
            <div style="margin:20px 0;text-align:center;">
                <a href="https://zzimbaonline.com/login" style="display:inline-block;padding:12px 24px;background-color:#D92B13;color:#ffffff;text-decoration:none;font-weight:500;border-radius:4px;">
                    Log In to Zzimba Online
                </a>
            </div>
            
            <p>If you have any questions about your new role or responsibilities, please contact the store owner or our support team.</p>
        </div>';

    return Mailer::sendMail($email, $subject, $content);
}

function sendManagerRemovalEmail(string $email, string $firstName, string $lastName, string $storeName): bool
{
    $subject = "You Have Been Removed as Store Manager - Zzimba Online";

    $content = '
        <div style="padding:20px 0;">
            <h2>Store Manager Removal Notice</h2>
            <p>Hello ' . htmlspecialchars($firstName . ' ' . $lastName) . ',</p>
            <p>This email is to inform you that you have been removed as a manager for the store <strong>' . htmlspecialchars($storeName) . '</strong> on Zzimba Online.</p>
            
            <div style="margin:20px 0;padding:15px;background-color:#f5f5f5;border-radius:5px;text-align:center;">
                <p style="font-size:18px;color:#4B5563;">You no longer have access to manage this store.</p>
            </div>
            
            <p>If you believe this action was taken in error or have any questions, please contact the store owner or our support team.</p>
            
            <p>Thank you for your contributions to the store management.</p>
        </div>';

    return Mailer::sendMail($email, $subject, $content);
}

// -----------------------------------------------------------------------------
// GET STORE MANAGERS
// -----------------------------------------------------------------------------
function getStoreManagers(PDO $pdo, ?string $storeId, ?string $currentUserId)
{
    if (!$storeId || !isValidUlid($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store ID']);
        return;
    }

    if (!canManageStore($pdo, $storeId, $currentUserId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        return;
    }

    try {
        $storeStmt = $pdo->prepare("SELECT name FROM vendor_stores WHERE id = ?");
        $storeStmt->execute([$storeId]);
        $storeName = $storeStmt->fetchColumn();

        if (!$storeName) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Store not found']);
            return;
        }

        $ownerStmt = $pdo->prepare("SELECT owner_id FROM vendor_stores WHERE id = ?");
        $ownerStmt->execute([$storeId]);
        $ownerId = $ownerStmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT 
                sm.id,
                sm.user_id,
                sm.role,
                sm.status,
                sm.approved,
                sm.created_at,
                sm.updated_at,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                u.status AS user_status,
                (sm.user_id = ?) AS is_owner
            FROM store_managers sm
            JOIN zzimba_users u ON sm.user_id = u.id
            WHERE sm.store_id = ? AND sm.status != 'removed'
            ORDER BY sm.created_at DESC
        ");
        $stmt->execute([$ownerId, $storeId]);
        $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'managers' => $managers,
            'is_owner' => isStoreOwner($pdo, $storeId, $currentUserId),
            'store_name' => $storeName
        ]);
    } catch (Exception $e) {
        error_log('Error getting store managers: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving store managers']);
    }
}

// -----------------------------------------------------------------------------
// CHECK EMAIL AVAILABILITY
// -----------------------------------------------------------------------------
function checkEmailAvailability(PDO $pdo, string $email, string $storeId, ?string $currentUserId)
{
    if (!$email || !isValidEmail($email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid email format']);
        return;
    }

    if (!$storeId || !isValidUlid($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store ID']);
        return;
    }

    if (!canManageStore($pdo, $storeId, $currentUserId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        return;
    }

    try {
        $userStmt = $pdo->prepare("SELECT id, first_name, last_name, status FROM zzimba_users WHERE email = ?");
        $userStmt->execute([$email]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode([
                'success' => false,
                'error' => 'User with this email does not exist',
                'code' => 'user_not_found'
            ]);
            return;
        }

        if ($user['status'] !== 'active') {
            echo json_encode([
                'success' => false,
                'error' => 'User account is not active',
                'code' => 'user_inactive'
            ]);
            return;
        }

        $ownerStmt = $pdo->prepare("SELECT 1 FROM vendor_stores WHERE id = ? AND owner_id = ?");
        $ownerStmt->execute([$storeId, $user['id']]);
        if ($ownerStmt->fetchColumn()) {
            echo json_encode([
                'success' => false,
                'error' => 'This user is the store owner',
                'code' => 'is_owner'
            ]);
            return;
        }

        $managerStmt = $pdo->prepare("SELECT status FROM store_managers WHERE store_id = ? AND user_id = ? AND status != 'removed'");
        $managerStmt->execute([$storeId, $user['id']]);
        $managerStatus = $managerStmt->fetchColumn();

        if ($managerStatus) {
            echo json_encode([
                'success' => false,
                'error' => 'This user is already a manager for this store',
                'code' => 'already_manager',
                'status' => $managerStatus
            ]);
            return;
        }

        $removedManagerStmt = $pdo->prepare("SELECT 1 FROM store_managers WHERE store_id = ? AND user_id = ? AND status = 'removed'");
        $removedManagerStmt->execute([$storeId, $user['id']]);
        $wasRemoved = (bool) $removedManagerStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name']
            ],
            'was_removed' => $wasRemoved
        ]);
    } catch (Exception $e) {
        error_log('Error checking email availability: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error checking email availability']);
    }
}

// -----------------------------------------------------------------------------
// INVITE MANAGER
// -----------------------------------------------------------------------------
function inviteManager(PDO $pdo, string $currentUser)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['store_id']) || empty($data['email']) || empty($data['role'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }

    $storeId = $data['store_id'];
    $email = $data['email'];
    $role = $data['role'];
    $reinvite = isset($data['reinvite']) ? (bool) $data['reinvite'] : false;

    if (!isValidUlid($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store ID']);
        return;
    }

    if (!isValidEmail($email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid email format']);
        return;
    }

    if (!in_array($role, ['manager', 'inventory_manager', 'sales_manager', 'content_manager'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid role']);
        return;
    }

    if (!isStoreOwner($pdo, $storeId, $currentUser)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Only store owners can add managers']);
        return;
    }

    try {
        $pdo->beginTransaction();

        $storeStmt = $pdo->prepare("SELECT name FROM vendor_stores WHERE id = ?");
        $storeStmt->execute([$storeId]);
        $storeName = $storeStmt->fetchColumn();

        if (!$storeName) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Store not found']);
            return;
        }

        $userStmt = $pdo->prepare("SELECT id, first_name, last_name, status FROM zzimba_users WHERE email = ?");
        $userStmt->execute([$email]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'error' => 'User with this email does not exist',
                'code' => 'user_not_found'
            ]);
            return;
        }

        if ($user['status'] !== 'active') {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'error' => 'User account is not active',
                'code' => 'user_inactive'
            ]);
            return;
        }

        $userId = $user['id'];

        $ownerStmt = $pdo->prepare("SELECT 1 FROM vendor_stores WHERE id = ? AND owner_id = ?");
        $ownerStmt->execute([$storeId, $userId]);
        if ($ownerStmt->fetchColumn()) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'error' => 'Cannot add store owner as a manager',
                'code' => 'is_owner'
            ]);
            return;
        }

        $managerStmt = $pdo->prepare("SELECT id, status FROM store_managers WHERE store_id = ? AND user_id = ? AND status != 'removed'");
        $managerStmt->execute([$storeId, $userId]);
        $existingManager = $managerStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingManager) {
            if ($existingManager['status'] === 'active') {
                $pdo->rollBack();
                echo json_encode([
                    'success' => false,
                    'error' => 'This user is already an active manager for this store',
                    'code' => 'already_active'
                ]);
                return;
            } else {
                $updateStmt = $pdo->prepare("
                UPDATE store_managers 
                SET status = 'inactive', role = ?, updated_at = NOW() 
                WHERE id = ?
            ");
                $updateStmt->execute([$role, $existingManager['id']]);

                $managerId = $existingManager['id'];
            }
        } else {
            $removedManagerStmt = $pdo->prepare("SELECT id FROM store_managers WHERE store_id = ? AND user_id = ? AND status = 'removed'");
            $removedManagerStmt->execute([$storeId, $userId]);
            $removedManager = $removedManagerStmt->fetch(PDO::FETCH_ASSOC);

            if ($removedManager && !$reinvite) {
                $pdo->rollBack();
                echo json_encode([
                    'success' => false,
                    'error' => 'This user was previously removed as a manager',
                    'code' => 'previously_removed',
                    'user_info' => [
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name']
                    ]
                ]);
                return;
            }

            if ($removedManager && $reinvite) {
                $updateStmt = $pdo->prepare("
                UPDATE store_managers 
                SET status = 'inactive', role = ?, approved = 0, updated_at = NOW(), created_at = NOW() 
                WHERE id = ?
            ");
                $updateStmt->execute([$role, $removedManager['id']]);
                $managerId = $removedManager['id'];
            } else {
                $managerId = generateUlid();
                $insertStmt = $pdo->prepare("
                INSERT INTO store_managers 
                    (id, store_id, user_id, role, status, added_by, approved, created_at, updated_at)
                VALUES 
                    (?, ?, ?, ?, 'inactive', ?, 0, NOW(), NOW())
            ");
                $insertStmt->execute([$managerId, $storeId, $userId, $role, $currentUser]);
            }
        }

        $emailSent = sendManagerInvitationEmail(
            $email,
            $user['first_name'],
            $user['last_name'],
            $storeName,
            $role
        );

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Manager invitation sent successfully',
            'manager_id' => $managerId,
            'email_sent' => $emailSent
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error inviting manager: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error inviting manager']);
    }
}

// -----------------------------------------------------------------------------
// UPDATE MANAGER STATUS
// -----------------------------------------------------------------------------
function updateManagerStatus(PDO $pdo, string $currentUser)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['store_id']) || empty($data['manager_id']) || !isset($data['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }

    $storeId = $data['store_id'];
    $managerId = $data['manager_id'];
    $newStatus = $data['status'];

    if (!isValidUlid($storeId) || !isValidUlid($managerId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid ID format']);
        return;
    }

    if (!in_array($newStatus, ['active', 'inactive'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
        return;
    }

    if (!isStoreOwner($pdo, $storeId, $currentUser)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Only store owners can update manager status']);
        return;
    }

    try {
        $storeStmt = $pdo->prepare("SELECT name FROM vendor_stores WHERE id = ?");
        $storeStmt->execute([$storeId]);
        $storeName = $storeStmt->fetchColumn();

        if (!$storeName) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Store not found']);
            return;
        }

        $checkStmt = $pdo->prepare("
        SELECT sm.id, sm.status, sm.user_id, u.first_name, u.last_name, u.email 
        FROM store_managers sm
        JOIN zzimba_users u ON sm.user_id = u.id
        WHERE sm.id = ? AND sm.store_id = ? AND sm.status != 'removed'
    ");
        $checkStmt->execute([$managerId, $storeId]);
        $manager = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$manager) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Manager not found']);
            return;
        }

        if ($manager['status'] === $newStatus) {
            echo json_encode([
                'success' => true,
                'message' => 'Manager status is already ' . $newStatus,
                'manager' => [
                    'id' => $managerId,
                    'status' => $newStatus,
                    'name' => $manager['first_name'] . ' ' . $manager['last_name']
                ]
            ]);
            return;
        }

        $updateStmt = $pdo->prepare("
        UPDATE store_managers 
        SET status = ?, updated_at = NOW() 
        WHERE id = ?
    ");
        $updateStmt->execute([$newStatus, $managerId]);

        $emailSent = sendManagerStatusChangeEmail(
            $manager['email'],
            $manager['first_name'],
            $manager['last_name'],
            $storeName,
            $newStatus
        );

        echo json_encode([
            'success' => true,
            'message' => 'Manager status updated successfully',
            'manager' => [
                'id' => $managerId,
                'status' => $newStatus,
                'name' => $manager['first_name'] . ' ' . $manager['last_name']
            ],
            'email_sent' => $emailSent
        ]);
    } catch (Exception $e) {
        error_log('Error updating manager status: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error updating manager status']);
    }
}

// -----------------------------------------------------------------------------
// UPDATE MANAGER ROLE
// -----------------------------------------------------------------------------
function updateManagerRole(PDO $pdo, string $currentUser)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['store_id']) || empty($data['manager_id']) || empty($data['role'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }

    $storeId = $data['store_id'];
    $managerId = $data['manager_id'];
    $newRole = $data['role'];

    if (!isValidUlid($storeId) || !isValidUlid($managerId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid ID format']);
        return;
    }

    if (!in_array($newRole, ['manager', 'inventory_manager', 'sales_manager', 'content_manager'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid role']);
        return;
    }

    if (!isStoreOwner($pdo, $storeId, $currentUser)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Only store owners can update manager roles']);
        return;
    }

    try {
        $storeStmt = $pdo->prepare("SELECT name FROM vendor_stores WHERE id = ?");
        $storeStmt->execute([$storeId]);
        $storeName = $storeStmt->fetchColumn();

        if (!$storeName) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Store not found']);
            return;
        }

        $checkStmt = $pdo->prepare("
            SELECT sm.id, sm.role, sm.user_id, u.first_name, u.last_name, u.email 
            FROM store_managers sm
            JOIN zzimba_users u ON sm.user_id = u.id
            WHERE sm.id = ? AND sm.store_id = ? AND sm.status != 'removed'
        ");
        $checkStmt->execute([$managerId, $storeId]);
        $manager = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$manager) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Manager not found']);
            return;
        }

        if ($manager['role'] === $newRole) {
            echo json_encode([
                'success' => true,
                'message' => 'Manager role is already ' . $newRole,
                'manager' => [
                    'id' => $managerId,
                    'role' => $newRole,
                    'name' => $manager['first_name'] . ' ' . $manager['last_name']
                ]
            ]);
            return;
        }

        $updateStmt = $pdo->prepare("
            UPDATE store_managers 
            SET role = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $updateStmt->execute([$newRole, $managerId]);

        $emailSent = sendManagerRoleChangeEmail(
            $manager['email'],
            $manager['first_name'],
            $manager['last_name'],
            $storeName,
            $newRole
        );

        echo json_encode([
            'success' => true,
            'message' => 'Manager role updated successfully',
            'manager' => [
                'id' => $managerId,
                'role' => $newRole,
                'name' => $manager['first_name'] . ' ' . $manager['last_name']
            ],
            'email_sent' => $emailSent
        ]);
    } catch (Exception $e) {
        error_log('Error updating manager role: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error updating manager role']);
    }
}

// -----------------------------------------------------------------------------
// REMOVE MANAGER
// -----------------------------------------------------------------------------
function removeManager(PDO $pdo, string $currentUser)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['store_id']) || empty($data['manager_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }

    $storeId = $data['store_id'];
    $managerId = $data['manager_id'];

    if (!isValidUlid($storeId) || !isValidUlid($managerId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid ID format']);
        return;
    }

    if (!isStoreOwner($pdo, $storeId, $currentUser)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Only store owners can remove managers']);
        return;
    }

    try {
        $storeStmt = $pdo->prepare("SELECT name FROM vendor_stores WHERE id = ?");
        $storeStmt->execute([$storeId]);
        $storeName = $storeStmt->fetchColumn();

        if (!$storeName) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Store not found']);
            return;
        }

        $checkStmt = $pdo->prepare("
            SELECT sm.id, sm.user_id, u.first_name, u.last_name, u.email 
            FROM store_managers sm
            JOIN zzimba_users u ON sm.user_id = u.id
            WHERE sm.id = ? AND sm.store_id = ? AND sm.status != 'removed'
        ");
        $checkStmt->execute([$managerId, $storeId]);
        $manager = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$manager) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Manager not found']);
            return;
        }

        $updateStmt = $pdo->prepare("
            UPDATE store_managers 
            SET status = 'removed', updated_at = NOW() 
            WHERE id = ?
        ");
        $updateStmt->execute([$managerId]);

        $emailSent = sendManagerRemovalEmail(
            $manager['email'],
            $manager['first_name'],
            $manager['last_name'],
            $storeName
        );

        echo json_encode([
            'success' => true,
            'message' => 'Manager removed successfully',
            'manager' => [
                'id' => $managerId,
                'name' => $manager['first_name'] . ' ' . $manager['last_name']
            ],
            'email_sent' => $emailSent
        ]);
    } catch (Exception $e) {
        error_log('Error removing manager: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error removing manager']);
    }
}
