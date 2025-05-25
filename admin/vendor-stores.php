<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Manage Vendors';
$activeNav = 'vendor-stores';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'load_vendors':
                $page = intval($_POST['page'] ?? 1);
                $limit = intval($_POST['limit'] ?? 10);
                $search = trim($_POST['search'] ?? '');
                $category = trim($_POST['category'] ?? '');
                $status = trim($_POST['status'] ?? '');

                $offset = ($page - 1) * $limit;

                $whereConditions = [];
                $params = [];

                if (!empty($search)) {
                    $whereConditions[] = "(vs.name LIKE ? OR vs.business_email LIKE ? OR vs.business_phone LIKE ? OR vs.contact_person_name LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
                    $searchTerm = "%$search%";
                    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
                }

                if (!empty($category)) {
                    $whereConditions[] = "nob.name = ?";
                    $params[] = $category;
                }

                if (!empty($status)) {
                    $whereConditions[] = "vs.status = ?";
                    $params[] = $status;
                }

                $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

                $countQuery = "SELECT COUNT(*) as total FROM vendor_stores vs 
                              LEFT JOIN zzimba_users u ON vs.owner_id = u.id 
                              LEFT JOIN nature_of_business nob ON vs.nature_of_business = nob.id 
                              $whereClause";

                $stmt = $pdo->prepare($countQuery);
                $stmt->execute($params);
                $totalCount = $stmt->fetch()['total'];

                $query = "SELECT vs.*, 
                                CONCAT(u.first_name, ' ', u.last_name) as owner_name,
                                nob.name as nature_of_business,
                                CONCAT(vs.region, ', ', vs.district, ', ', vs.subcounty) as full_address,
                                (SELECT COUNT(*) FROM store_products sp 
                                 JOIN store_categories sc ON sp.store_category_id = sc.id 
                                 WHERE sc.store_id = vs.id AND sp.status = 'active') as product_count
                         FROM vendor_stores vs 
                         LEFT JOIN zzimba_users u ON vs.owner_id = u.id 
                         LEFT JOIN nature_of_business nob ON vs.nature_of_business = nob.id 
                         $whereClause 
                         ORDER BY vs.created_at DESC 
                         LIMIT ? OFFSET ?";

                $params[] = $limit;
                $params[] = $offset;

                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $categoriesQuery = "SELECT DISTINCT name FROM nature_of_business WHERE status = 'active' ORDER BY name";
                $categoriesStmt = $pdo->prepare($categoriesQuery);
                $categoriesStmt->execute();
                $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

                $totalPages = ceil($totalCount / $limit);
                $start = $offset + 1;
                $end = min($offset + $limit, $totalCount);

                echo json_encode([
                    'success' => true,
                    'vendors' => $vendors,
                    'categories' => $categories,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => $totalPages,
                        'total' => $totalCount,
                        'start' => $start,
                        'end' => $end
                    ]
                ]);
                exit;

            case 'update_status':
                $storeId = $_POST['store_id'] ?? '';
                $newStatus = $_POST['status'] ?? '';

                if (empty($storeId) || empty($newStatus)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
                    exit;
                }

                $allowedStatuses = ['active', 'pending', 'inactive', 'suspended'];
                if (!in_array($newStatus, $allowedStatuses)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid status']);
                    exit;
                }

                $updateQuery = "UPDATE vendor_stores SET status = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $pdo->prepare($updateQuery);

                if ($stmt->execute([$newStatus, $storeId])) {
                    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
                }
                exit;
        }
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

ob_start();
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Manage Vendors</h1>
            <p class="text-sm text-gray-text mt-1">View, verify and manage all vendor accounts</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <button id="sendNotificationBtn"
                class="h-10 px-4 bg-success text-white rounded-lg hover:bg-success/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-bell"></i>
                <span>Send Notification</span>
            </button>
            <a href="nature-of-business"
                class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-tags"></i>
                <span>Nature of Business</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">Vendor List</h2>
                <p class="text-sm text-gray-text mt-1">
                    <span id="vendor-count">0</span> vendors found
                </p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchVendors" placeholder="Search vendors..."
                        class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
        </div>

        <div id="filterPanel" class="px-6 py-4 border-b border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="filterCategory" class="block text-sm font-medium text-gray-700 mb-1">Vendor
                        Category</label>
                    <select id="filterCategory"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div>
                    <label for="filterStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="filterStatus"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button id="resetFilters"
                        class="h-10 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors mr-2">
                        Reset
                    </button>
                    <button id="applyFilters"
                        class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        Apply
                    </button>
                </div>
            </div>
        </div>

        <div id="vendors-list" class="divide-y divide-gray-100">
        </div>

        <div id="loading-state" class="p-8 text-center">
            <div
                class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-gray-500 bg-white">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                Loading vendors...
            </div>
        </div>

        <div id="empty-state" class="p-8 text-center hidden">
            <div class="text-gray-500">
                <i class="fas fa-store text-4xl mb-4"></i>
                <p class="text-lg font-medium">No vendors found</p>
                <p class="text-sm">Try adjusting your search or filter criteria</p>
            </div>
        </div>

        <div id="pagination-container"
            class="p-6 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center hidden">
            <div class="text-sm text-gray-500 mb-4 md:mb-0">
                Showing <span id="showing-start">0</span> to <span id="showing-end">0</span> of <span
                    id="total-vendors">0</span> vendors
            </div>
            <div class="flex items-center gap-2">
                <button id="prev-page"
                    class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination-numbers" class="flex items-center gap-1">
                </div>
                <button id="next-page"
                    class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="statusModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideStatusModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Update Store Status</h3>
            <button onclick="hideStatusModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div id="status-store-info" class="mb-4">
            </div>
            <div class="space-y-3">
                <label class="flex items-center">
                    <input type="radio" name="new-status" value="active" class="form-radio h-4 w-4 text-primary">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="new-status" value="pending" class="form-radio h-4 w-4 text-primary">
                    <span class="ml-2 text-sm text-gray-700">Pending</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="new-status" value="inactive" class="form-radio h-4 w-4 text-primary">
                    <span class="ml-2 text-sm text-gray-700">Inactive</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="new-status" value="suspended" class="form-radio h-4 w-4 text-primary">
                    <span class="ml-2 text-sm text-gray-700">Suspended</span>
                </label>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideStatusModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="updateStatus" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                Update Status
            </button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    const BASE_URL = "<?= BASE_URL ?>";
    let currentPage = 1;
    let totalPages = 1;
    let currentStoreId = null;
    const itemsPerPage = 10;

    function truncateText(text, maxLength = 50) {
        if (!text || text.length <= maxLength) return text || 'Not provided';
        return text.substring(0, maxLength) + '...';
    }

    function getStatusBadgeClass(status) {
        switch (status) {
            case 'active':
                return 'bg-green-100 text-green-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'inactive':
                return 'bg-gray-100 text-gray-800';
            case 'suspended':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    function formatDate(dateString) {
        if (!dateString) return 'Not available';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: '2-digit'
        });
    }

    async function loadVendors(page = 1, search = '', category = '', status = '') {
        try {
            showLoading();

            const formData = new FormData();
            formData.append('action', 'load_vendors');
            formData.append('page', page);
            formData.append('limit', itemsPerPage);
            formData.append('search', search);
            formData.append('category', category);
            formData.append('status', status);

            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                displayVendors(data.vendors);
                updatePagination(data.pagination);
                updateVendorCount(data.pagination.total);
                loadCategories(data.categories);
            } else {
                showError(data.message || 'Failed to load vendors');
            }
        } catch (error) {
            console.error('Error loading vendors:', error);
            showError('Failed to load vendors. Please try again.');
        } finally {
            hideLoading();
        }
    }

    function displayVendors(vendors) {
        const container = document.getElementById('vendors-list');

        if (!vendors || vendors.length === 0) {
            container.innerHTML = '';
            showEmptyState();
            return;
        }

        hideEmptyState();

        container.innerHTML = vendors.map(vendor => `
        <div class="vendor-item p-6" data-vendor-id="${vendor.id}">
            <div class="flex flex-col lg:flex-row gap-4">
                <div class="flex-1">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-lg font-semibold text-secondary" title="${vendor.name}">
                                ${truncateText(vendor.name, 40)}
                            </h3>
                            ${vendor.nature_of_business ? `
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    ${vendor.nature_of_business}
                                </span>
                            ` : ''}
                            <button onclick="showStatusModal('${vendor.id}', '${vendor.name}', '${vendor.status}')" 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer hover:opacity-80 transition-opacity ${getStatusBadgeClass(vendor.status)}">
                                ${vendor.status.charAt(0).toUpperCase() + vendor.status.slice(1)}
                                <i class="fas fa-edit ml-1"></i>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm mb-4">
                        <div>
                            <span class="text-gray-500">Contact Person:</span>
                            <p class="font-medium" title="${vendor.contact_person_name}">
                                ${truncateText(vendor.contact_person_name, 25)}
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-500">Business Email:</span>
                            <p class="font-medium" title="${vendor.business_email}">
                                ${truncateText(vendor.business_email, 25)}
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-500">Business Phone:</span>
                            <p class="font-medium">
                                ${vendor.business_phone || 'Not provided'}
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-500">Location:</span>
                            <p class="font-medium" title="${vendor.full_address}">
                                ${truncateText(vendor.full_address, 30)}
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-500">Owner:</span>
                            <p class="font-medium" title="${vendor.owner_name}">
                                ${truncateText(vendor.owner_name, 25)}
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-500">Registered:</span>
                            <p class="font-medium">${formatDate(vendor.created_at)}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Store ID:</span>
                            <p class="font-medium">${vendor.vendor_id}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Products:</span>
                            <p class="font-medium">${vendor.product_count || 0}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button onclick="window.open('${BASE_URL}view/profile/vendor/${vendor.id}', '_blank')"
                                class="h-8 px-3 bg-white border border-gray-300 text-gray-700 rounded hover:bg-gray-50 transition-colors flex items-center gap-1 text-sm">
                            <i class="fas fa-user"></i>
                            <span>View Profile</span>
                        </button>
                        <button onclick="redirectToManageStore('${vendor.id}')"
                            class="h-8 px-3 bg-primary text-white rounded hover:bg-primary/90 transition-colors flex items-center gap-1 text-sm">
                            <i class="fas fa-cog"></i>
                            <span>Manage</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    }

    function loadCategories(categories) {
        const select = document.getElementById('filterCategory');
        const currentValue = select.value;

        select.innerHTML = '<option value="">All Categories</option>';

        if (categories && categories.length > 0) {
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.name;
                option.textContent = category.name;
                select.appendChild(option);
            });
        }

        select.value = currentValue;
    }

    function updatePagination(pagination) {
        if (!pagination) return;

        currentPage = pagination.current_page;
        totalPages = pagination.total_pages;

        const container = document.getElementById('pagination-container');
        const numbersContainer = document.getElementById('pagination-numbers');

        if (totalPages <= 1) {
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');

        document.getElementById('showing-start').textContent = pagination.start;
        document.getElementById('showing-end').textContent = pagination.end;
        document.getElementById('total-vendors').textContent = pagination.total;

        document.getElementById('prev-page').disabled = currentPage <= 1;
        document.getElementById('next-page').disabled = currentPage >= totalPages;

        numbersContainer.innerHTML = '';

        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        for (let i = startPage; i <= endPage; i++) {
            const button = document.createElement('button');
            button.className = `px-3 py-2 rounded-lg ${i === currentPage ? 'bg-primary text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50'}`;
            button.textContent = i;
            button.onclick = () => goToPage(i);
            numbersContainer.appendChild(button);
        }
    }

    function updateVendorCount(total) {
        document.getElementById('vendor-count').textContent = total || 0;
    }

    function goToPage(page) {
        if (page >= 1 && page <= totalPages && page !== currentPage) {
            currentPage = page;
            applyFilters();
        }
    }

    function showLoading() {
        document.getElementById('loading-state').classList.remove('hidden');
        document.getElementById('vendors-list').classList.add('hidden');
        document.getElementById('empty-state').classList.add('hidden');
    }

    function hideLoading() {
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('vendors-list').classList.remove('hidden');
    }

    function showEmptyState() {
        document.getElementById('empty-state').classList.remove('hidden');
        document.getElementById('pagination-container').classList.add('hidden');
    }

    function hideEmptyState() {
        document.getElementById('empty-state').classList.add('hidden');
    }

    function showError(message) {
        alert(message);
    }

    function showStatusModal(storeId, storeName, currentStatus) {
        currentStoreId = storeId;

        document.getElementById('status-store-info').innerHTML = `
        <div class="bg-gray-50 p-3 rounded-lg">
            <h4 class="font-medium text-gray-800">${storeName}</h4>
            <p class="text-sm text-gray-600">Store ID: ${storeId}</p>
        </div>
    `;

        const statusRadios = document.querySelectorAll('input[name="new-status"]');
        statusRadios.forEach(radio => {
            radio.checked = radio.value === currentStatus;
        });

        document.getElementById('statusModal').classList.remove('hidden');
    }

    function hideStatusModal() {
        document.getElementById('statusModal').classList.add('hidden');
        currentStoreId = null;
    }

    async function updateStoreStatus() {
        if (!currentStoreId) return;

        const selectedStatus = document.querySelector('input[name="new-status"]:checked');
        if (!selectedStatus) {
            showError('Please select a status');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('store_id', currentStoreId);
            formData.append('status', selectedStatus.value);

            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                hideStatusModal();
                applyFilters();
                alert('Store status updated successfully');
            } else {
                showError(data.message || 'Failed to update status');
            }
        } catch (error) {
            console.error('Error updating status:', error);
            showError('Failed to update status. Please try again.');
        }
    }

    function applyFilters() {
        const search = document.getElementById('searchVendors').value.trim();
        const category = document.getElementById('filterCategory').value;
        const status = document.getElementById('filterStatus').value;

        loadVendors(currentPage, search, category, status);
    }

    function resetFilters() {
        document.getElementById('searchVendors').value = '';
        document.getElementById('filterCategory').value = '';
        document.getElementById('filterStatus').value = '';
        currentPage = 1;
        loadVendors();
    }

    function redirectToManageStore(storeUuid) {
        showLoading();
        $.ajax({
            url: BASE_URL + 'account/fetch/initVendorSession.php',
            type: 'POST',
            data: { store_uuid: storeUuid },
            dataType: 'json',
            success: function (response) {
                hideLoading();
                if (response.success && response.redirect_url) {
                    window.open(response.redirect_url, '_blank');
                } else {
                    showErrorNotification(response.message || 'Failed to initiate store session');
                }
            },
            error: function () {
                hideLoading();
                showErrorNotification('Server error occurred. Please try again.');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadVendors();

        let searchTimeout;
        document.getElementById('searchVendors').addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                applyFilters();
            }, 500);
        });

        document.getElementById('filterCategory').addEventListener('change', function () {
            currentPage = 1;
            applyFilters();
        });

        document.getElementById('filterStatus').addEventListener('change', function () {
            currentPage = 1;
            applyFilters();
        });

        document.getElementById('applyFilters').addEventListener('click', function () {
            currentPage = 1;
            applyFilters();
        });

        document.getElementById('resetFilters').addEventListener('click', resetFilters);

        document.getElementById('prev-page').addEventListener('click', function () {
            if (currentPage > 1) {
                goToPage(currentPage - 1);
            }
        });

        document.getElementById('next-page').addEventListener('click', function () {
            if (currentPage < totalPages) {
                goToPage(currentPage + 1);
            }
        });

        document.getElementById('updateStatus').addEventListener('click', updateStoreStatus);
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>