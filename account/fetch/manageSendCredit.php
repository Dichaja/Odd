<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'searchWallet':
        handleSearchWallet($pdo);
        break;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
        exit;
}

function handleSearchWallet(PDO $pdo)
{
    $type = $_POST['type'] ?? '';
    $searchType = $_POST['searchType'] ?? '';
    $searchValue = $_POST['searchValue'] ?? '';

    // Validate inputs
    if (
        !in_array($type, ['vendor', 'user'], true)
        || !in_array($searchType, ['id', 'name'], true)
        || $searchValue === ''
    ) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid parameters'
        ]);
        return;
    }

    try {
        $wallet = fetchWallet($pdo, $type, $searchType, $searchValue);

        if ($wallet) {
            echo json_encode([
                'success' => true,
                'wallet' => $wallet
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => ucfirst($type) . ' wallet not found'
            ]);
        }
    } catch (PDOException $e) {
        error_log('Error searching wallet: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error'
        ]);
    }
}

/**
 * Fetch a single wallet by exact ID or name, filtered by owner_type.
 *
 * @param PDO    $pdo
 * @param string $type       'vendor' or 'user'
 * @param string $by         'id' or 'name'
 * @param string $val        search value (exact match)
 * @return array|null
 */
function fetchWallet(PDO $pdo, string $type, string $by, string $val): ?array
{
    $ownerType = $type === 'vendor' ? 'VENDOR' : 'USER';
    $col = $by === 'id' ? 'wallet_id' : 'wallet_name';

    $sql = "SELECT wallet_id, wallet_name
             FROM zzimba_wallets
             WHERE owner_type = :owner
               AND {$col} = :value
             LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'owner' => $ownerType,
        'value' => $val
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}
