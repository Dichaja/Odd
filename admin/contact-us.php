<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Manage Contact Page';
$activeNav = 'contact-us';

function loadContactData()
{
    $filePath = __DIR__ . '/../page-data/contact-us/contact-us.json';

    if (file_exists($jsonFile = $filePath)) {
        $jsonData = file_get_contents($jsonFile);
        return json_decode($jsonData, true) ?: [];
    }

    return [
        'contactInfo' => [
            'phones' => [
                [
                    'id' => '1',
                    'number' => '+256 392 003-406',
                    'active' => true,
                    'order' => 1
                ]
            ],
            'emails' => [
                [
                    'id' => '1',
                    'address' => 'halo@zzimbaonline.com',
                    'active' => true,
                    'order' => 1
                ]
            ],
            'location' => [
                [
                    'id' => '1',
                    'line' => 'Plaza Building Luzira',
                    'active' => true,
                    'order' => 1
                ],
                [
                    'id' => '2',
                    'line' => 'The Engineering Marksmen Ltd.',
                    'active' => true,
                    'order' => 2
                ],
                [
                    'id' => '3',
                    'line' => 'P.O Box 129572 Kampala - Uganda',
                    'active' => true,
                    'order' => 3
                ]
            ],
            'social' => [
                [
                    'id' => '1',
                    'platform' => 'facebook',
                    'url' => '#',
                    'active' => true,
                    'order' => 1
                ],
                [
                    'id' => '2',
                    'platform' => 'instagram',
                    'url' => '#',
                    'active' => true,
                    'order' => 2
                ],
                [
                    'id' => '3',
                    'platform' => 'linkedin',
                    'url' => '#',
                    'active' => true,
                    'order' => 3
                ],
                [
                    'id' => '4',
                    'platform' => 'twitter',
                    'url' => '#',
                    'active' => true,
                    'order' => 4
                ],
                [
                    'id' => '5',
                    'platform' => 'whatsapp',
                    'url' => '#',
                    'active' => false,
                    'order' => 5
                ]
            ]
        ],
        'formSettings' => [
            'title' => 'Get in Touch',
            'description' => 'Buy online, deliver on-site, buy now, construction procurement made easy, order now, request for quote, quality products, fast delivery, guaranteed service.',
            'fields' => [
                [
                    'id' => 'name',
                    'label' => 'Name',
                    'placeholder' => 'Name',
                    'required' => true,
                    'order' => 1
                ],
                [
                    'id' => 'phone',
                    'label' => 'Phone',
                    'placeholder' => 'Phone',
                    'required' => false,
                    'order' => 2
                ],
                [
                    'id' => 'email',
                    'label' => 'Email',
                    'placeholder' => 'Email',
                    'required' => true,
                    'order' => 3
                ],
                [
                    'id' => 'subject',
                    'label' => 'Subject',
                    'placeholder' => 'Subject',
                    'required' => true,
                    'order' => 4
                ],
                [
                    'id' => 'message',
                    'label' => 'Message',
                    'placeholder' => 'Message',
                    'required' => true,
                    'order' => 5
                ]
            ],
            'buttonText' => 'Send Message',
            'mapCoordinates' => [
                'latitude' => '0.31654191425996444',
                'longitude' => '32.629696775378866',
                'zoom' => 15
            ]
        ]
    ];
}

$contactData = loadContactData();
$contactInfo = $contactData['contactInfo'] ?? [];
$formSettings = $contactData['formSettings'] ?? [];

ob_start();
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Contact Page Management</h1>
                    <p class="text-gray-600 mt-1">Manage your website contact page content and layout</p>
                </div>
                <div class="flex flex-col md:flex-row items-center gap-3">
                    <a href="dashboard"
                        class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex gap-8">
            <div class="hidden lg:block w-64 flex-shrink-0">
                <div id="desktop-nav">
                    <nav class="space-y-2" aria-label="Contact Navigation">
                        <button id="contact-info-tab"
                            class="tab-button active w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 bg-primary/10 text-primary border border-primary/20"
                            onclick="switchTab('contact-info')">
                            <i class="fas fa-address-book"></i>
                            <span>Contact Information</span>
                        </button>
                        <button id="form-settings-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchTab('form-settings')">
                            <i class="fas fa-cogs"></i>
                            <span>Form & Map Settings</span>
                        </button>
                    </nav>
                </div>
            </div>

            <div class="flex-1">
                <div class="lg:hidden mb-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
                        <div class="relative">
                            <button id="mobile-tab-toggle"
                                class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-address-book text-primary"></i>
                                    <span id="mobile-tab-label" class="font-medium text-gray-900">Contact
                                        Information</span>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"
                                    id="mobile-tab-chevron"></i>
                            </button>

                            <div id="mobile-tab-dropdown"
                                class="hidden absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-lg z-50">
                                <div class="py-2">
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="contact-info">
                                        <i class="fas fa-address-book text-blue-600"></i>
                                        <span>Contact Information</span>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="form-settings">
                                        <i class="fas fa-cogs text-green-600"></i>
                                        <span>Form & Map Settings</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-content" class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200" id="content-container">

                        <div id="contact-info-content" class="tab-content">
                            <div class="p-6 border-b border-gray-100">
                                <h2 class="text-lg font-semibold text-secondary">Contact Information</h2>
                                <p class="text-sm text-gray-text mt-1">Manage contact information displayed on the
                                    contact page</p>
                            </div>

                            <div class="p-6">
                                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-info-circle text-blue-400"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-blue-700">
                                                This information will be displayed in the contact information ribbon at
                                                the top of the
                                                contact page. You can add multiple phone numbers, email addresses, and
                                                location lines.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-8">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                                        <h3 class="text-md font-semibold text-gray-700">Phone Numbers</h3>
                                        <button id="addPhoneBtn"
                                            class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                                            <i class="fas fa-plus"></i>
                                            <span>Add Phone Number</span>
                                        </button>
                                    </div>

                                    <div id="phones-container" class="space-y-4">
                                        <?php foreach ($contactInfo['phones'] ?? [] as $phone): ?>
                                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4"
                                                data-id="<?= $phone['id'] ?>" data-order="<?= $phone['order'] ?>">
                                                <div
                                                    class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="text-primary">
                                                            <i class="fas fa-phone-alt text-xl"></i>
                                                        </div>
                                                        <div>
                                                            <p class="font-medium"><?= htmlspecialchars($phone['number']) ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex items-center">
                                                            <div
                                                                class="relative inline-block w-10 h-5 transition duration-200 ease-in-out rounded-full cursor-pointer">
                                                                <input type="checkbox"
                                                                    class="phone-status-toggle absolute w-5 h-5 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                                                    data-id="<?= $phone['id'] ?>" <?= $phone['active'] ? 'checked' : '' ?>>
                                                                <label
                                                                    class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                                                            </div>
                                                            <span
                                                                class="ml-2 text-xs font-medium <?= $phone['active'] ? 'text-green-600' : 'text-gray-500' ?>">
                                                                <?= $phone['active'] ? 'Active' : 'Inactive' ?>
                                                            </span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <button class="btn-edit-phone text-blue-600 hover:text-blue-800"
                                                                data-id="<?= $phone['id'] ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn-delete-phone text-red-600 hover:text-red-800"
                                                                data-id="<?= $phone['id'] ?>">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                            <span
                                                                class="cursor-move bg-gray-100 text-gray-700 rounded-full h-6 w-6 flex items-center justify-center">
                                                                <?= $phone['order'] ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="mb-8">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                                        <h3 class="text-md font-semibold text-gray-700">Email Addresses</h3>
                                        <button id="addEmailBtn"
                                            class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                                            <i class="fas fa-plus"></i>
                                            <span>Add Email Address</span>
                                        </button>
                                    </div>

                                    <div id="emails-container" class="space-y-4">
                                        <?php foreach ($contactInfo['emails'] ?? [] as $email): ?>
                                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4"
                                                data-id="<?= $email['id'] ?>" data-order="<?= $email['order'] ?>">
                                                <div
                                                    class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="text-amber-500">
                                                            <i class="fas fa-envelope text-xl"></i>
                                                        </div>
                                                        <div>
                                                            <p class="font-medium">
                                                                <?= htmlspecialchars($email['address']) ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex items-center">
                                                            <div
                                                                class="relative inline-block w-10 h-5 transition duration-200 ease-in-out rounded-full cursor-pointer">
                                                                <input type="checkbox"
                                                                    class="email-status-toggle absolute w-5 h-5 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                                                    data-id="<?= $email['id'] ?>" <?= $email['active'] ? 'checked' : '' ?>>
                                                                <label
                                                                    class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                                                            </div>
                                                            <span
                                                                class="ml-2 text-xs font-medium <?= $email['active'] ? 'text-green-600' : 'text-gray-500' ?>">
                                                                <?= $email['active'] ? 'Active' : 'Inactive' ?>
                                                            </span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <button class="btn-edit-email text-blue-600 hover:text-blue-800"
                                                                data-id="<?= $email['id'] ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn-delete-email text-red-600 hover:text-red-800"
                                                                data-id="<?= $email['id'] ?>">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                            <span
                                                                class="cursor-move bg-gray-100 text-gray-700 rounded-full h-6 w-6 flex items-center justify-center">
                                                                <?= $email['order'] ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="mb-8">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                                        <h3 class="text-md font-semibold text-gray-700">Location Details</h3>
                                        <button id="addLocationBtn"
                                            class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                                            <i class="fas fa-plus"></i>
                                            <span>Add Location Line</span>
                                        </button>
                                    </div>

                                    <div id="location-container" class="space-y-4">
                                        <?php foreach ($contactInfo['location'] ?? [] as $location): ?>
                                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4"
                                                data-id="<?= $location['id'] ?>" data-order="<?= $location['order'] ?>">
                                                <div
                                                    class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="text-primary">
                                                            <i class="fas fa-map-marker-alt text-xl"></i>
                                                        </div>
                                                        <div>
                                                            <p class="font-medium">
                                                                <?= htmlspecialchars($location['line']) ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex items-center">
                                                            <div
                                                                class="relative inline-block w-10 h-5 transition duration-200 ease-in-out rounded-full cursor-pointer">
                                                                <input type="checkbox"
                                                                    class="location-status-toggle absolute w-5 h-5 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                                                    data-id="<?= $location['id'] ?>" <?= $location['active'] ? 'checked' : '' ?>>
                                                                <label
                                                                    class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                                                            </div>
                                                            <span
                                                                class="ml-2 text-xs font-medium <?= $location['active'] ? 'text-green-600' : 'text-gray-500' ?>">
                                                                <?= $location['active'] ? 'Active' : 'Inactive' ?>
                                                            </span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <button
                                                                class="btn-edit-location text-blue-600 hover:text-blue-800"
                                                                data-id="<?= $location['id'] ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button
                                                                class="btn-delete-location text-red-600 hover:text-red-800"
                                                                data-id="<?= $location['id'] ?>">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                            <span
                                                                class="cursor-move bg-gray-100 text-gray-700 rounded-full h-6 w-6 flex items-center justify-center">
                                                                <?= $location['order'] ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="mb-8">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                                        <h3 class="text-md font-semibold text-gray-700">Social Media Links</h3>
                                    </div>

                                    <div id="social-container" class="space-y-4">
                                        <?php
                                        $socialPlatforms = [
                                            'facebook' => ['icon' => 'fab fa-facebook', 'name' => 'Facebook'],
                                            'instagram' => ['icon' => 'fab fa-instagram', 'name' => 'Instagram'],
                                            'linkedin' => ['icon' => 'fab fa-linkedin', 'name' => 'LinkedIn'],
                                            'twitter' => ['icon' => 'fab fa-twitter', 'name' => 'Twitter/X'],
                                            'whatsapp' => ['icon' => 'fab fa-whatsapp', 'name' => 'WhatsApp']
                                        ];

                                        $socialData = [];
                                        foreach ($contactInfo['social'] ?? [] as $social) {
                                            $socialData[$social['platform']] = $social;
                                        }

                                        foreach ($socialPlatforms as $platform => $info):
                                            $social = $socialData[$platform] ?? [
                                                'id' => $platform,
                                                'platform' => $platform,
                                                'url' => '#',
                                                'active' => false,
                                                'order' => array_search($platform, array_keys($socialPlatforms)) + 1
                                            ];
                                            ?>
                                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4"
                                                data-id="<?= htmlspecialchars($social['id']) ?>"
                                                data-order="<?= $social['order'] ?>">
                                                <div
                                                    class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="text-gray-700">
                                                            <i class="<?= $info['icon'] ?> text-xl"></i>
                                                        </div>
                                                        <div>
                                                            <p class="font-medium"><?= $info['name'] ?></p>
                                                            <p class="text-sm text-gray-500">
                                                                <?= htmlspecialchars($social['url']) ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex items-center">
                                                            <div
                                                                class="relative inline-block w-10 h-5 transition duration-200 ease-in-out rounded-full cursor-pointer">
                                                                <input type="checkbox"
                                                                    class="social-status-toggle absolute w-5 h-5 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                                                    data-id="<?= htmlspecialchars($social['id']) ?>"
                                                                    <?= $social['active'] ? 'checked' : '' ?>>
                                                                <label
                                                                    class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                                                            </div>
                                                            <span
                                                                class="ml-2 text-xs font-medium <?= $social['active'] ? 'text-green-600' : 'text-gray-500' ?>">
                                                                <?= $social['active'] ? 'Active' : 'Inactive' ?>
                                                            </span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <button
                                                                class="btn-edit-social text-blue-600 hover:text-blue-800"
                                                                data-id="<?= htmlspecialchars($social['id']) ?>"
                                                                data-platform="<?= $platform ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <span
                                                                class="cursor-move bg-gray-100 text-gray-700 rounded-full h-6 w-6 flex items-center justify-center">
                                                                <?= $social['order'] ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="form-settings-content" class="tab-content hidden">
                            <div class="p-6 border-b border-gray-100">
                                <h2 class="text-lg font-semibold text-secondary">Form & Map Settings</h2>
                                <p class="text-sm text-gray-text mt-1">Configure the contact form and map settings</p>
                            </div>

                            <div class="p-6">
                                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-info-circle text-blue-400"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-blue-700">
                                                Customize the contact form title, description, field labels,
                                                placeholders, and button
                                                text. You can also set the map coordinates to display your location.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-8">
                                    <h3 class="text-md font-semibold text-gray-700 mb-4">Form Title & Description</h3>
                                    <form id="formTitleForm"
                                        class="space-y-6 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                        <div class="grid grid-cols-1 gap-6">
                                            <div>
                                                <label for="form-title"
                                                    class="block text-sm font-medium text-gray-700 mb-1">Form Title
                                                    <span class="text-red-500">*</span></label>
                                                <input type="text" id="form-title" name="title"
                                                    value="<?= htmlspecialchars($formSettings['title'] ?? 'Get in Touch') ?>"
                                                    class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                                    required>
                                            </div>
                                            <div>
                                                <label for="form-description"
                                                    class="block text-sm font-medium text-gray-700 mb-1">Form
                                                    Description</label>
                                                <textarea id="form-description" name="description" rows="3"
                                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"><?= htmlspecialchars($formSettings['description'] ?? '') ?></textarea>
                                            </div>
                                            <div>
                                                <label for="button-text"
                                                    class="block text-sm font-medium text-gray-700 mb-1">Button
                                                    Text
                                                    <span class="text-red-500">*</span></label>
                                                <input type="text" id="button-text" name="buttonText"
                                                    value="<?= htmlspecialchars($formSettings['buttonText'] ?? 'Send Message') ?>"
                                                    class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="pt-4 flex justify-end">
                                            <button type="submit" id="saveFormTitleBtn"
                                                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                                Save Changes
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <div class="mb-8">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                                        <h3 class="text-md font-semibold text-gray-700">Form Fields</h3>
                                        <button id="addFieldBtn"
                                            class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                                            <i class="fas fa-plus"></i>
                                            <span>Add Form Field</span>
                                        </button>
                                    </div>

                                    <div id="fields-container" class="space-y-4">
                                        <?php foreach ($formSettings['fields'] ?? [] as $field): ?>
                                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4"
                                                data-id="<?= htmlspecialchars($field['id']) ?>"
                                                data-order="<?= $field['order'] ?>">
                                                <div
                                                    class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="text-primary">
                                                            <i class="fas fa-keyboard text-xl"></i>
                                                        </div>
                                                        <div>
                                                            <p class="font-medium"><?= htmlspecialchars($field['label']) ?>
                                                            </p>
                                                            <p class="text-sm text-gray-500">ID:
                                                                <?= htmlspecialchars($field['id']) ?>,
                                                                Placeholder:
                                                                <?= htmlspecialchars($field['placeholder']) ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex items-center">
                                                            <span
                                                                class="text-xs font-medium <?= $field['required'] ? 'text-red-600' : 'text-gray-500' ?>">
                                                                <?= $field['required'] ? 'Required' : 'Optional' ?>
                                                            </span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <button class="btn-edit-field text-blue-600 hover:text-blue-800"
                                                                data-id="<?= htmlspecialchars($field['id']) ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn-delete-field text-red-600 hover:text-red-800"
                                                                data-id="<?= htmlspecialchars($field['id']) ?>">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                            <span
                                                                class="cursor-move bg-gray-100 text-gray-700 rounded-full h-6 w-6 flex items-center justify-center">
                                                                <?= $field['order'] ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="mb-8">
                                    <h3 class="text-md font-semibold text-gray-700 mb-4">Map Settings</h3>
                                    <form id="mapSettingsForm"
                                        class="space-y-6 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                            <div>
                                                <label for="map-latitude"
                                                    class="block text-sm font-medium text-gray-700 mb-1">Latitude
                                                    <span class="text-red-500">*</span></label>
                                                <input type="text" id="map-latitude" name="latitude"
                                                    value="<?= htmlspecialchars($formSettings['mapCoordinates']['latitude'] ?? '0.31654191425996444') ?>"
                                                    class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                                    required>
                                            </div>
                                            <div>
                                                <label for="map-longitude"
                                                    class="block text-sm font-medium text-gray-700 mb-1">Longitude
                                                    <span class="text-red-500">*</span></label>
                                                <input type="text" id="map-longitude" name="longitude"
                                                    value="<?= htmlspecialchars($formSettings['mapCoordinates']['longitude'] ?? '32.629696775378866') ?>"
                                                    class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                                    required>
                                            </div>
                                            <div>
                                                <label for="map-zoom"
                                                    class="block text-sm font-medium text-gray-700 mb-1">Zoom Level
                                                    <span class="text-red-500">*</span></label>
                                                <input type="number" id="map-zoom" name="zoom"
                                                    value="<?= htmlspecialchars($formSettings['mapCoordinates']['zoom'] ?? '15') ?>"
                                                    min="1" max="20" step="1"
                                                    class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="pt-4 flex justify-end">
                                            <button type="submit" id="saveMapSettingsBtn"
                                                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                                Save Map Settings
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <div class="mb-8">
                                    <h3 class="text-md font-semibold text-gray-700 mb-4">Map Preview</h3>
                                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                                        <div
                                            class="aspect-[16/9] md:aspect-[3/1] bg-gray-100 rounded-lg overflow-hidden">
                                            <iframe id="map-preview"
                                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.7570741913936!2d32.629696775378866!3d0.31654191425996444!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMMKwMTgnNTkuNiJOIDMywrAzNyc1NC44IkU!5e0!3m2!1sen!2sus!4v1621234567890!5m2!1sen!2sus"
                                                width="100%" height="100%" style="border:0;" allowfullscreen=""
                                                loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                                                title="Zzimba Online Head Office Location">
                                            </iframe>
                                        </div>
                                        <div class="mt-4 text-center">
                                            <button id="updateMapPreviewBtn"
                                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                                <i class="fas fa-sync-alt mr-2"></i>
                                                Update Map Preview
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="phoneModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hidePhoneModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="phoneModalTitle">Add Phone Number</h3>
            <button onclick="hidePhoneModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="phoneForm" class="space-y-6">
                <input type="hidden" id="phoneId" name="phoneId" value="">

                <div>
                    <label for="phoneNumber" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="phoneNumber" name="number" placeholder="+256 392 003-406"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        required>
                </div>

                <div>
                    <label for="phone-status-toggle" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center space-x-3">
                        <div
                            class="relative inline-block w-12 h-6 transition duration-200 ease-in-out rounded-full cursor-pointer">
                            <input type="checkbox" id="phone-status-toggle"
                                class="absolute w-6 h-6 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                checked>
                            <label for="phone-status-toggle"
                                class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                        </div>
                        <span id="phone-status-text" class="text-sm font-medium text-gray-700">Active</span>
                        <input type="hidden" id="phone-status" name="status" value="active">
                    </div>
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hidePhoneModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="submitPhone"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                Save Phone
            </button>
        </div>
    </div>
</div>

<div id="emailModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideEmailModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="emailModalTitle">Add Email Address</h3>
            <button onclick="hideEmailModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="emailForm" class="space-y-6">
                <input type="hidden" id="emailId" name="emailId" value="">

                <div>
                    <label for="emailAddress" class="block text-sm font-medium text-gray-700 mb-1">Email Address <span
                            class="text-red-500">*</span></label>
                    <input type="email" id="emailAddress" name="address" placeholder="halo@zzimbaonline.com"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        required>
                </div>

                <div>
                    <label for="email-status-toggle" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center space-x-3">
                        <div
                            class="relative inline-block w-12 h-6 transition duration-200 ease-in-out rounded-full cursor-pointer">
                            <input type="checkbox" id="email-status-toggle"
                                class="absolute w-6 h-6 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                checked>
                            <label for="email-status-toggle"
                                class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                        </div>
                        <span id="email-status-text" class="text-sm font-medium text-gray-700">Active</span>
                        <input type="hidden" id="email-status" name="status" value="active">
                    </div>
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideEmailModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="submitEmail"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                Save Email
            </button>
        </div>
    </div>
</div>

<div id="locationModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideLocationModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="locationModalTitle">Add Location Line</h3>
            <button onclick="hideLocationModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="locationForm" class="space-y-6">
                <input type="hidden" id="locationId" name="locationId" value="">

                <div>
                    <label for="locationLine" class="block text-sm font-medium text-gray-700 mb-1">Location Line <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="locationLine" name="line" placeholder="Plaza Building Luzira"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        required>
                </div>

                <div>
                    <label for="location-status-toggle"
                        class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center space-x-3">
                        <div
                            class="relative inline-block w-12 h-6 transition duration-200 ease-in-out rounded-full cursor-pointer">
                            <input type="checkbox" id="location-status-toggle"
                                class="absolute w-6 h-6 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                checked>
                            <label for="location-status-toggle"
                                class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                        </div>
                        <span id="location-status-text" class="text-sm font-medium text-gray-700">Active</span>
                        <input type="hidden" id="location-status" name="status" value="active">
                    </div>
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideLocationModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="submitLocation"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                Save Location
            </button>
        </div>
    </div>
</div>

<div id="socialModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideSocialModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="socialModalTitle">Edit Social Link</h3>
            <button onclick="hideSocialModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="socialForm" class="space-y-6">
                <input type="hidden" id="socialId" name="socialId" value="">
                <input type="hidden" id="socialPlatform" name="platform" value="">

                <div>
                    <label for="socialUrl" class="block text-sm font-medium text-gray-700 mb-1">URL <span
                            class="text-red-500">*</span></label>
                    <input type="url" id="socialUrl" name="url" placeholder="https://facebook.com/yourpage"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        required>
                </div>

                <div>
                    <label for="social-status-toggle"
                        class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center space-x-3">
                        <div
                            class="relative inline-block w-12 h-6 transition duration-200 ease-in-out rounded-full cursor-pointer">
                            <input type="checkbox" id="social-status-toggle"
                                class="absolute w-6 h-6 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                checked>
                            <label for="social-status-toggle"
                                class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                        </div>
                        <span id="social-status-text" class="text-sm font-medium text-gray-700">Active</span>
                        <input type="hidden" id="social-status" name="status" value="active">
                    </div>
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideSocialModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="submitSocial"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                Save Social Link
            </button>
        </div>
    </div>
</div>

<div id="fieldModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideFieldModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="fieldModalTitle">Add Form Field</h3>
            <button onclick="hideFieldModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="fieldForm" class="space-y-6">
                <input type="hidden" id="fieldId" name="fieldId" value="">

                <div>
                    <label for="fieldIdInput" class="block text-sm font-medium text-gray-700 mb-1">Field ID <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="fieldIdInput" name="id" placeholder="name"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        required>
                    <p class="mt-1 text-xs text-gray-500">Use lowercase letters, numbers, and underscores only. No
                        spaces.</p>
                </div>

                <div>
                    <label for="fieldLabel" class="block text-sm font-medium text-gray-700 mb-1">Field Label <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="fieldLabel" name="label" placeholder="Name"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        required>
                </div>

                <div>
                    <label for="fieldPlaceholder"
                        class="block text-sm font-medium text-gray-700 mb-1">Placeholder</label>
                    <input type="text" id="fieldPlaceholder" name="placeholder" placeholder="Enter your name"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="fieldRequired" name="required"
                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" checked>
                    <label for="fieldRequired" class="ml-2 block text-sm text-gray-900">Required Field</label>
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideFieldModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="submitField"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                Save Field
            </button>
        </div>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideDeleteModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="deleteModalTitle">Delete Item</h3>
            <button onclick="hideDeleteModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4" id="deleteModalMessage">Are you sure you want to delete this item? This action
                cannot be undone.</p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-500" id="deleteItemTypeLabel">Item:</div>
                    <div class="font-medium text-gray-900" id="deleteItemName"></div>
                    <div class="text-gray-500">Status:</div>
                    <div class="font-medium text-gray-900" id="deleteItemStatus"></div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideDeleteModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Delete
            </button>
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

<div id="loadingOverlay" class="fixed inset-0 bg-black/30 flex items-center justify-center z-[1000] hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full">
        <div class="flex flex-col items-center">
            <div class="w-12 h-12 border-4 border-primary/30 border-t-primary rounded-full animate-spin mb-4"></div>
            <p id="loadingMessage" class="text-gray-700 font-medium text-center">Loading...</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    const BASE_URL = '<?= BASE_URL ?>';
    let deleteItemType = '';
    let deleteItemId = '';
    let currentTab = 'contact-info';

    document.addEventListener('DOMContentLoaded', function () {
        initializeEventListeners();
        initializeSortable();
        initializeStatusToggles();
        updateMapPreview();
        switchTab('contact-info');
    });

    function switchTab(tabName) {
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
            btn.classList.add('text-gray-600', 'hover:bg-gray-50', 'hover:text-gray-900');
        });

        const tabId = `${tabName}-tab`;
        const activeTab = document.getElementById(tabId);
        if (activeTab) {
            activeTab.classList.remove('text-gray-600', 'hover:bg-gray-50', 'hover:text-gray-900');
            activeTab.classList.add('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
        }

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });

        const activeContent = document.getElementById(`${tabName}-content`);
        if (activeContent) {
            activeContent.classList.remove('hidden');
        }

        currentTab = tabName;

        const tabLabels = {
            'contact-info': { label: 'Contact Information', icon: 'fas fa-address-book' },
            'form-settings': { label: 'Form & Map Settings', icon: 'fas fa-cogs' }
        };
        const tabInfo = tabLabels[tabName] || tabLabels['contact-info'];
        updateMobileTabLabel(tabInfo.label, tabInfo.icon);
    }

    function updateMobileTabLabel(label, icon) {
        const labelElement = document.getElementById('mobile-tab-label');
        const toggleButton = document.getElementById('mobile-tab-toggle');
        if (labelElement && toggleButton) {
            labelElement.textContent = label;
            const iconElement = toggleButton.querySelector('i');
            if (iconElement) {
                iconElement.className = `${icon} text-primary`;
            }
        }
    }

    function toggleMobileTabDropdown() {
        const dropdown = document.getElementById('mobile-tab-dropdown');
        const chevron = document.getElementById('mobile-tab-chevron');
        dropdown.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
    }

    function showLoading(message = 'Loading...') {
        document.getElementById('loadingMessage').textContent = message;
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    function initializeEventListeners() {
        document.getElementById('addPhoneBtn')?.addEventListener('click', () => showPhoneModal());
        document.querySelectorAll('.btn-edit-phone').forEach(btn => {
            btn.addEventListener('click', function () {
                const phoneId = this.getAttribute('data-id');
                showPhoneModal(phoneId);
            });
        });
        document.querySelectorAll('.btn-delete-phone').forEach(btn => {
            btn.addEventListener('click', function () {
                const phoneId = this.getAttribute('data-id');
                showDeleteModal('phone', phoneId);
            });
        });
        document.getElementById('submitPhone')?.addEventListener('click', submitPhoneForm);

        document.getElementById('addEmailBtn')?.addEventListener('click', () => showEmailModal());
        document.querySelectorAll('.btn-edit-email').forEach(btn => {
            btn.addEventListener('click', function () {
                const emailId = this.getAttribute('data-id');
                showEmailModal(emailId);
            });
        });
        document.querySelectorAll('.btn-delete-email').forEach(btn => {
            btn.addEventListener('click', function () {
                const emailId = this.getAttribute('data-id');
                showDeleteModal('email', emailId);
            });
        });
        document.getElementById('submitEmail')?.addEventListener('click', submitEmailForm);

        document.getElementById('addLocationBtn')?.addEventListener('click', () => showLocationModal());
        document.querySelectorAll('.btn-edit-location').forEach(btn => {
            btn.addEventListener('click', function () {
                const locationId = this.getAttribute('data-id');
                showLocationModal(locationId);
            });
        });
        document.querySelectorAll('.btn-delete-location').forEach(btn => {
            btn.addEventListener('click', function () {
                const locationId = this.getAttribute('data-id');
                showDeleteModal('location', locationId);
            });
        });
        document.getElementById('submitLocation')?.addEventListener('click', submitLocationForm);

        document.querySelectorAll('.btn-edit-social').forEach(btn => {
            btn.addEventListener('click', function () {
                const socialId = this.getAttribute('data-id');
                const platform = this.getAttribute('data-platform');
                showSocialModal(socialId, platform);
            });
        });
        document.getElementById('submitSocial')?.addEventListener('click', submitSocialForm);

        document.getElementById('addFieldBtn')?.addEventListener('click', () => showFieldModal());
        document.querySelectorAll('.btn-edit-field').forEach(btn => {
            btn.addEventListener('click', function () {
                const fieldId = this.getAttribute('data-id');
                showFieldModal(fieldId);
            });
        });
        document.querySelectorAll('.btn-delete-field').forEach(btn => {
            btn.addEventListener('click', function () {
                const fieldId = this.getAttribute('data-id');
                showDeleteModal('field', fieldId);
            });
        });
        document.getElementById('submitField')?.addEventListener('click', submitFieldForm);

        document.getElementById('formTitleForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            submitFormTitleForm();
        });

        document.getElementById('mapSettingsForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            submitMapSettingsForm();
        });

        document.getElementById('updateMapPreviewBtn')?.addEventListener('click', updateMapPreview);
        document.getElementById('confirmDelete')?.addEventListener('click', confirmDelete);

        document.getElementById('mobile-tab-toggle').addEventListener('click', toggleMobileTabDropdown);

        document.querySelectorAll('.mobile-tab-option').forEach(option => {
            option.addEventListener('click', (e) => {
                const tab = e.currentTarget.getAttribute('data-tab');
                switchTab(tab);
                toggleMobileTabDropdown();
            });
        });
    }

    function initializeSortable() {
        const phonesContainer = document.getElementById('phones-container');
        if (phonesContainer) {
            new Sortable(phonesContainer, {
                animation: 150,
                ghostClass: 'bg-gray-100',
                handle: '.cursor-move',
                onEnd: function () {
                    updatePhonesOrder();
                }
            });
        }

        const emailsContainer = document.getElementById('emails-container');
        if (emailsContainer) {
            new Sortable(emailsContainer, {
                animation: 150,
                ghostClass: 'bg-gray-100',
                handle: '.cursor-move',
                onEnd: function () {
                    updateEmailsOrder();
                }
            });
        }

        const locationContainer = document.getElementById('location-container');
        if (locationContainer) {
            new Sortable(locationContainer, {
                animation: 150,
                ghostClass: 'bg-gray-100',
                handle: '.cursor-move',
                onEnd: function () {
                    updateLocationOrder();
                }
            });
        }

        const socialContainer = document.getElementById('social-container');
        if (socialContainer) {
            new Sortable(socialContainer, {
                animation: 150,
                ghostClass: 'bg-gray-100',
                handle: '.cursor-move',
                onEnd: function () {
                    updateSocialOrder();
                }
            });
        }

        const fieldsContainer = document.getElementById('fields-container');
        if (fieldsContainer) {
            new Sortable(fieldsContainer, {
                animation: 150,
                ghostClass: 'bg-gray-100',
                handle: '.cursor-move',
                onEnd: function () {
                    updateFieldsOrder();
                }
            });
        }
    }

    function initializeStatusToggles() {
        document.querySelectorAll('.phone-status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function () {
                const phoneId = this.getAttribute('data-id');
                const newStatus = this.checked ? 'active' : 'inactive';
                const statusText = this.parentElement.nextElementSibling;

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusText.classList.remove('text-gray-500');
                    statusText.classList.add('text-green-600');
                } else {
                    statusText.textContent = 'Inactive';
                    statusText.classList.remove('text-green-600');
                    statusText.classList.add('text-gray-500');
                }

                updatePhoneStatus(phoneId, newStatus);
            });
        });

        document.querySelectorAll('.email-status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function () {
                const emailId = this.getAttribute('data-id');
                const newStatus = this.checked ? 'active' : 'inactive';
                const statusText = this.parentElement.nextElementSibling;

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusText.classList.remove('text-gray-500');
                    statusText.classList.add('text-green-600');
                } else {
                    statusText.textContent = 'Inactive';
                    statusText.classList.remove('text-green-600');
                    statusText.classList.add('text-gray-500');
                }

                updateEmailStatus(emailId, newStatus);
            });
        });

        document.querySelectorAll('.location-status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function () {
                const locationId = this.getAttribute('data-id');
                const newStatus = this.checked ? 'active' : 'inactive';
                const statusText = this.parentElement.nextElementSibling;

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusText.classList.remove('text-gray-500');
                    statusText.classList.add('text-green-600');
                } else {
                    statusText.textContent = 'Inactive';
                    statusText.classList.remove('text-green-600');
                    statusText.classList.add('text-gray-500');
                }

                updateLocationStatus(locationId, newStatus);
            });
        });

        document.querySelectorAll('.social-status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function () {
                const socialId = this.getAttribute('data-id');
                const newStatus = this.checked ? 'active' : 'inactive';
                const statusText = this.parentElement.nextElementSibling;

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusText.classList.remove('text-gray-500');
                    statusText.classList.add('text-green-600');
                } else {
                    statusText.textContent = 'Inactive';
                    statusText.classList.remove('text-green-600');
                    statusText.classList.add('text-gray-500');
                }

                updateSocialStatus(socialId, newStatus);
            });
        });

        const phoneStatusToggle = document.getElementById('phone-status-toggle');
        if (phoneStatusToggle) {
            phoneStatusToggle.addEventListener('change', function () {
                const statusText = document.getElementById('phone-status-text');
                const statusInput = document.getElementById('phone-status');

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusInput.value = 'active';
                } else {
                    statusText.textContent = 'Inactive';
                    statusInput.value = 'inactive';
                }
            });
        }

        const emailStatusToggle = document.getElementById('email-status-toggle');
        if (emailStatusToggle) {
            emailStatusToggle.addEventListener('change', function () {
                const statusText = document.getElementById('email-status-text');
                const statusInput = document.getElementById('email-status');

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusInput.value = 'active';
                } else {
                    statusText.textContent = 'Inactive';
                    statusInput.value = 'inactive';
                }
            });
        }

        const locationStatusToggle = document.getElementById('location-status-toggle');
        if (locationStatusToggle) {
            locationStatusToggle.addEventListener('change', function () {
                const statusText = document.getElementById('location-status-text');
                const statusInput = document.getElementById('location-status');

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusInput.value = 'active';
                } else {
                    statusText.textContent = 'Inactive';
                    statusInput.value = 'inactive';
                }
            });
        }

        const socialStatusToggle = document.getElementById('social-status-toggle');
        if (socialStatusToggle) {
            socialStatusToggle.addEventListener('change', function () {
                const statusText = document.getElementById('social-status-text');
                const statusInput = document.getElementById('social-status');

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusInput.value = 'active';
                } else {
                    statusText.textContent = 'Inactive';
                    statusInput.value = 'inactive';
                }
            });
        }
    }

    function updatePhonesOrder() {
        const phones = document.querySelectorAll('#phones-container > div');
        const updatedPhones = [];

        phones.forEach((phone, index) => {
            const phoneId = phone.dataset.id;
            const newOrder = index + 1;

            phone.dataset.order = newOrder;
            phone.querySelector('.cursor-move').textContent = newOrder;

            updatedPhones.push({
                id: phoneId,
                order: newOrder
            });
        });

        saveContactData('phonesOrder', updatedPhones);
    }

    function updateEmailsOrder() {
        const emails = document.querySelectorAll('#emails-container > div');
        const updatedEmails = [];

        emails.forEach((email, index) => {
            const emailId = email.dataset.id;
            const newOrder = index + 1;

            email.dataset.order = newOrder;
            email.querySelector('.cursor-move').textContent = newOrder;

            updatedEmails.push({
                id: emailId,
                order: newOrder
            });
        });

        saveContactData('emailsOrder', updatedEmails);
    }

    function updateLocationOrder() {
        const locations = document.querySelectorAll('#location-container > div');
        const updatedLocations = [];

        locations.forEach((location, index) => {
            const locationId = location.dataset.id;
            const newOrder = index + 1;

            location.dataset.order = newOrder;
            location.querySelector('.cursor-move').textContent = newOrder;

            updatedLocations.push({
                id: locationId,
                order: newOrder
            });
        });

        saveContactData('locationOrder', updatedLocations);
    }

    function updateSocialOrder() {
        const socials = document.querySelectorAll('#social-container > div');
        const updatedSocials = [];

        socials.forEach((social, index) => {
            const socialId = social.dataset.id;
            const newOrder = index + 1;

            social.dataset.order = newOrder;
            social.querySelector('.cursor-move').textContent = newOrder;

            updatedSocials.push({
                id: socialId,
                order: newOrder
            });
        });

        saveContactData('socialOrder', updatedSocials);
    }

    function updateFieldsOrder() {
        const fields = document.querySelectorAll('#fields-container > div');
        const updatedFields = [];

        fields.forEach((field, index) => {
            const fieldId = field.dataset.id;
            const newOrder = index + 1;

            field.dataset.order = newOrder;
            field.querySelector('.cursor-move').textContent = newOrder;

            updatedFields.push({
                id: fieldId,
                order: newOrder
            });
        });

        saveContactData('fieldsOrder', updatedFields);
    }

    function updatePhoneStatus(phoneId, status) {
        saveContactData('phoneStatus', {
            id: phoneId,
            active: status === 'active'
        });
    }

    function updateEmailStatus(emailId, status) {
        saveContactData('emailStatus', {
            id: emailId,
            active: status === 'active'
        });
    }

    function updateLocationStatus(locationId, status) {
        saveContactData('locationStatus', {
            id: locationId,
            active: status === 'active'
        });
    }

    function updateSocialStatus(socialId, status) {
        saveContactData('socialStatus', {
            id: socialId,
            active: status === 'active'
        });
    }

    function showPhoneModal(phoneId = null) {
        resetPhoneForm();

        if (phoneId) {
            document.getElementById('phoneModalTitle').textContent = 'Edit Phone Number';
            document.getElementById('submitPhone').textContent = 'Update Phone';
            document.getElementById('phoneId').value = phoneId;

            showLoading('Loading phone data...');

            fetch('fetch/manageContactUs.php?action=get_phone&id=' + phoneId)
                .then(response => response.json())
                .then(data => {
                    hideLoading();

                    if (data.success && data.phone) {
                        const phone = data.phone;

                        document.getElementById('phoneNumber').value = phone.number || '';
                        document.getElementById('phone-status-toggle').checked = phone.active;
                        document.getElementById('phone-status').value = phone.active ? 'active' : 'inactive';
                        document.getElementById('phone-status-text').textContent = phone.active ? 'Active' : 'Inactive';
                    } else {
                        showErrorNotification('Error loading phone data: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    hideLoading();
                    showErrorNotification('Error loading phone data: ' + error.message);
                });
        } else {
            document.getElementById('phoneModalTitle').textContent = 'Add Phone Number';
            document.getElementById('submitPhone').textContent = 'Save Phone';
        }

        document.getElementById('phoneModal').classList.remove('hidden');
    }

    function hidePhoneModal() {
        document.getElementById('phoneModal').classList.add('hidden');
        resetPhoneForm();
    }

    function resetPhoneForm() {
        document.getElementById('phoneForm').reset();
        document.getElementById('phoneId').value = '';

        document.getElementById('phone-status-toggle').checked = true;
        document.getElementById('phone-status-text').textContent = 'Active';
        document.getElementById('phone-status').value = 'active';
    }

    function submitPhoneForm() {
        const number = document.getElementById('phoneNumber').value.trim();

        if (!number) {
            showErrorNotification('Phone number is required');
            return;
        }

        showLoading('Saving phone number...');

        const phoneId = document.getElementById('phoneId').value || Date.now().toString();
        const formData = {
            id: phoneId,
            number: number,
            active: document.getElementById('phone-status-toggle').checked,
            order: document.getElementById('phoneId').value ? parseInt(document.querySelector(`[data-id="${phoneId}"]`)?.dataset.order || 1) : document.querySelectorAll('#phones-container > div').length + 1
        };

        saveContactData('phone', formData, () => {
            hidePhoneModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        });
    }

    function showEmailModal(emailId = null) {
        resetEmailForm();

        if (emailId) {
            document.getElementById('emailModalTitle').textContent = 'Edit Email Address';
            document.getElementById('submitEmail').textContent = 'Update Email';
            document.getElementById('emailId').value = emailId;

            showLoading('Loading email data...');

            fetch('fetch/manageContactUs.php?action=get_email&id=' + emailId)
                .then(response => response.json())
                .then(data => {
                    hideLoading();

                    if (data.success && data.email) {
                        const email = data.email;

                        document.getElementById('emailAddress').value = email.address || '';
                        document.getElementById('email-status-toggle').checked = email.active;
                        document.getElementById('email-status').value = email.active ? 'active' : 'inactive';
                        document.getElementById('email-status-text').textContent = email.active ? 'Active' : 'Inactive';
                    } else {
                        showErrorNotification('Error loading email data: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    hideLoading();
                    showErrorNotification('Error loading email data: ' + error.message);
                });
        } else {
            document.getElementById('emailModalTitle').textContent = 'Add Email Address';
            document.getElementById('submitEmail').textContent = 'Save Email';
        }

        document.getElementById('emailModal').classList.remove('hidden');
    }

    function hideEmailModal() {
        document.getElementById('emailModal').classList.add('hidden');
        resetEmailForm();
    }

    function resetEmailForm() {
        document.getElementById('emailForm').reset();
        document.getElementById('emailId').value = '';

        document.getElementById('email-status-toggle').checked = true;
        document.getElementById('email-status-text').textContent = 'Active';
        document.getElementById('email-status').value = 'active';
    }

    function submitEmailForm() {
        const address = document.getElementById('emailAddress').value.trim();

        if (!address) {
            showErrorNotification('Email address is required');
            return;
        }

        showLoading('Saving email address...');

        const emailId = document.getElementById('emailId').value || Date.now().toString();
        const formData = {
            id: emailId,
            address: address,
            active: document.getElementById('email-status-toggle').checked,
            order: document.getElementById('emailId').value ? parseInt(document.querySelector(`[data-id="${emailId}"]`)?.dataset.order || 1) : document.querySelectorAll('#emails-container > div').length + 1
        };

        saveContactData('email', formData, () => {
            hideEmailModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        });
    }

    function showLocationModal(locationId = null) {
        resetLocationForm();

        if (locationId) {
            document.getElementById('locationModalTitle').textContent = 'Edit Location Line';
            document.getElementById('submitLocation').textContent = 'Update Location';
            document.getElementById('locationId').value = locationId;

            showLoading('Loading location data...');

            fetch('fetch/manageContactUs.php?action=get_location&id=' + locationId)
                .then(response => response.json())
                .then(data => {
                    hideLoading();

                    if (data.success && data.location) {
                        const location = data.location;

                        document.getElementById('locationLine').value = location.line || '';
                        document.getElementById('location-status-toggle').checked = location.active;
                        document.getElementById('location-status').value = location.active ? 'active' : 'inactive';
                        document.getElementById('location-status-text').textContent = location.active ? 'Active' : 'Inactive';
                    } else {
                        showErrorNotification('Error loading location data: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    hideLoading();
                    showErrorNotification('Error loading location data: ' + error.message);
                });
        } else {
            document.getElementById('locationModalTitle').textContent = 'Add Location Line';
            document.getElementById('submitLocation').textContent = 'Save Location';
        }

        document.getElementById('locationModal').classList.remove('hidden');
    }

    function hideLocationModal() {
        document.getElementById('locationModal').classList.add('hidden');
        resetLocationForm();
    }

    function resetLocationForm() {
        document.getElementById('locationForm').reset();
        document.getElementById('locationId').value = '';

        document.getElementById('location-status-toggle').checked = true;
        document.getElementById('location-status-text').textContent = 'Active';
        document.getElementById('location-status').value = 'active';
    }

    function submitLocationForm() {
        const line = document.getElementById('locationLine').value.trim();

        if (!line) {
            showErrorNotification('Location line is required');
            return;
        }

        showLoading('Saving location line...');

        const locationId = document.getElementById('locationId').value || Date.now().toString();
        const formData = {
            id: locationId,
            line: line,
            active: document.getElementById('location-status-toggle').checked,
            order: document.getElementById('locationId').value ? parseInt(document.querySelector(`[data-id="${locationId}"]`)?.dataset.order || 1) : document.querySelectorAll('#location-container > div').length + 1
        };

        saveContactData('location', formData, () => {
            hideLocationModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        });
    }

    function showSocialModal(socialId, platform) {
        resetSocialForm();

        document.getElementById('socialModalTitle').textContent = 'Edit ' + capitalizeFirstLetter(platform) + ' Link';
        document.getElementById('submitSocial').textContent = 'Update Social Link';
        document.getElementById('socialId').value = socialId;
        document.getElementById('socialPlatform').value = platform;

        showLoading('Loading social data...');

        fetch('fetch/manageContactUs.php?action=get_social&id=' + socialId)
            .then(response => response.json())
            .then(data => {
                hideLoading();

                if (data.success && data.social) {
                    const social = data.social;

                    document.getElementById('socialUrl').value = social.url || '';
                    document.getElementById('social-status-toggle').checked = social.active;
                    document.getElementById('social-status').value = social.active ? 'active' : 'inactive';
                    document.getElementById('social-status-text').textContent = social.active ? 'Active' : 'Inactive';
                } else {
                    showErrorNotification('Error loading social data: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                hideLoading();
                showErrorNotification('Error loading social data: ' + error.message);
            });

        document.getElementById('socialModal').classList.remove('hidden');
    }

    function hideSocialModal() {
        document.getElementById('socialModal').classList.add('hidden');
        resetSocialForm();
    }

    function resetSocialForm() {
        document.getElementById('socialForm').reset();
        document.getElementById('socialId').value = '';
        document.getElementById('socialPlatform').value = '';

        document.getElementById('social-status-toggle').checked = true;
        document.getElementById('social-status-text').textContent = 'Active';
        document.getElementById('social-status').value = 'active';
    }

    function submitSocialForm() {
        const url = document.getElementById('socialUrl').value.trim();
        const platform = document.getElementById('socialPlatform').value;

        if (!url) {
            showErrorNotification('URL is required');
            return;
        }

        if (!platform) {
            showErrorNotification('Platform is required');
            return;
        }

        showLoading('Saving social link...');

        const socialId = document.getElementById('socialId').value;
        const formData = {
            id: socialId,
            platform: platform,
            url: url,
            active: document.getElementById('social-status-toggle').checked,
            order: parseInt(document.querySelector(`[data-id="${socialId}"]`)?.dataset.order || 1)
        };

        saveContactData('social', formData, () => {
            hideSocialModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        });
    }

    function showFieldModal(fieldId = null) {
        resetFieldForm();

        if (fieldId) {
            document.getElementById('fieldModalTitle').textContent = 'Edit Form Field';
            document.getElementById('submitField').textContent = 'Update Field';
            document.getElementById('fieldId').value = fieldId;
            document.getElementById('fieldIdInput').disabled = true;

            showLoading('Loading field data...');

            fetch('fetch/manageContactUs.php?action=get_field&id=' + fieldId)
                .then(response => response.json())
                .then(data => {
                    hideLoading();

                    if (data.success && data.field) {
                        const field = data.field;

                        document.getElementById('fieldIdInput').value = field.id || '';
                        document.getElementById('fieldLabel').value = field.label || '';
                        document.getElementById('fieldPlaceholder').value = field.placeholder || '';
                        document.getElementById('fieldRequired').checked = field.required;
                    } else {
                        showErrorNotification('Error loading field data: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    hideLoading();
                    showErrorNotification('Error loading field data: ' + error.message);
                });
        } else {
            document.getElementById('fieldModalTitle').textContent = 'Add Form Field';
            document.getElementById('submitField').textContent = 'Save Field';
            document.getElementById('fieldIdInput').disabled = false;
        }

        document.getElementById('fieldModal').classList.remove('hidden');
    }

    function hideFieldModal() {
        document.getElementById('fieldModal').classList.add('hidden');
        resetFieldForm();
    }

    function resetFieldForm() {
        document.getElementById('fieldForm').reset();
        document.getElementById('fieldId').value = '';
        document.getElementById('fieldIdInput').disabled = false;
    }

    function submitFieldForm() {
        const id = document.getElementById('fieldIdInput').value.trim();
        const label = document.getElementById('fieldLabel').value.trim();
        const placeholder = document.getElementById('fieldPlaceholder').value.trim();
        const required = document.getElementById('fieldRequired').checked;

        if (!id) {
            showErrorNotification('Field ID is required');
            return;
        }

        if (!label) {
            showErrorNotification('Field label is required');
            return;
        }

        if (!/^[a-z0-9_]+$/.test(id)) {
            showErrorNotification('Field ID must contain only lowercase letters, numbers, and underscores');
            return;
        }

        showLoading('Saving form field...');

        const fieldId = document.getElementById('fieldId').value || id;
        const formData = {
            id: fieldId,
            label: label,
            placeholder: placeholder,
            required: required,
            order: document.getElementById('fieldId').value ? parseInt(document.querySelector(`[data-id="${fieldId}"]`)?.dataset.order || 1) : document.querySelectorAll('#fields-container > div').length + 1
        };

        saveContactData('field', formData, () => {
            hideFieldModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        });
    }

    function submitFormTitleForm() {
        const title = document.getElementById('form-title').value.trim();
        const description = document.getElementById('form-description').value.trim();
        const buttonText = document.getElementById('button-text').value.trim();

        if (!title) {
            showErrorNotification('Form title is required');
            return;
        }

        if (!buttonText) {
            showErrorNotification('Button text is required');
            return;
        }

        showLoading('Saving form settings...');

        const formData = {
            title: title,
            description: description,
            buttonText: buttonText
        };

        saveContactData('formTitle', formData);
    }

    function submitMapSettingsForm() {
        const latitude = document.getElementById('map-latitude').value.trim();
        const longitude = document.getElementById('map-longitude').value.trim();
        const zoom = document.getElementById('map-zoom').value.trim();

        if (!latitude || !longitude || !zoom) {
            showErrorNotification('All map coordinates are required');
            return;
        }

        showLoading('Saving map settings...');

        const formData = {
            latitude: latitude,
            longitude: longitude,
            zoom: parseInt(zoom)
        };

        saveContactData('mapSettings', formData, () => {
            updateMapPreview();
        });
    }

    function updateMapPreview() {
        const latitude = document.getElementById('map-latitude')?.value || '0.31654191425996444';
        const longitude = document.getElementById('map-longitude')?.value || '32.629696775378866';
        const zoom = document.getElementById('map-zoom')?.value || '15';

        const mapPreview = document.getElementById('map-preview');
        if (mapPreview) {
            const mapUrl = `https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.7570741913936!2d${longitude}!3d${latitude}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMMKwMTgnNTkuNiJOIDMywrAzNyc1NC44IkU!5e0!3m2!1sen!2sus!4v1621234567890!5m2!1sen!2sus&z=${zoom}`;
            mapPreview.src = mapUrl;
        }
    }

    function showDeleteModal(type, id) {
        deleteItemType = type;
        deleteItemId = id;

        let itemName = '';
        let itemStatus = '';
        let typeLabel = '';

        if (type === 'phone') {
            document.getElementById('deleteModalTitle').textContent = 'Delete Phone Number';
            document.getElementById('deleteModalMessage').textContent = 'Are you sure you want to delete this phone number? This action cannot be undone.';
            typeLabel = 'Phone:';

            const phoneElement = document.querySelector(`#phones-container [data-id="${id}"]`);
            if (phoneElement) {
                itemName = phoneElement.querySelector('.font-medium').textContent;
                itemStatus = phoneElement.querySelector('.phone-status-toggle').checked ? 'Active' : 'Inactive';
            }
        } else if (type === 'email') {
            document.getElementById('deleteModalTitle').textContent = 'Delete Email Address';
            document.getElementById('deleteModalMessage').textContent = 'Are you sure you want to delete this email address? This action cannot be undone.';
            typeLabel = 'Email:';

            const emailElement = document.querySelector(`#emails-container [data-id="${id}"]`);
            if (emailElement) {
                itemName = emailElement.querySelector('.font-medium').textContent;
                itemStatus = emailElement.querySelector('.email-status-toggle').checked ? 'Active' : 'Inactive';
            }
        } else if (type === 'location') {
            document.getElementById('deleteModalTitle').textContent = 'Delete Location Line';
            document.getElementById('deleteModalMessage').textContent = 'Are you sure you want to delete this location line? This action cannot be undone.';
            typeLabel = 'Location:';

            const locationElement = document.querySelector(`#location-container [data-id="${id}"]`);
            if (locationElement) {
                itemName = locationElement.querySelector('.font-medium').textContent;
                itemStatus = locationElement.querySelector('.location-status-toggle').checked ? 'Active' : 'Inactive';
            }
        } else if (type === 'field') {
            document.getElementById('deleteModalTitle').textContent = 'Delete Form Field';
            document.getElementById('deleteModalMessage').textContent = 'Are you sure you want to delete this form field? This action cannot be undone.';
            typeLabel = 'Field:';

            const fieldElement = document.querySelector(`#fields-container [data-id="${id}"]`);
            if (fieldElement) {
                itemName = fieldElement.querySelector('.font-medium').textContent;
                itemStatus = fieldElement.querySelector('.text-xs.font-medium').textContent;
            }
        }

        document.getElementById('deleteItemTypeLabel').textContent = typeLabel;
        document.getElementById('deleteItemName').textContent = itemName;
        document.getElementById('deleteItemStatus').textContent = itemStatus;

        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function hideDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        deleteItemType = '';
        deleteItemId = '';
    }

    function confirmDelete() {
        if (!deleteItemType || !deleteItemId) {
            hideDeleteModal();
            return;
        }

        showLoading(`Deleting ${deleteItemType}...`);

        saveContactData(`${deleteItemType}Delete`, { id: deleteItemId }, () => {
            hideDeleteModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        });
    }

    function saveContactData(section, content, callback) {
        fetch('fetch/manageContactUs.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_section_content',
                page: 'contact-us',
                section: section,
                content: content
            })
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification(`${capitalizeFirstLetter(section)} saved successfully`);
                    if (callback) callback();
                } else {
                    showErrorNotification(`Error saving ${section}: ` + data.message);
                }
            })
            .catch(error => {
                hideLoading();
                showErrorNotification(`Error saving ${section}: ` + error.message);
            });
    }

    function showSuccessNotification(message) {
        const notification = document.getElementById('successNotification');
        const messageEl = document.getElementById('successMessage');

        messageEl.textContent = message;
        notification.classList.remove('hidden');

        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    function showErrorNotification(message) {
        const notification = document.getElementById('errorNotification');
        const messageEl = document.getElementById('errorMessage');

        messageEl.textContent = message;
        notification.classList.remove('hidden');

        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    document.addEventListener('click', function (event) {
        const mobileDropdown = document.getElementById('mobile-tab-dropdown');
        const mobileToggle = document.getElementById('mobile-tab-toggle');

        if (mobileDropdown && mobileToggle && !mobileDropdown.contains(event.target) && !mobileToggle.contains(event.target)) {
            mobileDropdown.classList.add('hidden');
            document.getElementById('mobile-tab-chevron').classList.remove('rotate-180');
        }
    });

    window.switchTab = switchTab;
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>