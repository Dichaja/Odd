<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Nature of Business';
$activeNav = 'nature-of-business';
ob_start();
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Nature of Business</h1>
            <p class="text-sm text-gray-text mt-1">Manage nature of businesses for vendor stores</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <button id="addNewCategory"
                class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-success/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-plus"></i>
                <span>Add New</span>
            </button>
            <a href="vendor-stores"
                class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Vendor Stores</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">Category List</h2>
                <p class="text-sm text-gray-text mt-1">
                    <span id="category-count">0</span> categories found
                </p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchCategories" placeholder="Search categories..."
                        class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select id="filterStatus"
                        class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                        <option value="" selected>All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="responsive-table-desktop overflow-x-auto">
            <table class="w-full" id="categories-table">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Category Name</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Vendors</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Description</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Status</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text w-32">Actions</th>
                    </tr>
                </thead>
                <tbody id="categories-table-body">
                    <tr id="loading-row">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex justify-center">
                                <i class="fas fa-spinner fa-spin text-primary text-xl"></i>
                            </div>
                            <p class="mt-2">Loading business categories...</p>
                        </td>
                    </tr>
                    <tr id="no-data-row" class="hidden">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex justify-center">
                                <i class="fas fa-folder-open text-gray-400 text-3xl"></i>
                            </div>
                            <p class="mt-2">No business categories found</p>
                            <button id="add-first-category"
                                class="mt-3 px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary/90">
                                Add Your First Category
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="responsive-table-mobile p-4" id="categories-mobile">
            <div id="mobile-loading" class="py-8 text-center text-gray-500">
                <div class="flex justify-center">
                    <i class="fas fa-spinner fa-spin text-primary text-xl"></i>
                </div>
                <p class="mt-2">Loading business categories...</p>
            </div>
            <div id="mobile-no-data" class="py-8 text-center text-gray-500 hidden">
                <div class="flex justify-center">
                    <i class="fas fa-folder-open text-gray-400 text-3xl"></i>
                </div>
                <p class="mt-2">No business categories found</p>
                <button id="mobile-add-first-category"
                    class="mt-3 px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary/90">
                    Add Your First Category
                </button>
            </div>
        </div>

        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start">0</span> to <span id="showing-end">0</span> of <span
                    id="total-categories">0</span> categories
            </div>
            <div class="flex items-center gap-2">
                <button id="prev-page"
                    class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination-numbers" class="flex items-center">
                </div>
                <button id="next-page"
                    class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="categoryModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideCategoryModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="modal-title">Add New Category</h3>
            <button onclick="hideCategoryModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="categoryForm" class="space-y-4">
                <input type="hidden" id="category-id" value="">

                <div>
                    <label for="category-name" class="block text-sm font-medium text-gray-700 mb-1">Category Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="category-name"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        placeholder="Enter category name" required>
                </div>

                <div>
                    <label for="category-description"
                        class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="category-description" rows="3"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        placeholder="Enter category description"></textarea>
                </div>

                <div>
                    <label for="category-status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="category-status"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div>
                    <label for="category-icon" class="block text-sm font-medium text-gray-700 mb-1">Icon
                        (Optional)</label>
                    <div class="flex items-center gap-3">
                        <div id="selected-icon"
                            class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-tag text-gray-400"></i>
                        </div>
                        <button type="button" id="choose-icon"
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                            Choose Icon
                        </button>
                    </div>
                    <input type="hidden" id="category-icon-value" value="">
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideCategoryModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="saveCategory" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                Save Category
            </button>
        </div>
    </div>
</div>

<div id="deleteCategoryModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideDeleteModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Delete Category</h3>
            <button onclick="hideDeleteModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-2">Are you sure you want to delete this category?</p>
            <p class="text-yellow-600 text-sm mb-4">Warning: This may affect vendors currently assigned to this
                category.</p>

            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-500">Category:</div>
                    <div class="font-medium text-gray-900" id="delete-category-name"></div>
                    <div class="text-gray-500">Vendors Affected:</div>
                    <div class="font-medium text-gray-900" id="delete-category-vendors"></div>
                </div>
            </div>

            <div>
                <label class="flex items-center">
                    <input type="checkbox" id="delete-confirm"
                        class="form-checkbox h-4 w-4 text-primary rounded border-gray-300 focus:ring-primary">
                    <span class="ml-2 text-sm text-gray-700">I understand this action cannot be undone</span>
                </label>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideDeleteModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="confirmDelete"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
                Delete
            </button>
        </div>
    </div>
</div>

<div id="iconModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideIconModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Select Icon</h3>
            <button onclick="hideIconModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <input type="text" id="icon-search"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                    placeholder="Search icons...">
            </div>

            <div class="grid grid-cols-8 gap-3 max-h-[400px] overflow-y-auto" id="icon-grid">
                <!-- Icons will be populated dynamically -->
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideIconModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="selectIcon" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                Select
            </button>
        </div>
    </div>
</div>

<div id="messageModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideMessageModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="message-title">Message</h3>
            <button onclick="hideMessageModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="flex items-center gap-3">
                <div id="message-icon" class="w-10 h-10 rounded-full flex items-center justify-center bg-green-100">
                    <i class="fas fa-check text-green-500"></i>
                </div>
                <p id="message-text" class="text-gray-700"></p>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end">
            <button onclick="hideMessageModal()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                OK
            </button>
        </div>
    </div>
</div>

<style>
    .responsive-table-mobile {
        display: none;
    }

    @media (max-width: 768px) {
        .responsive-table-desktop {
            display: none;
        }

        .responsive-table-mobile {
            display: block;
        }

        .mobile-card {
            background: white;
            border: 1px solid #f3f4f6;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .mobile-card-header {
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f3f4f6;
        }

        .mobile-card-content {
            padding: 1rem;
        }

        .mobile-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .mobile-grid-item {
            display: flex;
            flex-direction: column;
        }

        .mobile-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .mobile-value {
            font-size: 0.875rem;
            font-weight: 500;
            color: #111827;
        }

        .mobile-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f3f4f6;
        }
    }
</style>

<script>
    let businessTypes = [];
    let currentPage = 1;
    let itemsPerPage = 10;
    let totalPages = 1;
    let selectedIcon = '';

    const API_URL = '<?= BASE_URL ?>admin/fetch/manageNatureOfBusiness.php';

    const iconsList = [
        'fa-address-book', 'fa-address-card', 'fa-adjust', 'fa-air-freshener', 'fa-align-center',
        'fa-align-justify', 'fa-align-left', 'fa-align-right', 'fa-allergies', 'fa-ambulance',
        'fa-american-sign-language-interpreting', 'fa-anchor', 'fa-angle-double-down', 'fa-angle-double-left',
        'fa-angle-double-right', 'fa-angle-double-up', 'fa-angle-down', 'fa-angle-left', 'fa-angle-right',
        'fa-angle-up', 'fa-angry', 'fa-apple-alt', 'fa-archive', 'fa-archway', 'fa-arrow-alt-circle-down',
        'fa-arrow-alt-circle-left', 'fa-arrow-alt-circle-right', 'fa-arrow-alt-circle-up', 'fa-arrow-circle-down',
        'fa-arrow-circle-left', 'fa-arrow-circle-right', 'fa-arrow-circle-up', 'fa-arrow-down', 'fa-arrow-left',
        'fa-arrow-right', 'fa-arrow-up', 'fa-arrows-alt', 'fa-arrows-alt-h', 'fa-arrows-alt-v', 'fa-assistive-listening-systems',
        'fa-asterisk', 'fa-at', 'fa-atlas', 'fa-atom', 'fa-audio-description', 'fa-award', 'fa-baby', 'fa-baby-carriage',
        'fa-backspace', 'fa-backward', 'fa-bacon', 'fa-balance-scale', 'fa-balance-scale-left', 'fa-balance-scale-right',
        'fa-ban', 'fa-band-aid', 'fa-barcode', 'fa-bars', 'fa-baseball-ball', 'fa-basketball-ball', 'fa-bath',
        'fa-battery-empty', 'fa-battery-full', 'fa-battery-half', 'fa-battery-quarter', 'fa-battery-three-quarters',
        'fa-bed', 'fa-beer', 'fa-bell', 'fa-bell-slash', 'fa-bezier-curve', 'fa-bible', 'fa-bicycle', 'fa-biking',
        'fa-binoculars', 'fa-biohazard', 'fa-birthday-cake', 'fa-blender', 'fa-blender-phone', 'fa-blind', 'fa-blog',
        'fa-bold', 'fa-bolt', 'fa-bomb', 'fa-bone', 'fa-bong', 'fa-book', 'fa-book-dead', 'fa-book-medical',
        'fa-book-open', 'fa-book-reader', 'fa-bookmark', 'fa-border-all', 'fa-border-none', 'fa-border-style',
        'fa-bowling-ball', 'fa-box', 'fa-box-open', 'fa-boxes', 'fa-braille', 'fa-brain', 'fa-bread-slice',
        'fa-briefcase', 'fa-briefcase-medical', 'fa-broadcast-tower', 'fa-broom', 'fa-brush', 'fa-bug', 'fa-building',
        'fa-bullhorn', 'fa-bullseye', 'fa-burn', 'fa-bus', 'fa-bus-alt', 'fa-business-time', 'fa-calculator',
        'fa-calendar', 'fa-calendar-alt', 'fa-calendar-check', 'fa-calendar-day', 'fa-calendar-minus', 'fa-calendar-plus',
        'fa-calendar-times', 'fa-calendar-week', 'fa-camera', 'fa-camera-retro', 'fa-campground', 'fa-candy-cane',
        'fa-cannabis', 'fa-capsules', 'fa-car', 'fa-car-alt', 'fa-car-battery', 'fa-car-crash', 'fa-car-side',
        'fa-caret-down', 'fa-caret-left', 'fa-caret-right', 'fa-caret-up', 'fa-carrot', 'fa-cart-arrow-down',
        'fa-cart-plus', 'fa-cash-register', 'fa-cat', 'fa-certificate', 'fa-chair', 'fa-chalkboard', 'fa-chalkboard-teacher',
        'fa-charging-station', 'fa-chart-area', 'fa-chart-bar', 'fa-chart-line', 'fa-chart-pie', 'fa-check',
        'fa-check-circle', 'fa-check-double', 'fa-check-square', 'fa-cheese', 'fa-chess', 'fa-chess-bishop',
        'fa-chess-board', 'fa-chess-king', 'fa-chess-knight', 'fa-chess-pawn', 'fa-chess-queen', 'fa-chess-rook',
        'fa-chevron-circle-down', 'fa-chevron-circle-left', 'fa-chevron-circle-right', 'fa-chevron-circle-up',
        'fa-chevron-down', 'fa-chevron-left', 'fa-chevron-right', 'fa-chevron-up', 'fa-child', 'fa-church',
        'fa-circle', 'fa-circle-notch', 'fa-city', 'fa-clinic-medical', 'fa-clipboard', 'fa-clipboard-check',
        'fa-clipboard-list', 'fa-clock', 'fa-clone', 'fa-closed-captioning', 'fa-cloud', 'fa-cloud-download-alt',
        'fa-cloud-meatball', 'fa-cloud-moon', 'fa-cloud-moon-rain', 'fa-cloud-rain', 'fa-cloud-showers-heavy',
        'fa-cloud-sun', 'fa-cloud-sun-rain', 'fa-cloud-upload-alt', 'fa-cocktail', 'fa-code', 'fa-code-branch',
        'fa-coffee', 'fa-cog', 'fa-cogs', 'fa-coins', 'fa-columns', 'fa-comment', 'fa-comment-alt', 'fa-comment-dollar',
        'fa-comment-dots', 'fa-comment-medical', 'fa-comment-slash', 'fa-comments', 'fa-comments-dollar', 'fa-compact-disc',
        'fa-compass', 'fa-compress', 'fa-compress-arrows-alt', 'fa-concierge-bell', 'fa-cookie', 'fa-cookie-bite',
        'fa-copy', 'fa-copyright', 'fa-couch', 'fa-credit-card', 'fa-crop', 'fa-crop-alt', 'fa-cross', 'fa-crosshairs',
        'fa-crow', 'fa-crown', 'fa-crutch', 'fa-cube', 'fa-cubes', 'fa-cut', 'fa-database', 'fa-deaf', 'fa-democrat',
        'fa-desktop', 'fa-dharmachakra', 'fa-diagnoses', 'fa-dice', 'fa-dice-d20', 'fa-dice-d6', 'fa-dice-five',
        'fa-dice-four', 'fa-dice-one', 'fa-dice-six', 'fa-dice-three', 'fa-dice-two', 'fa-digital-tachograph',
        'fa-directions', 'fa-divide', 'fa-dizzy', 'fa-dna', 'fa-dog', 'fa-dollar-sign', 'fa-dolly', 'fa-dolly-flatbed',
        'fa-donate', 'fa-door-closed', 'fa-door-open', 'fa-dot-circle', 'fa-dove', 'fa-download', 'fa-drafting-compass',
        'fa-dragon', 'fa-draw-polygon', 'fa-drum', 'fa-drum-steelpan', 'fa-drumstick-bite', 'fa-dumbbell', 'fa-dumpster',
        'fa-dumpster-fire', 'fa-dungeon', 'fa-edit', 'fa-egg', 'fa-eject', 'fa-ellipsis-h', 'fa-ellipsis-v',
        'fa-envelope', 'fa-envelope-open', 'fa-envelope-open-text', 'fa-envelope-square', 'fa-equals', 'fa-eraser',
        'fa-ethernet', 'fa-euro-sign', 'fa-exchange-alt', 'fa-exclamation', 'fa-exclamation-circle', 'fa-exclamation-triangle',
        'fa-expand', 'fa-expand-arrows-alt', 'fa-external-link-alt', 'fa-external-link-square-alt', 'fa-eye',
        'fa-eye-dropper', 'fa-eye-slash', 'fa-fan', 'fa-fast-backward', 'fa-fast-forward', 'fa-fax', 'fa-feather',
        'fa-feather-alt', 'fa-female', 'fa-fighter-jet', 'fa-file', 'fa-file-alt', 'fa-file-archive', 'fa-file-audio',
        'fa-file-code', 'fa-file-contract', 'fa-file-csv', 'fa-file-download', 'fa-file-excel', 'fa-file-export',
        'fa-file-image', 'fa-file-import', 'fa-file-invoice', 'fa-file-invoice-dollar', 'fa-file-medical',
        'fa-file-medical-alt', 'fa-file-pdf', 'fa-file-powerpoint', 'fa-file-prescription', 'fa-file-signature',
        'fa-file-upload', 'fa-file-video', 'fa-file-word', 'fa-fill', 'fa-fill-drip', 'fa-film', 'fa-filter',
        'fa-fingerprint', 'fa-fire', 'fa-fire-alt', 'fa-fire-extinguisher', 'fa-first-aid', 'fa-fish', 'fa-fist-raised',
        'fa-flag', 'fa-flag-checkered', 'fa-flag-usa', 'fa-flask', 'fa-flushed', 'fa-folder', 'fa-folder-minus',
        'fa-folder-open', 'fa-folder-plus', 'fa-font', 'fa-football-ball', 'fa-forward', 'fa-frog', 'fa-frown',
        'fa-frown-open', 'fa-funnel-dollar', 'fa-futbol', 'fa-gamepad', 'fa-gas-pump', 'fa-gavel', 'fa-gem',
        'fa-genderless', 'fa-ghost', 'fa-gift', 'fa-gifts', 'fa-glass-cheers', 'fa-glass-martini', 'fa-glass-martini-alt',
        'fa-glass-whiskey', 'fa-glasses', 'fa-globe', 'fa-globe-africa', 'fa-globe-americas', 'fa-globe-asia',
        'fa-globe-europe', 'fa-golf-ball', 'fa-gopuram', 'fa-graduation-cap', 'fa-greater-than', 'fa-greater-than-equal',
        'fa-grimace', 'fa-grin', 'fa-grin-alt', 'fa-grin-beam', 'fa-grin-beam-sweat', 'fa-grin-hearts', 'fa-grin-squint',
        'fa-grin-squint-tears', 'fa-grin-stars', 'fa-grin-tears', 'fa-grin-tongue', 'fa-grin-tongue-squint',
        'fa-grin-tongue-wink', 'fa-grin-wink', 'fa-grip-horizontal', 'fa-grip-lines', 'fa-grip-lines-vertical',
        'fa-grip-vertical', 'fa-guitar', 'fa-h-square', 'fa-hamburger', 'fa-hammer', 'fa-hamsa', 'fa-hand-holding',
        'fa-hand-holding-heart', 'fa-hand-holding-usd', 'fa-hand-lizard', 'fa-hand-middle-finger', 'fa-hand-paper',
        'fa-hand-peace', 'fa-hand-point-down', 'fa-hand-point-left', 'fa-hand-point-right', 'fa-hand-point-up',
        'fa-hand-pointer', 'fa-hand-rock', 'fa-hand-scissors', 'fa-hand-spock', 'fa-hands', 'fa-hands-helping',
        'fa-handshake', 'fa-hanukiah', 'fa-hard-hat', 'fa-hashtag', 'fa-hat-wizard', 'fa-haykal', 'fa-hdd',
        'fa-heading', 'fa-headphones', 'fa-headphones-alt', 'fa-headset', 'fa-heart', 'fa-heart-broken', 'fa-heartbeat',
        'fa-helicopter', 'fa-highlighter', 'fa-hiking', 'fa-hippo', 'fa-history', 'fa-hockey-puck', 'fa-holly-berry',
        'fa-home', 'fa-horse', 'fa-horse-head', 'fa-hospital', 'fa-hospital-alt', 'fa-hospital-symbol', 'fa-hot-tub',
        'fa-hotdog', 'fa-hotel', 'fa-hourglass', 'fa-hourglass-end', 'fa-hourglass-half', 'fa-hourglass-start',
        'fa-house-damage', 'fa-hryvnia', 'fa-i-cursor', 'fa-ice-cream', 'fa-icicles', 'fa-icons', 'fa-id-badge',
        'fa-id-card', 'fa-id-card-alt', 'fa-igloo', 'fa-image', 'fa-images', 'fa-inbox', 'fa-indent', 'fa-industry',
        'fa-infinity', 'fa-info', 'fa-info-circle', 'fa-italic', 'fa-jedi', 'fa-joint', 'fa-journal-whills',
        'fa-kaaba', 'fa-key', 'fa-keyboard', 'fa-khanda', 'fa-kiss', 'fa-kiss-beam', 'fa-kiss-wink-heart',
        'fa-kiwi-bird', 'fa-landmark', 'fa-language', 'fa-laptop', 'fa-laptop-code', 'fa-laptop-medical', 'fa-laugh',
        'fa-laugh-beam', 'fa-laugh-squint', 'fa-laugh-wink', 'fa-layer-group', 'fa-leaf', 'fa-lemon', 'fa-less-than',
        'fa-less-than-equal', 'fa-level-down-alt', 'fa-level-up-alt', 'fa-life-ring', 'fa-lightbulb', 'fa-link',
        'fa-lira-sign', 'fa-list', 'fa-list-alt', 'fa-list-ol', 'fa-list-ul', 'fa-location-arrow', 'fa-lock',
        'fa-lock-open', 'fa-long-arrow-alt-down', 'fa-long-arrow-alt-left', 'fa-long-arrow-alt-right', 'fa-long-arrow-alt-up',
        'fa-low-vision', 'fa-luggage-cart', 'fa-magic', 'fa-magnet', 'fa-mail-bulk', 'fa-male', 'fa-map', 'fa-map-marked',
        'fa-map-marked-alt', 'fa-map-marker', 'fa-map-marker-alt', 'fa-map-pin', 'fa-map-signs', 'fa-marker',
        'fa-mars', 'fa-mars-double', 'fa-mars-stroke', 'fa-mars-stroke-h', 'fa-mars-stroke-v', 'fa-mask', 'fa-medal',
        'fa-medkit', 'fa-meh', 'fa-meh-blank', 'fa-meh-rolling-eyes', 'fa-memory', 'fa-menorah', 'fa-mercury',
        'fa-meteor', 'fa-microchip', 'fa-microphone', 'fa-microphone-alt', 'fa-microphone-alt-slash', 'fa-microphone-slash',
        'fa-microscope', 'fa-minus', 'fa-minus-circle', 'fa-minus-square', 'fa-mitten', 'fa-mobile', 'fa-mobile-alt',
        'fa-money-bill', 'fa-money-bill-alt', 'fa-money-bill-wave', 'fa-money-bill-wave-alt', 'fa-money-check',
        'fa-money-check-alt', 'fa-monument', 'fa-moon', 'fa-mortar-pestle', 'fa-mosque', 'fa-motorcycle', 'fa-mountain',
        'fa-mouse-pointer', 'fa-mug-hot', 'fa-music', 'fa-network-wired', 'fa-neuter', 'fa-newspaper', 'fa-not-equal',
        'fa-notes-medical', 'fa-object-group', 'fa-object-ungroup', 'fa-oil-can', 'fa-om', 'fa-otter', 'fa-outdent',
        'fa-pager', 'fa-paint-brush', 'fa-paint-roller', 'fa-palette', 'fa-pallet', 'fa-paper-plane', 'fa-paperclip',
        'fa-parachute-box', 'fa-paragraph', 'fa-parking', 'fa-passport', 'fa-pastafarianism', 'fa-paste', 'fa-pause',
        'fa-pause-circle', 'fa-paw', 'fa-peace', 'fa-pen', 'fa-pen-alt', 'fa-pen-fancy', 'fa-pen-nib', 'fa-pen-square',
        'fa-pencil-alt', 'fa-pencil-ruler', 'fa-people-carry', 'fa-pepper-hot', 'fa-percent', 'fa-percentage',
        'fa-person-booth', 'fa-phone', 'fa-phone-alt', 'fa-phone-slash', 'fa-phone-square', 'fa-phone-square-alt',
        'fa-phone-volume', 'fa-photo-video', 'fa-piggy-bank', 'fa-pills', 'fa-pizza-slice', 'fa-place-of-worship',
        'fa-plane', 'fa-plane-arrival', 'fa-plane-departure', 'fa-play', 'fa-play-circle', 'fa-plug', 'fa-plus',
        'fa-plus-circle', 'fa-plus-square', 'fa-podcast', 'fa-poll', 'fa-poll-h', 'fa-poo', 'fa-poo-storm',
        'fa-poop', 'fa-portrait', 'fa-pound-sign', 'fa-power-off', 'fa-pray', 'fa-praying-hands', 'fa-prescription',
        'fa-prescription-bottle', 'fa-prescription-bottle-alt', 'fa-print', 'fa-procedures', 'fa-project-diagram',
        'fa-puzzle-piece', 'fa-qrcode', 'fa-question', 'fa-question-circle', 'fa-quidditch', 'fa-quote-left',
        'fa-quote-right', 'fa-quran', 'fa-radiation', 'fa-radiation-alt', 'fa-rainbow', 'fa-random', 'fa-receipt',
        'fa-recycle', 'fa-redo', 'fa-redo-alt', 'fa-registered', 'fa-remove-format', 'fa-reply', 'fa-reply-all',
        'fa-republican', 'fa-restroom', 'fa-retweet', 'fa-ribbon', 'fa-ring', 'fa-road', 'fa-robot', 'fa-rocket',
        'fa-route', 'fa-rss', 'fa-rss-square', 'fa-ruble-sign', 'fa-ruler', 'fa-ruler-combined', 'fa-ruler-horizontal',
        'fa-ruler-vertical', 'fa-running', 'fa-rupee-sign', 'fa-sad-cry', 'fa-sad-tear', 'fa-satellite', 'fa-satellite-dish',
        'fa-save', 'fa-school', 'fa-screwdriver', 'fa-scroll', 'fa-sd-card', 'fa-search', 'fa-search-dollar',
        'fa-search-location', 'fa-search-minus', 'fa-search-plus', 'fa-seedling', 'fa-server', 'fa-shapes',
        'fa-share', 'fa-share-alt', 'fa-share-alt-square', 'fa-share-square', 'fa-shekel-sign', 'fa-shield-alt',
        'fa-ship', 'fa-shipping-fast', 'fa-shoe-prints', 'fa-shopping-bag', 'fa-shopping-basket', 'fa-shopping-cart',
        'fa-shower', 'fa-shuttle-van', 'fa-sign', 'fa-sign-in-alt', 'fa-sign-language', 'fa-sign-out-alt',
        'fa-signal', 'fa-signature', 'fa-sim-card', 'fa-sitemap', 'fa-skating', 'fa-skiing', 'fa-skiing-nordic',
        'fa-skull', 'fa-skull-crossbones', 'fa-slash', 'fa-sleigh', 'fa-sliders-h', 'fa-smile', 'fa-smile-beam',
        'fa-smile-wink', 'fa-smog', 'fa-smoking', 'fa-smoking-ban', 'fa-sms', 'fa-snowboarding', 'fa-snowflake',
        'fa-snowman', 'fa-snowplow', 'fa-socks', 'fa-solar-panel', 'fa-sort', 'fa-sort-alpha-down', 'fa-sort-alpha-down-alt',
        'fa-sort-alpha-up', 'fa-sort-alpha-up-alt', 'fa-sort-amount-down', 'fa-sort-amount-down-alt', 'fa-sort-amount-up',
        'fa-sort-amount-up-alt', 'fa-sort-down', 'fa-sort-numeric-down', 'fa-sort-numeric-down-alt', 'fa-sort-numeric-up',
        'fa-sort-numeric-up-alt', 'fa-sort-up', 'fa-spa', 'fa-space-shuttle', 'fa-spell-check', 'fa-spider',
        'fa-spinner', 'fa-splotch', 'fa-spray-can', 'fa-square', 'fa-square-full', 'fa-square-root-alt', 'fa-stamp',
        'fa-star', 'fa-star-and-crescent', 'fa-star-half', 'fa-star-half-alt', 'fa-star-of-david', 'fa-star-of-life',
        'fa-step-backward', 'fa-step-forward', 'fa-stethoscope', 'fa-sticky-note', 'fa-stop', 'fa-stop-circle',
        'fa-stopwatch', 'fa-store', 'fa-store-alt', 'fa-stream', 'fa-street-view', 'fa-strikethrough', 'fa-stroopwafel',
        'fa-subscript', 'fa-subway', 'fa-suitcase', 'fa-suitcase-rolling', 'fa-sun', 'fa-superscript', 'fa-surprise',
        'fa-swatchbook', 'fa-swimmer', 'fa-swimming-pool', 'fa-synagogue', 'fa-sync', 'fa-sync-alt', 'fa-syringe',
        'fa-table', 'fa-table-tennis', 'fa-tablet', 'fa-tablet-alt', 'fa-tablets', 'fa-tachometer-alt', 'fa-tag',
        'fa-tags', 'fa-tape', 'fa-tasks', 'fa-taxi', 'fa-teeth', 'fa-teeth-open', 'fa-temperature-high',
        'fa-temperature-low', 'fa-tenge', 'fa-terminal', 'fa-text-height', 'fa-text-width', 'fa-th', 'fa-th-large',
        'fa-th-list', 'fa-theater-masks', 'fa-thermometer', 'fa-thermometer-empty', 'fa-thermometer-full',
        'fa-thermometer-half', 'fa-thermometer-quarter', 'fa-thermometer-three-quarters', 'fa-thumbs-down',
        'fa-thumbs-up', 'fa-thumbtack', 'fa-ticket-alt', 'fa-times', 'fa-times-circle', 'fa-tint', 'fa-tint-slash',
        'fa-tired', 'fa-toggle-off', 'fa-toggle-on', 'fa-toilet', 'fa-toilet-paper', 'fa-toolbox', 'fa-tools',
        'fa-tooth', 'fa-torah', 'fa-torii-gate', 'fa-tractor', 'fa-trademark', 'fa-traffic-light', 'fa-train',
        'fa-tram', 'fa-transgender', 'fa-transgender-alt', 'fa-trash', 'fa-trash-alt', 'fa-trash-restore',
        'fa-trash-restore-alt', 'fa-tree', 'fa-trophy', 'fa-truck', 'fa-truck-loading', 'fa-truck-monster',
        'fa-truck-moving', 'fa-truck-pickup', 'fa-tshirt', 'fa-tty', 'fa-tv', 'fa-umbrella', 'fa-umbrella-beach',
        'fa-underline', 'fa-undo', 'fa-undo-alt', 'fa-universal-access', 'fa-university', 'fa-unlink', 'fa-unlock',
        'fa-unlock-alt', 'fa-upload', 'fa-user', 'fa-user-alt', 'fa-user-alt-slash', 'fa-user-astronaut',
        'fa-user-check', 'fa-user-circle', 'fa-user-clock', 'fa-user-cog', 'fa-user-edit', 'fa-user-friends',
        'fa-user-graduate', 'fa-user-injured', 'fa-user-lock', 'fa-user-md', 'fa-user-minus', 'fa-user-ninja',
        'fa-user-nurse', 'fa-user-plus', 'fa-user-secret', 'fa-user-shield', 'fa-user-slash', 'fa-user-tag',
        'fa-user-tie', 'fa-user-times', 'fa-users', 'fa-users-cog', 'fa-utensil-spoon', 'fa-utensils', 'fa-vector-square',
        'fa-venus', 'fa-venus-double', 'fa-venus-mars', 'fa-vial', 'fa-vials', 'fa-video', 'fa-video-slash',
        'fa-vihara', 'fa-voicemail', 'fa-volleyball-ball', 'fa-volume-down', 'fa-volume-mute', 'fa-volume-off',
        'fa-volume-up', 'fa-vote-yea', 'fa-vr-cardboard', 'fa-walking', 'fa-wallet', 'fa-warehouse', 'fa-water',
        'fa-wave-square', 'fa-weight', 'fa-weight-hanging', 'fa-wheelchair', 'fa-wifi', 'fa-wind', 'fa-window-close',
        'fa-window-maximize', 'fa-window-minimize', 'fa-window-restore', 'fa-wine-bottle', 'fa-wine-glass',
        'fa-wine-glass-alt', 'fa-won-sign', 'fa-wrench', 'fa-x-ray', 'fa-yen-sign', 'fa-yin-yang'
    ];

    document.addEventListener('DOMContentLoaded', function () {
        fetchBusinessTypes();

        document.getElementById('addNewCategory').addEventListener('click', function () {
            showCategoryModal();
        });

        document.getElementById('add-first-category').addEventListener('click', function () {
            showCategoryModal();
        });

        document.getElementById('mobile-add-first-category').addEventListener('click', function () {
            showCategoryModal();
        });

        document.getElementById('choose-icon').addEventListener('click', function () {
            showIconModal();
        });

        populateIconGrid();

        document.getElementById('selectIcon').addEventListener('click', function () {
            if (selectedIcon) {
                const selectedIconElement = document.getElementById('selected-icon');
                selectedIconElement.innerHTML = `<i class="fas ${selectedIcon} text-primary"></i>`;
                document.getElementById('category-icon-value').value = selectedIcon;
            }
            hideIconModal();
        });

        document.getElementById('delete-confirm').addEventListener('change', function () {
            document.getElementById('confirmDelete').disabled = !this.checked;
        });

        document.getElementById('saveCategory').addEventListener('click', function () {
            saveCategory();
        });

        document.getElementById('confirmDelete').addEventListener('click', function () {
            const categoryId = this.getAttribute('data-id');
            deleteCategory(categoryId);
        });

        document.getElementById('searchCategories').addEventListener('input', function () {
            filterBusinessTypes();
        });

        document.getElementById('filterStatus').addEventListener('change', function () {
            filterBusinessTypes();
        });

        document.getElementById('icon-search').addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();

            document.querySelectorAll('.icon-item').forEach(item => {
                const iconName = item.getAttribute('data-icon').toLowerCase();
                if (iconName.includes(query)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        });

        document.getElementById('prev-page').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                renderBusinessTypes();
            }
        });

        document.getElementById('next-page').addEventListener('click', function () {
            if (currentPage < totalPages) {
                currentPage++;
                renderBusinessTypes();
            }
        });
    });

    function populateIconGrid() {
        const iconGrid = document.getElementById('icon-grid');
        iconGrid.innerHTML = '';

        iconsList.forEach(icon => {
            const iconItem = document.createElement('div');
            iconItem.className = 'icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors';
            iconItem.setAttribute('data-icon', icon);
            iconItem.innerHTML = `<i class="fas ${icon} text-xl"></i>`;

            iconItem.addEventListener('click', function () {
                selectedIcon = icon;

                document.querySelectorAll('.icon-item').forEach(i => i.classList.remove('border-primary', 'bg-primary/10'));
                this.classList.add('border-primary', 'bg-primary/10');
            });

            iconGrid.appendChild(iconItem);
        });
    }

    function fetchBusinessTypes() {
        fetch(`${API_URL}?action=getBusinessTypes`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    businessTypes = data.businessTypes;
                    renderBusinessTypes();
                } else {
                    showMessage('Error', data.message || 'Failed to fetch business types', 'error');
                }
            })
            .catch(error => {
                console.error('Error fetching business types:', error);
                showMessage('Error', 'Failed to fetch business types. Please try again later.', 'error');
            });
    }

    function filterBusinessTypes() {
        const searchQuery = document.getElementById('searchCategories').value.trim().toLowerCase();
        const statusFilter = document.getElementById('filterStatus').value;

        let filteredTypes = businessTypes;

        if (searchQuery) {
            filteredTypes = filteredTypes.filter(type =>
                type.name.toLowerCase().includes(searchQuery) ||
                (type.description && type.description.toLowerCase().includes(searchQuery))
            );
        }

        if (statusFilter) {
            filteredTypes = filteredTypes.filter(type => type.status === statusFilter);
        }

        currentPage = 1;

        renderBusinessTypes(filteredTypes);
    }

    function renderBusinessTypes(typesToRender = businessTypes) {
        const tableBody = document.getElementById('categories-table-body');
        const mobileContainer = document.getElementById('categories-mobile');
        const loadingRow = document.getElementById('loading-row');
        const noDataRow = document.getElementById('no-data-row');
        const mobileLoading = document.getElementById('mobile-loading');
        const mobileNoData = document.getElementById('mobile-no-data');

        loadingRow.classList.add('hidden');
        mobileLoading.classList.add('hidden');

        document.getElementById('category-count').textContent = typesToRender.length;
        document.getElementById('total-categories').textContent = typesToRender.length;

        totalPages = Math.ceil(typesToRender.length / itemsPerPage);
        const start = (currentPage - 1) * itemsPerPage;
        const end = Math.min(start + itemsPerPage, typesToRender.length);
        const paginatedTypes = typesToRender.slice(start, end);

        document.getElementById('showing-start').textContent = typesToRender.length ? start + 1 : 0;
        document.getElementById('showing-end').textContent = end;

        document.getElementById('prev-page').disabled = currentPage === 1;
        document.getElementById('next-page').disabled = currentPage === totalPages || totalPages === 0;

        renderPaginationNumbers();

        const tableRows = tableBody.querySelectorAll('tr:not(#loading-row):not(#no-data-row)');
        tableRows.forEach(row => row.remove());

        const mobileCards = mobileContainer.querySelectorAll('.mobile-card');
        mobileCards.forEach(card => card.remove());

        if (typesToRender.length === 0) {
            noDataRow.classList.remove('hidden');
            mobileNoData.classList.remove('hidden');
            return;
        } else {
            noDataRow.classList.add('hidden');
            mobileNoData.classList.add('hidden');
        }

        paginatedTypes.forEach(type => {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-100 hover:bg-gray-50 transition-colors';

            const statusClass = type.status === 'active'
                ? 'bg-green-100 text-green-800'
                : 'bg-yellow-100 text-yellow-800';
            const statusText = type.status === 'active' ? 'Active' : 'Inactive';

            row.innerHTML = `
                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                    <div class="flex items-center gap-2">
                        ${type.icon ? `<i class="fas ${type.icon} text-primary"></i>` : ''}
                        ${type.name}
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-text">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 whitespace-nowrap">
                        ${type.vendor_count} vendors
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-text">${type.description || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-text">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                        ${statusText}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm">
                    <div class="flex items-center gap-2">
                        <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="${type.id}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-delete text-red-600 hover:text-red-800" data-id="${type.id}" title="Delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            `;

            tableBody.insertBefore(row, noDataRow);

            row.querySelector('.btn-edit').addEventListener('click', function () {
                const categoryId = this.getAttribute('data-id');
                showCategoryModal(categoryId);
            });

            row.querySelector('.btn-delete').addEventListener('click', function () {
                const categoryId = this.getAttribute('data-id');
                showDeleteModal(categoryId);
            });
        });

        paginatedTypes.forEach(type => {
            const statusClass = type.status === 'active'
                ? 'bg-green-100 text-green-800'
                : 'bg-yellow-100 text-yellow-800';
            const statusText = type.status === 'active' ? 'Active' : 'Inactive';

            const card = document.createElement('div');
            card.className = 'mobile-card mb-4';
            card.innerHTML = `
                <div class="mobile-card-header">
                    <div>
                        <div class="font-medium text-gray-900 flex items-center gap-2">
                            ${type.icon ? `<i class="fas ${type.icon} text-primary"></i>` : ''}
                            ${type.name}
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 whitespace-nowrap">
                            ${type.vendor_count} vendors
                        </span>
                    </div>
                </div>
                <div class="mobile-card-content">
                    <div class="mobile-grid mb-3">
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Description</span>
                            <span class="mobile-value">${type.description || '-'}</span>
                        </div>
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Status</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                                ${statusText}
                            </span>
                        </div>
                    </div>
                    <div class="mobile-actions">
                        <button class="btn-edit px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100" data-id="${type.id}">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <button class="btn-delete px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100" data-id="${type.id}">
                            <i class="fas fa-trash-alt mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            `;

            mobileContainer.insertBefore(card, mobileNoData);

            card.querySelector('.btn-edit').addEventListener('click', function () {
                const categoryId = this.getAttribute('data-id');
                showCategoryModal(categoryId);
            });

            card.querySelector('.btn-delete').addEventListener('click', function () {
                const categoryId = this.getAttribute('data-id');
                showDeleteModal(categoryId);
            });
        });
    }

    function renderPaginationNumbers() {
        const paginationContainer = document.getElementById('pagination-numbers');
        paginationContainer.innerHTML = '';

        if (totalPages <= 1) return;

        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);

        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        if (startPage > 1) {
            addPageButton(1);
            if (startPage > 2) {
                addEllipsis();
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            addPageButton(i);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                addEllipsis();
            }
            addPageButton(totalPages);
        }

        function addPageButton(pageNum) {
            const button = document.createElement('button');
            button.className = pageNum === currentPage
                ? 'px-3 py-2 rounded-lg bg-primary text-white'
                : 'px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50';
            button.textContent = pageNum;
            button.addEventListener('click', function () {
                currentPage = pageNum;
                renderBusinessTypes();
            });
            paginationContainer.appendChild(button);
        }

        function addEllipsis() {
            const span = document.createElement('span');
            span.className = 'px-3 py-2 text-gray-400';
            span.textContent = '...';
            paginationContainer.appendChild(span);
        }
    }

    function showCategoryModal(categoryId = null) {
        const modal = document.getElementById('categoryModal');
        const modalTitle = document.getElementById('modal-title');
        const form = document.getElementById('categoryForm');

        form.reset();
        document.getElementById('category-id').value = '';
        document.getElementById('selected-icon').innerHTML = '<i class="fas fa-tag text-gray-400"></i>';
        document.getElementById('category-icon-value').value = '';
        selectedIcon = '';

        if (categoryId) {
            fetch(`${API_URL}?action=getBusinessType&id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const category = data.data;
                        modalTitle.textContent = 'Edit Category';

                        document.getElementById('category-id').value = category.id;
                        document.getElementById('category-name').value = category.name;
                        document.getElementById('category-description').value = category.description || '';
                        document.getElementById('category-status').value = category.status;

                        if (category.icon) {
                            document.getElementById('selected-icon').innerHTML = `<i class="fas ${category.icon} text-primary"></i>`;
                            document.getElementById('category-icon-value').value = category.icon;
                            selectedIcon = category.icon;
                        }
                    } else {
                        showMessage('Error', data.message || 'Failed to fetch category details', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error fetching category details:', error);
                    showMessage('Error', 'Failed to fetch category details. Please try again later.', 'error');
                });
        } else {
            modalTitle.textContent = 'Add New Category';
        }

        modal.classList.remove('hidden');
    }

    function hideCategoryModal() {
        const modal = document.getElementById('categoryModal');
        modal.classList.add('hidden');
    }

    function showDeleteModal(categoryId) {
        const modal = document.getElementById('deleteCategoryModal');
        const category = businessTypes.find(c => c.id === categoryId);

        if (category) {
            document.getElementById('delete-category-name').textContent = category.name;
            document.getElementById('delete-category-vendors').textContent = `${category.vendor_count} vendor(s)`;
            document.getElementById('confirmDelete').setAttribute('data-id', categoryId);

            document.getElementById('delete-confirm').checked = false;
            document.getElementById('confirmDelete').disabled = true;

            modal.classList.remove('hidden');
        }
    }

    function hideDeleteModal() {
        const modal = document.getElementById('deleteCategoryModal');
        modal.classList.add('hidden');
    }

    function showIconModal() {
        const modal = document.getElementById('iconModal');

        document.querySelectorAll('.icon-item').forEach(item => {
            item.classList.remove('border-primary', 'bg-primary/10');
            if (item.getAttribute('data-icon') === selectedIcon) {
                item.classList.add('border-primary', 'bg-primary/10');
            }
        });

        modal.classList.remove('hidden');
    }

    function hideIconModal() {
        const modal = document.getElementById('iconModal');
        modal.classList.add('hidden');
    }

    function saveCategory() {
        const categoryId = document.getElementById('category-id').value;
        const categoryName = document.getElementById('category-name').value.trim();
        const categoryDescription = document.getElementById('category-description').value.trim();
        const categoryStatus = document.getElementById('category-status').value;
        const categoryIcon = document.getElementById('category-icon-value').value;

        if (!categoryName) {
            showMessage('Error', 'Category name is required!', 'error');
            return;
        }

        const data = {
            name: categoryName,
            description: categoryDescription,
            status: categoryStatus,
            icon: categoryIcon
        };

        if (categoryId) {
            data.id = categoryId;

            fetch(`${API_URL}?action=updateBusinessType`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showMessage('Success', 'Category updated successfully!', 'success');
                        hideCategoryModal();
                        fetchBusinessTypes();
                    } else {
                        showMessage('Error', result.message || 'Failed to update category', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error updating category:', error);
                    showMessage('Error', 'Failed to update category. Please try again later.', 'error');
                });
        } else {
            fetch(`${API_URL}?action=createBusinessType`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showMessage('Success', 'Category created successfully!', 'success');
                        hideCategoryModal();
                        fetchBusinessTypes();
                    } else {
                        showMessage('Error', result.message || 'Failed to create category', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error creating category:', error);
                    showMessage('Error', 'Failed to create category. Please try again later.', 'error');
                });
        }
    }

    function deleteCategory(categoryId) {
        fetch(`${API_URL}?action=deleteBusinessType`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: categoryId })
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showMessage('Success', 'Category deleted successfully!', 'success');
                    hideDeleteModal();
                    fetchBusinessTypes();
                } else {
                    showMessage('Error', result.message || 'Failed to delete category', 'error');
                }
            })
            .catch(error => {
                console.error('Error deleting category:', error);
                showMessage('Error', 'Failed to delete category. Please try again later.', 'error');
            });
    }

    function showMessage(title, message, type = 'success') {
        const modal = document.getElementById('messageModal');
        const messageTitle = document.getElementById('message-title');
        const messageText = document.getElementById('message-text');
        const messageIcon = document.getElementById('message-icon');

        messageTitle.textContent = title;
        messageText.textContent = message;

        if (type === 'success') {
            messageIcon.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-green-100';
            messageIcon.innerHTML = '<i class="fas fa-check text-green-500"></i>';
        } else if (type === 'error') {
            messageIcon.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-red-100';
            messageIcon.innerHTML = '<i class="fas fa-times text-red-500"></i>';
        } else if (type === 'warning') {
            messageIcon.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-yellow-100';
            messageIcon.innerHTML = '<i class="fas fa-exclamation text-yellow-500"></i>';
        } else if (type === 'info') {
            messageIcon.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-blue-100';
            messageIcon.innerHTML = '<i class="fas fa-info text-blue-500"></i>';
        }

        modal.classList.remove('hidden');
    }

    function hideMessageModal() {
        const modal = document.getElementById('messageModal');
        modal.classList.add('hidden');
    }
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>