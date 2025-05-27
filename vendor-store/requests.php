<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Store Visit Requests';
$activeNav = 'requests';

if (!isset($_SESSION['user']) || empty($_SESSION['user']['logged_in'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL);
    exit;
}

$storeId = $_SESSION['active_store'] ?? null;
if (!$storeId) {
    header('Location: ' . BASE_URL . 'account/dashboard');
    exit;
}

ob_start();
?>
<script>
    const BUY_IN_STORE_ENDPOINT = '<?= BASE_URL ?>vendor-store/fetch/manageBuyInStore.php';
</script>
<div class="space-y-4 md:space-y-6">
    <div id="alertContainer"></div>
    <div class="content-section">
        <div class="content-header p-4 md:p-6">
            <h2 class="text-lg md:text-xl font-semibold text-secondary">Request Overview</h2>
        </div>
        <div class="p-4 md:p-6 grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">
            <div class="user-card">
                <div class="p-3 md:p-6 flex flex-col items-center text-center">
                    <div
                        class="w-12 h-12 md:w-16 md:h-16 rounded-full bg-yellow-100 flex items-center justify-center mb-2 md:mb-4">
                        <i class="fas fa-clock text-yellow-600 text-lg md:text-2xl"></i>
                    </div>
                    <h3 id="count-pending" class="text-lg md:text-2xl font-semibold text-secondary mb-1">0</h3>
                    <p class="text-xs md:text-sm text-gray-text">Pending</p>
                </div>
            </div>
            <div class="user-card">
                <div class="p-3 md:p-6 flex flex-col items-center text-center">
                    <div
                        class="w-12 h-12 md:w-16 md:h-16 rounded-full bg-blue-100 flex items-center justify-center mb-2 md:mb-4">
                        <i class="fas fa-check-circle text-blue-600 text-lg md:text-2xl"></i>
                    </div>
                    <h3 id="count-confirmed" class="text-lg md:text-2xl font-semibold text-secondary mb-1">0</h3>
                    <p class="text-xs md:text-sm text-gray-text">Confirmed</p>
                </div>
            </div>
            <div class="user-card">
                <div class="p-3 md:p-6 flex flex-col items-center text-center">
                    <div
                        class="w-12 h-12 md:w-16 md:h-16 rounded-full bg-green-100 flex items-center justify-center mb-2 md:mb-4">
                        <i class="fas fa-check-double text-green-600 text-lg md:text-2xl"></i>
                    </div>
                    <h3 id="count-completed" class="text-lg md:text-2xl font-semibold text-secondary mb-1">0</h3>
                    <p class="text-xs md:text-sm text-gray-text">Completed</p>
                </div>
            </div>
            <div class="user-card">
                <div class="p-3 md:p-6 flex flex-col items-center text-center">
                    <div
                        class="w-12 h-12 md:w-16 md:h-16 rounded-full bg-red-100 flex items-center justify-center mb-2 md:mb-4">
                        <i class="fas fa-times-circle text-red-600 text-lg md:text-2xl"></i>
                    </div>
                    <h3 id="count-cancelled" class="text-lg md:text-2xl font-semibold text-secondary mb-1">0</h3>
                    <p class="text-xs md:text-sm text-gray-text">Cancelled</p>
                </div>
            </div>
        </div>
    </div>

    <div class="content-section">
        <div class="p-4 md:p-6">
            <div class="flex flex-col space-y-3 md:space-y-0 md:flex-row md:space-x-4">
                <div class="flex-1">
                    <input type="text" id="searchInput" placeholder="Search customers or products..."
                        class="w-full px-3 py-2 md:px-4 md:py-2 text-sm md:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary focus:border-transparent">
                </div>
                <div class="flex space-x-2">
                    <select id="statusFilter"
                        class="flex-1 md:flex-none px-3 py-2 md:px-4 md:py-2 text-sm md:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary focus:border-transparent">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button id="filterBtn"
                        class="px-4 py-2 md:px-6 md:py-2 bg-user-primary text-white text-sm md:text-base rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-search mr-1 md:mr-2"></i><span class="hidden md:inline">Filter</span>
                    </button>
                    <button id="clearBtn"
                        class="px-4 py-2 md:px-6 md:py-2 bg-gray-500 text-white text-sm md:text-base rounded-lg hover:bg-gray-600 transition-colors">
                        <i class="fas fa-times mr-1 md:mr-2"></i><span class="hidden md:inline">Clear</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="content-section">
        <div class="content-header p-4 md:p-6">
            <h2 class="text-lg md:text-xl font-semibold text-secondary">Store Visit Requests <span
                    id="requestCount"></span></h2>
        </div>
        <div class="overflow-x-auto">
            <div id="loadingIndicator" class="hidden p-8 text-center">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                <p class="text-gray-600">Loading requests...</p>
            </div>
            <div id="requestsContainer"></div>
        </div>
        <div id="paginationContainer" class="px-4 py-3 md:px-6 md:py-4 border-t border-gray-200"></div>
    </div>
</div>

<div id="requestModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-[95vh] overflow-hidden flex flex-col">
        <div class="p-4 md:p-6 border-b border-gray-200 flex-shrink-0">
            <div class="flex items-center justify-between">
                <h3 class="text-lg md:text-xl font-semibold text-gray-900">Request Details</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto">
            <div id="modalContent" class="p-4 md:p-6"></div>
        </div>
    </div>
</div>

<div id="emailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <div class="p-4 md:p-6 border-b border-gray-200 flex-shrink-0">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Send Email</h3>
                <button onclick="closeEmailModal()" class="text-gray-400 hover:text-gray-600 p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto">
            <form id="emailForm" class="p-4 md:p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To:</label>
                    <input type="email" id="emailTo" readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Subject:</label>
                    <input type="text" id="emailSubject" readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message:</label>
                    <textarea id="emailMessage" rows="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary focus:border-transparent"
                        placeholder="Type your message here..."></textarea>
                </div>
                <div class="flex space-x-3">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-user-primary text-white rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>Send Email
                    </button>
                    <button type="button" onclick="closeEmailModal()"
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="smsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col">
        <div class="p-4 md:p-6 border-b border-gray-200 flex-shrink-0">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Send SMS</h3>
                <button onclick="closeSmsModal()" class="text-gray-400 hover:text-gray-600 p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto">
            <form id="smsForm" class="p-4 md:p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To:</label>
                    <input type="tel" id="smsTo" readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message:</label>
                    <textarea id="smsMessage" rows="4" maxlength="160"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary focus:border-transparent"
                        placeholder="Type your SMS message here..."></textarea>
                    <div class="text-right text-sm text-gray-500 mt-1">
                        <span id="smsCharCount">0</span>/160 characters
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-user-primary text-white rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-sms mr-2"></i>Send SMS
                    </button>
                    <button type="button" onclick="closeSmsModal()"
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    let currentFilters = { search: '', status: '' };
    let currentRequestId = null;

    $(document).ready(function () {
        fetchStats();
        setInterval(fetchStats, 15000);
        loadRequests();

        $('#filterBtn').click(function () {
            currentPage = 1;
            currentFilters.search = $('#searchInput').val();
            currentFilters.status = $('#statusFilter').val();
            loadRequests();
        });

        $('#clearBtn').click(function () {
            $('#searchInput').val('');
            $('#statusFilter').val('');
            currentPage = 1;
            currentFilters = { search: '', status: '' };
            loadRequests();
        });

        $('#searchInput').keypress(function (e) {
            if (e.which === 13) $('#filterBtn').click();
        });

        $('#smsMessage').on('input', function () {
            $('#smsCharCount').text($(this).val().length);
        });

        $('#emailForm').submit(function (e) {
            e.preventDefault();
            sendEmail();
        });

        $('#smsForm').submit(function (e) {
            e.preventDefault();
            sendSms();
        });
    });

    function fetchStats() {
        $.ajax({
            url: BUY_IN_STORE_ENDPOINT + '?action=get_stats_counts',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#count-pending').text(response.data.pending || 0);
                    $('#count-confirmed').text(response.data.confirmed || 0);
                    $('#count-completed').text(response.data.completed || 0);
                    $('#count-cancelled').text(response.data.cancelled || 0);
                }
            }
        });
    }

    function loadRequests() {
        $('#loadingIndicator').removeClass('hidden');
        $('#requestsContainer').addClass('opacity-50');
        $.ajax({
            url: BUY_IN_STORE_ENDPOINT + '?action=filter_requests',
            type: 'POST',
            data: {
                page: currentPage,
                search: currentFilters.search,
                status: currentFilters.status
            },
            dataType: 'json',
            success: function (response) {
                $('#loadingIndicator').addClass('hidden');
                $('#requestsContainer').removeClass('opacity-50');
                if (response.success) {
                    renderRequests(response.data);
                    renderPagination(response.pagination);
                    $('#requestCount').text(`(${response.pagination.total_requests})`);
                } else {
                    showAlert('error', response.message || 'Failed to load requests');
                }
            },
            error: function (xhr) {
                $('#loadingIndicator').addClass('hidden');
                $('#requestsContainer').removeClass('opacity-50');
                console.error('AJAX Error:', xhr.responseText);
                showAlert('error', 'Failed to connect to server');
            }
        });
    }

    function renderRequests(requests) {
        const statusColors = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'confirmed': 'bg-blue-100 text-blue-800',
            'completed': 'bg-green-100 text-green-800',
            'cancelled': 'bg-red-100 text-red-800'
        };
        const statusIcons = {
            'pending': 'fa-clock',
            'confirmed': 'fa-check-circle',
            'completed': 'fa-check-double',
            'cancelled': 'fa-times-circle'
        };
        if (requests.length === 0) {
            $('#requestsContainer').html(`
            <div class="p-8 md:p-12 text-center">
                <i class="fas fa-calendar-times text-4xl md:text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg md:text-xl font-medium text-gray-500 mb-2">No requests found</h3>
                <p class="text-sm md:text-base text-gray-400">No store visit requests match your current filters.</p>
            </div>
        `);
            return;
        }
        let mobileHTML = '<div class="block md:hidden space-y-3 p-4">';
        requests.forEach(request => {
            const visitDate = new Date(request.visit_date);
            const today = new Date();
            const diffDays = Math.ceil((visitDate - today) / (1000 * 60 * 60 * 24));
            let dateInfo = diffDays < 0 ? `${Math.abs(diffDays)} days ago` : diffDays === 0 ? 'Today' : `In ${diffDays} days`;
            mobileHTML += `
            <div class="user-card cursor-pointer hover:shadow-md transition-shadow" onclick="viewRequest('${request.id}')">
                <div class="p-4">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h4 class="font-semibold text-secondary">${request.first_name} ${request.last_name}</h4>
                            <p class="text-sm text-gray-text">${request.email}</p>
                            <p class="text-sm text-gray-text">${request.phone}</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${statusColors[request.status]}">
                            <i class="fas ${statusIcons[request.status]} mr-1"></i>
                            ${request.status.charAt(0).toUpperCase() + request.status.slice(1)}
                        </span>
                    </div>
                    <div class="border-t pt-3">
                        <p class="text-sm font-medium text-gray-900 mb-1">${request.product_title}</p>
                        <div class="flex justify-between items-center text-sm text-gray-text">
                            <span>Qty: ${request.quantity}</span>
                            <span>${visitDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} (${dateInfo})</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        });
        mobileHTML += '</div>';

        let tableHTML = `
        <div class="hidden md:block">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visit Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
    `;
        requests.forEach(request => {
            const visitDate = new Date(request.visit_date);
            const today = new Date();
            const diffDays = Math.ceil((visitDate - today) / (1000 * 60 * 60 * 24));
            let dateInfo = diffDays < 0 ? `${Math.abs(diffDays)} days ago` : diffDays === 0 ? 'Today' : `In ${diffDays} days`;
            tableHTML += `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="flex flex-col">
                        <div class="text-sm font-medium text-gray-900">${request.first_name} ${request.last_name}</div>
                        <div class="text-sm text-gray-500">${request.email}</div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">${visitDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</div>
                    <div class="text-xs text-gray-500">${dateInfo}</div>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColors[request.status]}">
                        <i class="fas ${statusIcons[request.status]} mr-1"></i>
                        ${request.status.charAt(0).toUpperCase() + request.status.slice(1)}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center space-x-2">
                        <button onclick="viewRequest('${request.id}')" class="text-blue-600 hover:text-blue-900 text-sm p-2 hover:bg-blue-50 rounded">
                            <i class="fas fa-eye"></i>
                        </button>
                        <div class="relative">
                            <button onclick="toggleStatusDropdown('${request.id}')" class="text-gray-600 hover:text-gray-900 text-sm p-2 hover:bg-gray-50 rounded">
                                <i class="fas fa-edit"></i>
                            </button>
                            <div id="status-dropdown-${request.id}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border">
                                <div class="py-1">
                                    ${['pending', 'confirmed', 'completed', 'cancelled'].map(status => {
                return status !== request.status
                    ? `<button onclick="updateStatus('${request.id}','${status}')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas ${statusIcons[status]} mr-2"></i>${status.charAt(0).toUpperCase() + status.slice(1)}</button>`
                    : '';
            }).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        `;
        });
        tableHTML += '</tbody></table></div>';
        $('#requestsContainer').html(mobileHTML + tableHTML);
    }

    function renderPagination(pagination) {
        if (pagination.total_pages <= 1) {
            $('#paginationContainer').html('');
            return;
        }
        let paginationHTML = `
        <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0">
            <div class="text-sm text-gray-700">
                Showing ${pagination.offset + 1} to ${Math.min(pagination.offset + pagination.limit, pagination.total_requests)} of ${pagination.total_requests} results
            </div>
            <div class="flex space-x-1 md:space-x-2">
    `;
        if (pagination.current_page > 1) {
            paginationHTML += `
            <button onclick="changePage(${pagination.current_page - 1})" class="px-2 py-1 md:px-3 md:py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                <i class="fas fa-chevron-left md:hidden"></i><span class="hidden md:inline">Previous</span>
            </button>
        `;
        }
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === pagination.current_page;
            paginationHTML += `
            <button onclick="changePage(${i})" class="px-2 py-1 md:px-3 md:py-2 text-sm ${isActive ? 'bg-user-primary text-white' : 'bg-white border border-gray-300 hover:bg-gray-50'} rounded-md">${i}</button>
        `;
        }
        if (pagination.current_page < pagination.total_pages) {
            paginationHTML += `
            <button onclick="changePage(${pagination.current_page + 1})" class="px-2 py-1 md:px-3 md:py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                <i class="fas fa-chevron-right md:hidden"></i><span class="hidden md:inline">Next</span>
            </button>
        `;
        }
        paginationHTML += '</div></div>';
        $('#paginationContainer').html(paginationHTML);
    }

    function changePage(page) {
        currentPage = page;
        loadRequests();
    }

    function toggleStatusDropdown(requestId) {
        $('[id^="status-dropdown-"]').addClass('hidden');
        $(`#status-dropdown-${requestId}`).toggleClass('hidden');
    }

    function updateStatus(requestId, status) {
        $.ajax({
            url: BUY_IN_STORE_ENDPOINT + '?action=update_status',
            type: 'POST',
            data: { request_id: requestId, status: status },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showAlert('success', response.message);
                    loadRequests();
                    $(`#status-dropdown-${requestId}`).addClass('hidden');
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function () {
                showAlert('error', 'Failed to update status');
            }
        });
    }

    function viewRequest(requestId) {
        $('#modalContent').html(`
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600">Loading request details...</p>
        </div>
    `);
        $('#requestModal').removeClass('hidden');
        $.ajax({
            url: BUY_IN_STORE_ENDPOINT + '?action=get_request_details',
            type: 'POST',
            data: { request_id: requestId },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    currentRequestId = requestId;
                    renderRequestDetails(response.data);
                } else {
                    $('#modalContent').html(`
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-circle text-4xl text-red-500 mb-4"></i>
                        <h4 class="text-lg font-semibold mb-2">Error</h4>
                        <p class="text-gray-600">${response.message}</p>
                    </div>
                `);
                }
            },
            error: function () {
                $('#modalContent').html(`
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-circle text-4xl text-red-500 mb-4"></i>
                    <h4 class="text-lg font-semibold mb-2">Connection Error</h4>
                    <p class="text-gray-600">Failed to load request details</p>
                </div>
            `);
            }
        });
    }

    function renderRequestDetails(request) {
        const statusColors = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'confirmed': 'bg-blue-100 text-blue-800',
            'completed': 'bg-green-100 text-green-800',
            'cancelled': 'bg-red-100 text-red-800'
        };
        const statusIcons = {
            'pending': 'fa-clock',
            'confirmed': 'fa-check-circle',
            'completed': 'fa-check-double',
            'cancelled': 'fa-times-circle'
        };
        const visitDate = new Date(request.visit_date);
        const createdDate = new Date(request.created_at);
        const totalValue = request.price * request.quantity;
        const modalContent = `
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-user-accent rounded-lg p-4 md:p-6">
                    <h4 class="text-lg font-semibold text-secondary mb-4 flex items-center">
                        <i class="fas fa-user mr-2 text-user-primary"></i>Customer Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-text">Full Name</label>
                            <p class="text-secondary">${request.first_name} ${request.last_name}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-text">Email</label>
                            <p class="text-secondary">${request.email}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-text">Phone</label>
                            <p class="text-secondary">${request.phone}</p>
                        </div>
                        ${request.alt_contact ? `<div><label class="text-sm font-medium text-gray-text">Alternative Contact</label><p class="text-secondary">${request.alt_contact}</p></div>` : ''}
                        ${request.alt_email ? `<div class="md:col-span-2"><label class="text-sm font-medium text-gray-text">Alternative Email</label><p class="text-secondary">${request.alt_email}</p></div>` : ''}
                    </div>
                </div>
                <div class="bg-user-accent rounded-lg p-4 md:p-6">
                    <h4 class="text-lg font-semibold text-secondary mb-4 flex items-center">
                        <i class="fas fa-box mr-2 text-user-primary"></i>Product Information
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-text">Product</label>
                            <p class="text-secondary font-medium">${request.product_title}</p>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div><label class="text-sm font-medium text-gray-text">Package</label><p class="text-secondary">${request.package_name}</p></div>
                            <div><label class="text-sm font-medium text-gray-text">Unit</label><p class="text-secondary">${request.si_unit}</p></div>
                            <div><label class="text-sm font-medium text-gray-text">Quantity</label><p class="text-secondary font-medium">${request.quantity}</p></div>
                            <div><label class="text-sm font-medium text-gray-text">Unit Price</label><p class="text-user-primary font-medium">UGX ${Number(request.price).toLocaleString()}</p></div>
                        </div>
                        <div class="border-t pt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-medium text-secondary">Total Value:</span>
                                <span class="text-xl font-bold text-user-primary">UGX ${totalValue.toLocaleString()}</span>
                            </div>
                            <p class="text-sm text-gray-text mt-1">${request.price_category.charAt(0).toUpperCase() + request.price_category.slice(1)} pricing</p>
                        </div>
                    </div>
                </div>
                <div class="bg-user-accent rounded-lg p-4 md:p-6">
                    <h4 class="text-lg font-semibold text-secondary mb-4 flex items-center">
                        <i class="fas fa-calendar mr-2 text-user-primary"></i>Visit Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-text">Requested Visit Date</label>
                            <p class="text-secondary font-medium">${visitDate.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-text">Request Status</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${statusColors[request.status]}">
                                <i class="fas ${statusIcons[request.status]} mr-2"></i>${request.status.charAt(0).toUpperCase() + request.status.slice(1)}
                            </span>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-text">Request Date</label>
                            <p class="text-secondary">${createdDate.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                        </div>
                        ${request.notes ? `<div class="md:col-span-2"><label class="text-sm font-medium text-gray-text">Customer Notes</label><p class="text-secondary bg-white p-3 rounded border">${request.notes}</p></div>` : ''}
                    </div>
                </div>
            </div>
            <div class="space-y-4">
                <div class="bg-user-accent rounded-lg p-4 md:p-6">
                    <h4 class="text-lg font-semibold text-secondary mb-4 flex items-center">
                        <i class="fas fa-phone mr-2 text-user-primary"></i>Quick Actions
                    </h4>
                    <div class="space-y-3">
                        <button onclick="callCustomer('${request.phone}')" class="w-full flex items-center justify-center px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-phone mr-2"></i>Call Customer
                        </button>
                        <button onclick="openEmailModal('${request.email}','${request.first_name} ${request.last_name}','${request.product_title}')" class="w-full flex items-center justify-center px-4 py-3 bg-user-primary text-white rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-envelope mr-2"></i>Send Email
                        </button>
                        <button onclick="openSmsModal('${request.phone}','${request.first_name}')" class="w-full flex items-center justify-center px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-sms mr-2"></i>Send SMS
                        </button>
                        ${request.alt_contact ? `<button onclick="callCustomer('${request.alt_contact}')" class="w-full flex items-center justify-center px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"><i class="fas fa-phone mr-2"></i>Call Alt. Number</button>` : ''}
                    </div>
                </div>
                <div class="bg-user-accent rounded-lg p-4 md:p-6">
                    <h4 class="text-lg font-semibold text-secondary mb-4 flex items-center">
                        <i class="fas fa-edit mr-2 text-user-primary"></i>Update Status
                    </h4>
                    <div class="space-y-2">
                        ${['pending', 'confirmed', 'completed', 'cancelled'].map(status => {
            if (status !== request.status) {
                const colors = { 'pending': 'bg-yellow-600 hover:bg-yellow-700', 'confirmed': 'bg-blue-600 hover:bg-blue-700', 'completed': 'bg-green-600 hover:bg-green-700', 'cancelled': 'bg-red-600 hover:bg-red-700' };
                return `<button onclick="updateStatusFromModal('${request.id}','${status}')" class="w-full flex items-center justify-center px-4 py-2 ${colors[status]} text-white rounded-lg transition-colors"><i class="fas ${statusIcons[status]} mr-2"></i>Mark as ${status.charAt(0).toUpperCase() + status.slice(1)}</button>`;
            }
            return '';
        }).join('')}
                    </div>
                </div>
            </div>
        </div>
    `;
        $('#modalContent').html(modalContent);
    }

    function updateStatusFromModal(requestId, status) {
        updateStatus(requestId, status);
    }

    function callCustomer(phone) {
        window.location.href = `tel:${phone}`;
    }

    function openEmailModal(email, customerName, productTitle) {
        $('#emailTo').val(email);
        $('#emailSubject').val(`Store Visit Request - ${productTitle}`);
        $('#emailMessage').val(`Dear ${customerName},\n\nThank you for your interest in visiting our store to discuss ${productTitle}.\n\nWe look forward to meeting with you.\n\nBest regards,\nStore Team`);
        $('#emailModal').removeClass('hidden');
    }

    function openSmsModal(phone, customerName) {
        $('#smsTo').val(phone);
        $('#smsMessage').val(`Hello ${customerName}, regarding your store visit request. Please contact us for more details.`);
        $('#smsCharCount').text($('#smsMessage').val().length);
        $('#smsModal').removeClass('hidden');
    }

    function sendEmail() {
        const message = $('#emailMessage').val();
        if (!message.trim()) {
            showAlert('error', 'Please enter a message');
            return;
        }
        $.ajax({
            url: BUY_IN_STORE_ENDPOINT + '?action=send_email',
            type: 'POST',
            data: { request_id: currentRequestId, message: message },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showAlert('success', response.message);
                    closeEmailModal();
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function () {
                showAlert('error', 'Failed to send email');
            }
        });
    }

    function sendSms() {
        const message = $('#smsMessage').val();
        if (!message.trim()) {
            showAlert('error', 'Please enter a message');
            return;
        }
        $.ajax({
            url: BUY_IN_STORE_ENDPOINT + '?action=send_sms',
            type: 'POST',
            data: { request_id: currentRequestId, message: message },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showAlert('success', response.message);
                    closeSmsModal();
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function () {
                showAlert('error', 'Failed to send SMS');
            }
        });
    }

    function closeModal() {
        $('#requestModal').addClass('hidden');
    }

    function closeEmailModal() {
        $('#emailModal').addClass('hidden');
        $('#emailMessage').val('');
    }

    function closeSmsModal() {
        $('#smsModal').addClass('hidden');
        $('#smsMessage').val('');
        $('#smsCharCount').text('0');
    }

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        const alertHTML = `<div class="${alertClass} border px-4 py-3 rounded-lg mb-4"><i class="fas ${iconClass} mr-2"></i>${message}</div>`;
        $('#alertContainer').html(alertHTML);
        setTimeout(() => { $('#alertContainer').html(''); }, 5000);
    }

    $(document).click(function (event) {
        if (!$(event.target).closest('[onclick^="toggleStatusDropdown"]').length) {
            $('[id^="status-dropdown-"]').addClass('hidden');
        }
    });

    $('#requestModal, #emailModal, #smsModal').click(function (event) {
        if (event.target === this) {
            if (this.id === 'requestModal') closeModal();
            if (this.id === 'emailModal') closeEmailModal();
            if (this.id === 'smsModal') closeSmsModal();
        }
    });
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>