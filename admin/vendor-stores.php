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
                $limit = intval($_POST['limit'] ?? 20);
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

<div class="min-h-screen bg-gray-50 font-rubik" id="app-container">
    <div class="bg-white border-b border-gray-200 sm:px-6 lg:px-8 py-3 sm:py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
                <div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <h1 class="text-lg sm:text-2xl font-bold text-secondary">Manage Vendors</h1>
                    </div>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base hidden sm:block">View, verify and manage all
                        vendor accounts</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <button id="sendNotificationBtn"
                        class="px-3 sm:px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-bell text-sm"></i>
                        <span class="hidden sm:inline">Send Notification</span>
                    </button>
                    <a href="nature-of-business"
                        class="px-3 sm:px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-tags text-sm"></i>
                        <span class="hidden sm:inline">Nature of Business</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
        <div class="hidden sm:grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Total Vendors</p>
                        <p class="text-xl font-bold text-blue-900 truncate" id="totalVendors">0</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-store text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Active</p>
                        <p class="text-xl font-bold text-green-900 truncate" id="activeVendors">0</p>
                    </div>
                    <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-orange-600 uppercase tracking-wide">Pending</p>
                        <p class="text-xl font-bold text-orange-900 truncate" id="pendingVendors">0</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-clock text-orange-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-xl p-4 border border-red-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-red-600 uppercase tracking-wide">Suspended</p>
                        <p class="text-xl font-bold text-red-900 truncate" id="suspendedVendors">0</p>
                    </div>
                    <div class="w-10 h-10 bg-red-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-ban text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-secondary mb-2">Filter & Search</h2>
                    <p class="text-sm text-gray-600">Configure your vendor view and filters</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Vendors</label>
                    <div class="relative">
                        <input type="text" id="searchVendors" placeholder="Search vendors..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category Filter</label>
                    <select id="filterCategory"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Filter</label>
                    <select id="filterStatus"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-secondary">Vendors</h3>
                        <p class="text-sm text-gray-600"><span id="vendorCount">0</span> vendors found</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <button id="resetFilters"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            Reset Filters
                        </button>
                        <button id="applyFilters"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full" id="vendorsTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Vendor</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Contact</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Category</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Products</th>
                        </tr>
                    </thead>
                    <tbody id="vendorsBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <div>Loading vendors...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="showingStart">0</span> to <span id="showingEnd">0</span> of <span
                        id="totalVendorsCount">0</span> vendors
                </div>
                <div class="flex items-center gap-2">
                    <button id="prev-page"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Prev
                    </button>
                    <div id="pagination-numbers" class="flex items-center"></div>
                    <button id="next-page"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Next
                    </button>
                </div>
            </div>

            <div class="lg:hidden" id="vendorsCards">
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <div>Loading vendors...</div>
                </div>
            </div>

            <div
                class="lg:hidden p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="mobileShowingStart">0</span> to <span id="mobileShowingEnd">0</span> of <span
                        id="mobileTotalVendors">0</span> vendors
                </div>
                <div class="flex items-center gap-2">
                    <button id="mobilePrevPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Prev
                    </button>
                    <span id="mobilePageInfo" class="px-3 py-1 text-sm text-gray-600">Page 1 of 1</span>
                    <button id="mobileNextPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loadingOverlay" class="fixed inset-0 bg-black/30 flex items-center justify-center z-[999] hidden">
    <div class="bg-white p-5 rounded-lg shadow-lg flex items-center gap-3">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        <span id="loadingMessage" class="text-gray-700 font-medium">Loading...</span>
    </div>
</div>

<div id="vendorModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="hideVendorModal()"></div>
    <div
        class="relative w-full  max-w-4xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg max-h-[90vh] overflow-hidden m-4">
        <div
            class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-primary/10 to-primary/5">
            <div class="flex items-center gap-3">
                <div
                    class="flex-shrink-0 h-12 w-12 rounded-lg bg-gray-100 overflow-hidden flex items-center justify-center">
                    <i class="fas fa-store text-gray-400 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-secondary" id="modalTitle">Vendor Details</h3>
                    <p class="text-sm text-gray-600 mt-1">View vendor information and manage status</p>
                </div>
            </div>
            <button onclick="hideVendorModal()"
                class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 sm:p-6 max-h-[calc(90vh-160px)]" id="vendorDetails">
        </div>

        <div class="p-2 border-t border-gray-100 flex justify-between">
            <button type="button" id="updateStatusBtn"
                class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                <i class="fas fa-edit mr-2"></i>Update Status
            </button>
            <div class="flex gap-3">
                <button type="button" onclick="hideVendorModal()"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="button" id="manageStoreBtn"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    <i class="fas fa-cog mr-2"></i>Manage
                </button>
            </div>
        </div>
    </div>
</div>

<div id="statusModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="hideStatusModal()"></div>
    <div
        class="relative w-full  max-w-md mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg max-h-[90vh] overflow-hidden m-4">
        <div
            class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-orange-50 to-orange-100">
            <div class="flex items-center gap-3">
                <div
                    class="flex-shrink-0 h-12 w-12 rounded-lg bg-orange-100 overflow-hidden flex items-center justify-center">
                    <i class="fas fa-edit text-orange-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-secondary">Update Status</h3>
                    <p class="text-sm text-gray-600 mt-1">Change vendor store status</p>
                </div>
            </div>
            <button onclick="hideStatusModal()"
                class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 sm:p-6 max-h-[calc(90vh-100px)]">
            <div id="status-store-info" class="mb-4"></div>
            <div class="space-y-3">
                <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="new-status" value="active" class="form-radio h-4 w-4 text-primary">
                    <span class="ml-3 text-sm text-gray-700 font-medium">Active</span>
                </label>
                <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="new-status" value="pending" class="form-radio h-4 w-4 text-primary">
                    <span class="ml-3 text-sm text-gray-700 font-medium">Pending</span>
                </label>
                <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="new-status" value="inactive" class="form-radio h-4 w-4 text-primary">
                    <span class="ml-3 text-sm text-gray-700 font-medium">Inactive</span>
                </label>
                <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="new-status" value="suspended" class="form-radio h-4 w-4 text-primary">
                    <span class="ml-3 text-sm text-gray-700 font-medium">Suspended</span>
                </label>
            </div>
        </div>

        <div class="p-2 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideStatusModal()"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
            <button id="confirmUpdateStatus"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Update</button>
        </div>
    </div>
</div>

<div id="successNotification"
    class="fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span id="successMessage"></span>
    </div>
</div>

<div id="errorNotification"
    class="fixed top-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <span id="errorMessage"></span>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    let vendorsData = [];
    let currentPage = 1;
    let itemsPerPage = 20;
    let totalPages = 1;
    let currentStoreId = null;
    let filterData = {
        category: '',
        status: '',
        search: ''
    };

    document.addEventListener('DOMContentLoaded', () => {
        loadVendors();

        let searchTimeout;
        document.getElementById('searchVendors').addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterData.search = e.target.value;
                applyFilters();
            }, 500);
        });

        document.getElementById('filterCategory').addEventListener('change', (e) => {
            filterData.category = e.target.value;
        });

        document.getElementById('filterStatus').addEventListener('change', (e) => {
            filterData.status = e.target.value;
        });

        document.getElementById('applyFilters').addEventListener('click', applyFilters);
        document.getElementById('resetFilters').addEventListener('click', resetFilters);

        document.getElementById('prev-page').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderPagination();
                renderVendors(vendorsData);
            }
        });

        document.getElementById('next-page').addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderPagination();
                renderVendors(vendorsData);
            }
        });

        ['mobilePrevPage', 'mobileNextPage'].forEach(id => {
            document.getElementById(id).addEventListener('click', function () {
                const filteredList = filterVendors(vendorsData);
                const newTotalPages = Math.ceil(filteredList.length / itemsPerPage);

                if (id.includes('prev') && currentPage > 1) {
                    currentPage--;
                } else if (id.includes('next') && currentPage < newTotalPages) {
                    currentPage++;
                }

                totalPages = newTotalPages;
                renderPagination();
                renderVendors(vendorsData);
            });
        });

        document.getElementById('confirmUpdateStatus').addEventListener('click', updateStoreStatus);
    });

    function showLoading(message = 'Loading...') {
        document.getElementById('loadingMessage').textContent = message;
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    function loadVendors() {
        showLoading('Loading vendors...');

        const formData = new FormData();
        formData.append('action', 'load_vendors');
        formData.append('page', 1);
        formData.append('limit', 1000);
        formData.append('search', '');
        formData.append('category', '');
        formData.append('status', '');

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    vendorsData = data.vendors || [];
                    loadCategories(data.categories);
                    updateStatistics();
                    totalPages = Math.ceil(vendorsData.length / itemsPerPage);
                    currentPage = 1;
                    renderPagination();
                    renderVendors(vendorsData);
                } else {
                    showErrorNotification(data.message || 'Failed to load vendors');
                }
            })
            .catch(err => {
                hideLoading();
                console.error('Error loading vendors:', err);
                showErrorNotification('Failed to load vendors.');
            });
    }

    function loadCategories(categories) {
        const select = document.getElementById('filterCategory');
        select.innerHTML = '<option value="">All Categories</option>';

        if (categories && categories.length > 0) {
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.name;
                option.textContent = category.name;
                select.appendChild(option);
            });
        }
    }

    function updateStatistics() {
        const total = vendorsData.length;
        const active = vendorsData.filter(v => v.status === 'active').length;
        const pending = vendorsData.filter(v => v.status === 'pending').length;
        const suspended = vendorsData.filter(v => v.status === 'suspended').length;

        document.getElementById('totalVendors').textContent = total.toLocaleString();
        document.getElementById('activeVendors').textContent = active.toLocaleString();
        document.getElementById('pendingVendors').textContent = pending.toLocaleString();
        document.getElementById('suspendedVendors').textContent = suspended.toLocaleString();
    }

    function renderPagination() {
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;

        const pagNums = document.getElementById('pagination-numbers');
        pagNums.innerHTML = '';
        if (totalPages <= 5) {
            for (let i = 1; i <= totalPages; i++) {
                pagNums.appendChild(createPagButton(i));
            }
        } else {
            pagNums.appendChild(createPagButton(1));
            if (currentPage > 3) {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.classList.add('px-2');
                pagNums.appendChild(ellipsis);
            }
            for (let i = Math.max(2, currentPage - 1); i <= Math.min(totalPages - 1, currentPage + 1); i++) {
                pagNums.appendChild(createPagButton(i));
            }
            if (currentPage < totalPages - 2) {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.classList.add('px-2');
                pagNums.appendChild(ellipsis);
            }
            pagNums.appendChild(createPagButton(totalPages));
        }
    }

    function createPagButton(page) {
        const btn = document.createElement('button');
        btn.className = (page === currentPage) ?
            'px-3 py-2 rounded-lg bg-primary text-white' :
            'px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50';
        btn.textContent = page;
        btn.addEventListener('click', () => {
            currentPage = page;
            renderPagination();
            renderVendors(vendorsData);
        });
        return btn;
    }

    function renderVendors(list) {
        const filteredList = filterVendors(list);
        totalPages = Math.ceil(filteredList.length / itemsPerPage);

        if (currentPage > totalPages && totalPages > 0) {
            currentPage = totalPages;
        }

        const start = (currentPage - 1) * itemsPerPage;
        const end = Math.min(start + itemsPerPage, filteredList.length);

        document.getElementById('vendorCount').textContent = filteredList.length;
        document.getElementById('showingStart').textContent = filteredList.length > 0 ? start + 1 : 0;
        document.getElementById('showingEnd').textContent = end;
        document.getElementById('totalVendorsCount').textContent = filteredList.length;

        renderVendorsTable(filteredList.slice(start, end));
        renderVendorsCards(filteredList.slice(start, end));
        updateMobilePagination(filteredList.length, currentPage);
        renderPagination();
    }

    function renderVendorsTable(vendors) {
        const tbody = document.getElementById('vendorsBody');

        if (vendors.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-store text-2xl mb-2"></i>
                        <div>No vendors found</div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = vendors.map(vendor => {
            const statusBadge = getStatusBadge(vendor.status);

            return `
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="showVendorModal('${vendor.id}')">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                <i class="fas fa-store text-gray-400"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-secondary max-w-xs hover:text-primary">
                                    <span class="hidden sm:block truncate">${escapeHtml(vendor.name)}</span>
                                    <span class="sm:hidden break-words">${escapeHtml(vendor.name)}</span>
                                </div>
                                <div class="text-xs text-gray-500 hidden sm:block">${escapeHtml(vendor.owner_name || 'No owner')}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="text-sm text-gray-900">${escapeHtml(vendor.contact_person_name || 'N/A')}</div>
                        <div class="text-xs text-gray-500">${escapeHtml(vendor.business_email || 'N/A')}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-sm text-gray-900">${escapeHtml(vendor.nature_of_business || 'Uncategorized')}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        ${statusBadge}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-sm font-medium text-gray-900">${vendor.product_count || 0}</span>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderVendorsCards(vendors) {
        const container = document.getElementById('vendorsCards');

        if (vendors.length === 0) {
            container.innerHTML = `
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-store text-2xl mb-2"></i>
                    <div>No vendors found</div>
                </div>
            `;
            return;
        }

        container.innerHTML = vendors.map(vendor => {
            const statusBadge = getStatusBadge(vendor.status);

            return `
                <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer" onclick="showVendorModal('${vendor.id}')">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 h-12 w-12 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-store text-gray-400"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="mb-1">
                                <div class="flex items-center gap-1 mb-1">
                                    ${statusBadge}
                                    ${vendor.nature_of_business ? `
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            ${escapeHtml(vendor.nature_of_business)}
                                        </span>
                                    ` : ''}
                                </div>
                                <h4 class="text-sm font-medium text-secondary hover:text-primary pr-2 break-words">
                                    ${escapeHtml(vendor.name)}
                                </h4>
                            </div>
                            <div class="text-xs text-gray-500 mb-2">Owner: ${escapeHtml(vendor.owner_name || 'No owner')}</div>
                            <div class="text-xs text-gray-600 mb-2">Contact: ${escapeHtml(vendor.contact_person_name || 'N/A')}</div>
                            <div class="text-xs text-gray-600 mb-2">Email: ${escapeHtml(vendor.business_email || 'N/A')}</div>
                            <div class="text-xs text-gray-600 mb-2">Location: ${escapeHtml(vendor.full_address || 'N/A')}</div>
                            <div class="text-xs text-gray-600 mb-2">Products: ${vendor.product_count || 0}</div>
                            <div class="text-xs text-gray-500">Registered: ${formatDate(vendor.created_at)}</div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function getStatusBadge(status) {
        switch (status) {
            case 'active':
                return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>';
            case 'pending':
                return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Pending</span>';
            case 'inactive':
                return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>';
            case 'suspended':
                return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Suspended</span>';
            default:
                return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Unknown</span>';
        }
    }

    function updateMobilePagination(total, page) {
        const totalPages = Math.ceil(total / itemsPerPage);
        const startIndex = (page - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, total);

        document.getElementById('mobileShowingStart').textContent = `${startIndex + 1}`;
        document.getElementById('mobileShowingEnd').textContent = `${endIndex}`;
        document.getElementById('mobileTotalVendors').textContent = total;
        document.getElementById('mobilePageInfo').textContent = `Page ${page} of ${Math.max(1, totalPages)}`;

        document.getElementById('mobilePrevPage').disabled = page === 1;
        document.getElementById('mobileNextPage').disabled = page === totalPages || totalPages === 0;
    }

    function filterVendors(vendors) {
        return vendors.filter(vendor => {
            if (filterData.search &&
                !vendor.name.toLowerCase().includes(filterData.search.toLowerCase()) &&
                !vendor.business_email.toLowerCase().includes(filterData.search.toLowerCase()) &&
                !vendor.contact_person_name.toLowerCase().includes(filterData.search.toLowerCase()) &&
                !vendor.owner_name.toLowerCase().includes(filterData.search.toLowerCase())) {
                return false;
            }
            if (filterData.category && vendor.nature_of_business !== filterData.category) {
                return false;
            }
            if (filterData.status && vendor.status !== filterData.status) {
                return false;
            }
            return true;
        });
    }

    function applyFilters() {
        currentPage = 1;
        renderPagination();
        renderVendors(vendorsData);
    }

    function resetFilters() {
        document.getElementById('filterCategory').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('searchVendors').value = '';

        filterData = {
            category: '',
            status: '',
            search: ''
        };

        applyFilters();
    }

    function showVendorModal(vendorId) {
        const vendor = vendorsData.find(v => v.id === vendorId);
        if (!vendor) return;

        currentStoreId = vendorId;

        const detailsHtml = `
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                        <div class="text-sm text-gray-900 p-2 bg-gray-50 rounded">${escapeHtml(vendor.name)}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Owner</label>
                        <div class="text-sm text-gray-900 p-2 bg-gray-50 rounded">${escapeHtml(vendor.owner_name || 'No owner')}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                        <div class="text-sm text-gray-900 p-2 bg-gray-50 rounded">${escapeHtml(vendor.contact_person_name || 'N/A')}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business Email</label>
                        <div class="text-sm text-gray-900 p-2 bg-gray-50 rounded">${escapeHtml(vendor.business_email || 'N/A')}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business Phone</label>
                        <div class="text-sm text-gray-900 p-2 bg-gray-50 rounded">${escapeHtml(vendor.business_phone || 'N/A')}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nature of Business</label>
                        <div class="text-sm text-gray-900 p-2 bg-gray-50 rounded">${escapeHtml(vendor.nature_of_business || 'Uncategorized')}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <div class="p-2">${getStatusBadge(vendor.status)}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Products Count</label>
                        <div class="text-sm text-gray-900 p-2 bg-gray-50 rounded">${vendor.product_count || 0}</div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Address</label>
                        <div class="text-sm text-gray-900 p-2 bg-gray-50 rounded">${escapeHtml(vendor.full_address || 'N/A')}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Registration Date</label>
                        <div class="text-sm text-gray-900 p-2 bg-gray-50 rounded">${formatDate(vendor.created_at)}</div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('vendorDetails').innerHTML = detailsHtml;
        document.getElementById('vendorModal').classList.remove('hidden');

        document.getElementById('updateStatusBtn').onclick = () => showStatusModal(vendor.id, vendor.name, vendor.status);
        document.getElementById('manageStoreBtn').onclick = () => redirectToManageStore(vendor.id);
    }

    function hideVendorModal() {
        document.getElementById('vendorModal').classList.add('hidden');
        currentStoreId = null;
    }

    function showStatusModal(storeId, storeName, currentStatus) {
        document.getElementById('status-store-info').innerHTML = `
            <div class="bg-gray-50 p-3 rounded-lg">
                <h4 class="font-medium text-gray-800">${escapeHtml(storeName)}</h4>
                <p class="text-sm text-gray-600">Current Status: ${getStatusBadge(currentStatus)}</p>
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
    }

    function updateStoreStatus() {
        if (!currentStoreId) return;

        const selectedStatus = document.querySelector('input[name="new-status"]:checked');
        if (!selectedStatus) {
            showErrorNotification('Please select a status');
            return;
        }

        showLoading('Updating status...');

        const formData = new FormData();
        formData.append('action', 'update_status');
        formData.append('store_id', currentStoreId);
        formData.append('status', selectedStatus.value);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    hideStatusModal();
                    hideVendorModal();
                    showSuccessNotification(data.message || 'Status updated successfully');
                    loadVendors();
                } else {
                    showErrorNotification(data.message || 'Failed to update status');
                }
            })
            .catch(err => {
                hideLoading();
                console.error('Error updating status:', err);
                showErrorNotification('Failed to update status');
            });
    }

    function redirectToManageStore(storeUuid) {
        showLoading('Initiating store session...');
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

    function formatDate(dateString) {
        if (!dateString) return 'Not available';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: '2-digit'
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showSuccessNotification(message) {
        const notif = document.getElementById('successNotification');
        const msgEl = document.getElementById('successMessage');
        msgEl.textContent = message;
        notif.classList.remove('hidden');
        setTimeout(() => notif.classList.add('hidden'), 3000);
    }

    function showErrorNotification(message) {
        const notif = document.getElementById('errorNotification');
        const msgEl = document.getElementById('errorMessage');
        msgEl.textContent = message;
        notif.classList.remove('hidden');
        setTimeout(() => notif.classList.add('hidden'), 5000);
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>