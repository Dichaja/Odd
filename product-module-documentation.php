<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Module Documentation</title>

    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- Rubik Font -->
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            font-family: 'Rubik', sans-serif;
            color: #333;
            background-color: #f8f9fa;
        }

        .sidebar {
            background-color: #f8f9fa;
            border-right: 1px solid #e2e8f0;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
            transition: all 0.3s;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 50;
                width: 80% !important;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40;
            }

            .overlay.active {
                display: block;
            }
        }

        .content {
            min-height: 100vh;
            transition: margin-left 0.3s;
        }

        .nav-item {
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }

        .nav-item:hover,
        .nav-item.active {
            background-color: #e2e8f0;
            border-left-color: #4f46e5;
        }

        .section {
            display: none;
            opacity: 0;
            transition: opacity 0.5s;
        }

        .section.active {
            display: block;
            opacity: 1;
        }

        .step {
            display: none;
            opacity: 0;
            transition: opacity 0.5s;
        }

        .step.active {
            display: block;
            opacity: 1;
        }

        .code-block {
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            overflow-x: auto;
        }

        code {
            font-family: Monaco, Consolas, "Andale Mono", "DejaVu Sans Mono", monospace;
        }

        .json .string {
            color: #d63031;
        }

        .json .number {
            color: #0984e3;
        }

        .json .boolean {
            color: #8e44ad;
        }

        .json .null {
            color: #d63031;
        }

        .json .key {
            color: #6c5ce7;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table th {
            background-color: #f7fafc;
            text-align: left;
        }

        table th,
        table td {
            border: 1px solid #e2e8f0;
            padding: 0.75rem;
        }

        .mobile-nav-toggle {
            display: none;
        }

        @media (max-width: 768px) {
            .mobile-nav-toggle {
                display: block;
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 60;
                padding: 0.5rem;
                background-color: white;
                border-radius: 0.25rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .content-container {
                padding-top: 4rem !important;
            }

            .step-navigation {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background-color: white;
                padding: 0.75rem;
                box-shadow: 0 -1px 3px rgba(0, 0, 0, 0.1);
                z-index: 30;
            }

            .table-container {
                overflow-x: auto;
            }
        }
    </style>
</head>

<body class="text-gray-800">
    <!-- Mobile Navigation Toggle -->
    <button class="mobile-nav-toggle">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- Overlay for mobile -->
    <div class="overlay"></div>

    <!-- Sidebar Navigation -->
    <div class="sidebar w-64 p-4">
        <h2 class="text-xl font-bold mb-6">Product Module</h2>
        <nav>
            <ul>
                <li class="mb-2">
                    <a href="#introduction" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Introduction</a>
                </li>
                <li class="mb-1">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mt-4 mb-2">Categories</h3>
                    <ul>
                        <li><a href="#categories-get" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Get Categories</a></li>
                        <li><a href="#categories-get-single" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Get Category</a></li>
                        <li><a href="#categories-create" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Create Category</a></li>
                        <li><a href="#categories-update" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Update Category</a></li>
                        <li><a href="#categories-delete" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Delete Category</a></li>
                    </ul>
                </li>
                <li class="mb-1">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mt-4 mb-2">Package Definitions</h3>
                    <ul>
                        <li><a href="#packages-get-names" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Get Package Names</a></li>
                        <li><a href="#packages-get-name" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Get Package Name</a></li>
                        <li><a href="#packages-create-name" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Create Package Name</a></li>
                        <li><a href="#packages-update-name" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Update Package Name</a></li>
                        <li><a href="#packages-delete-name" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Delete Package Name</a></li>
                        <li><a href="#packages-get-units" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Get SI Units</a></li>
                        <li><a href="#packages-get-unit" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Get SI Unit</a></li>
                        <li><a href="#packages-create-unit" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Create SI Unit</a></li>
                        <li><a href="#packages-update-unit" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Update SI Unit</a></li>
                        <li><a href="#packages-delete-unit" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Delete SI Unit</a></li>
                    </ul>
                </li>
                <li class="mb-1">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 mt-4 mb-2">Products</h3>
                    <ul>
                        <li><a href="#products-get" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Get Products</a></li>
                        <li><a href="#products-get-single" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Get Product</a></li>
                        <li><a href="#products-create" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Create Product</a></li>
                        <li><a href="#products-update" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Update Product</a></li>
                        <li><a href="#products-delete" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Delete Product</a></li>
                        <li><a href="#products-toggle-featured" class="nav-item block px-3 py-2 rounded-md text-sm font-medium nav-link">Toggle Featured</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="content ml-0 md:ml-64">
        <div class="content-container max-w-5xl mx-auto p-4 md:p-8">
            <!-- Introduction Section -->
            <section id="introduction-section" class="section active">
                <div id="introduction" class="step active">
                    <h1 class="text-3xl font-bold mb-6">Product Module Documentation</h1>
                    <p class="mb-4">This documentation provides a comprehensive guide to the Product Module API, which allows you to manage product categories, package definitions, and products.</p>
                    <p class="mb-4">The Product Module API is a RESTful API that supports standard HTTP methods like GET, POST, PUT, and DELETE. All requests and responses are in JSON format.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Authentication</h2>
                    <p class="mb-4">All API endpoints require administrative authentication. You must be logged in as an admin user to access these endpoints.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Base URL</h2>
                    <p class="mb-4">The base URL for all API endpoints is your site's domain followed by the specific endpoint path.</p>

                    <div class="code-block p-4 mb-6">
                        <code>https://your-domain.com/admin/fetch/</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Error Handling</h2>
                    <p class="mb-4">All API endpoints return standardized error responses with appropriate HTTP status codes. Error responses include a success flag (set to false) and an error message.</p>

                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">false</span>,
    <span class="key">"message"</span>: <span class="string">"Error message description"</span>
}</code></pre>
                    </div>
                </div>
            </section>

            <!-- Categories Section -->
            <section id="categories-section" class="section">
                <!-- Get Categories -->
                <div id="categories-get" class="step">
                    <h1 class="text-3xl font-bold mb-6">Get All Categories</h1>
                    <p class="mb-4">This endpoint retrieves all product categories.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">GET</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProductCategories.php?action=getCategories</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <p class="mb-4">Returns an array of category objects.</p>

                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"categories"</span>: [
        {
            <span class="key">"uuid_id"</span>: <span class="string">"01h2c5n3a9s4f6g7h8j9k0l1m2"</span>,
            <span class="key">"name"</span>: <span class="string">"Electronics"</span>,
            <span class="key">"description"</span>: <span class="string">"Electronic devices and accessories"</span>,
            <span class="key">"meta_title"</span>: <span class="string">"Electronics"</span>,
            <span class="key">"meta_description"</span>: <span class="string">"Browse our electronics collection"</span>,
            <span class="key">"meta_keywords"</span>: <span class="string">"electronics, devices, gadgets"</span>,
            <span class="key">"status"</span>: <span class="string">"active"</span>,
            <span class="key">"created_at"</span>: <span class="string">"2023-06-01 10:00:00"</span>,
            <span class="key">"updated_at"</span>: <span class="string">"2023-06-01 10:00:00"</span>,
            <span class="key">"image_url"</span>: <span class="string">"https://your-domain.com/img/product-categories/01h2c5n3a9s4f6g7h8j9k0l1m2/electronics.jpg"</span>
        }
    ]
}</code></pre>
                    </div>
                </div>

                <!-- Get Category -->
                <div id="categories-get-single" class="step">
                    <h1 class="text-3xl font-bold mb-6">Get Category</h1>
                    <p class="mb-4">This endpoint retrieves a specific product category by ID.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">GET</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProductCategories.php?action=getCategory&id={category_id}</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the category.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <p class="mb-4">Returns the requested category object.</p>

                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"data"</span>: {
        <span class="key">"uuid_id"</span>: <span class="string">"01h2c5n3a9s4f6g7h8j9k0l1m2"</span>,
        <span class="key">"name"</span>: <span class="string">"Electronics"</span>,
        <span class="key">"description"</span>: <span class="string">"Electronic devices and accessories"</span>,
        <span class="key">"meta_title"</span>: <span class="string">"Electronics"</span>,
        <span class="key">"meta_description"</span>: <span class="string">"Browse our electronics collection"</span>,
        <span class="key">"meta_keywords"</span>: <span class="string">"electronics, devices, gadgets"</span>,
        <span class="key">"status"</span>: <span class="string">"active"</span>,
        <span class="key">"created_at"</span>: <span class="string">"2023-06-01 10:00:00"</span>,
        <span class="key">"updated_at"</span>: <span class="string">"2023-06-01 10:00:00"</span>,
        <span class="key">"image_url"</span>: <span class="string">"https://your-domain.com/img/product-categories/01h2c5n3a9s4f6g7h8j9k0l1m2/electronics.jpg"</span>,
        <span class="key">"has_image"</span>: <span class="boolean">true</span>
    }
}</code></pre>
                    </div>
                </div>

                <!-- Create Category -->
                <div id="categories-create" class="step">
                    <h1 class="text-3xl font-bold mb-6">Create Category</h1>
                    <p class="mb-4">This endpoint creates a new product category.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">POST</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProductCategories.php?action=createCategory</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>name</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The name of the category.</td>
                                </tr>
                                <tr>
                                    <td>description</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>A description of the category.</td>
                                </tr>
                                <tr>
                                    <td>meta_title</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The meta title for SEO.</td>
                                </tr>
                                <tr>
                                    <td>meta_description</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The meta description for SEO.</td>
                                </tr>
                                <tr>
                                    <td>meta_keywords</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The meta keywords for SEO.</td>
                                </tr>
                                <tr>
                                    <td>status</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The status of the category. Values: "active" or "inactive". Default: "active".</td>
                                </tr>
                                <tr>
                                    <td>temp_image_path</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The path to a temporarily uploaded image file.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Sample Request</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"name"</span>: <span class="string">"Electronics"</span>,
    <span class="key">"description"</span>: <span class="string">"Electronic devices and accessories"</span>,
    <span class="key">"meta_title"</span>: <span class="string">"Electronics"</span>,
    <span class="key">"meta_description"</span>: <span class="string">"Browse our electronics collection"</span>,
    <span class="key">"meta_keywords"</span>: <span class="string">"electronics, devices, gadgets"</span>,
    <span class="key">"status"</span>: <span class="string">"active"</span>,
    <span class="key">"temp_image_path"</span>: <span class="string">"uploads/temp/temp_12345.jpg"</span>
}</code></pre>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <p class="mb-4">Returns the ID of the newly created category.</p>

                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"message"</span>: <span class="string">"Category created successfully"</span>,
    <span class="key">"id"</span>: <span class="string">"01h2c5n3a9s4f6g7h8j9k0l1m2"</span>
}</code></pre>
                    </div>
                </div>

                <!-- Update Category -->
                <div id="categories-update" class="step">
                    <h1 class="text-3xl font-bold mb-6">Update Category</h1>
                    <p class="mb-4">This endpoint updates an existing product category.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">POST</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProductCategories.php?action=updateCategory</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the category.</td>
                                </tr>
                                <tr>
                                    <td>name</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The name of the category.</td>
                                </tr>
                                <tr>
                                    <td>description</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>A description of the category.</td>
                                </tr>
                                <tr>
                                    <td>meta_title</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The meta title for SEO.</td>
                                </tr>
                                <tr>
                                    <td>meta_description</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The meta description for SEO.</td>
                                </tr>
                                <tr>
                                    <td>meta_keywords</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The meta keywords for SEO.</td>
                                </tr>
                                <tr>
                                    <td>status</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The status of the category. Values: "active" or "inactive".</td>
                                </tr>
                                <tr>
                                    <td>temp_image_path</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The path to a temporarily uploaded image file.</td>
                                </tr>
                                <tr>
                                    <td>remove_image</td>
                                    <td>boolean</td>
                                    <td>Optional</td>
                                    <td>Whether to remove the existing image. Default: false.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Sample Request</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"id"</span>: <span class="string">"01h2c5n3a9s4f6g7h8j9k0l1m2"</span>,
    <span class="key">"name"</span>: <span class="string">"Electronics Updated"</span>,
    <span class="key">"description"</span>: <span class="string">"Updated description for electronics"</span>,
    <span class="key">"meta_title"</span>: <span class="string">"Electronics - Updated"</span>,
    <span class="key">"meta_description"</span>: <span class="string">"Updated meta description"</span>,
    <span class="key">"meta_keywords"</span>: <span class="string">"electronics, updated, gadgets"</span>,
    <span class="key">"status"</span>: <span class="string">"active"</span>,
    <span class="key">"temp_image_path"</span>: <span class="string">"uploads/temp/temp_67890.jpg"</span>
}</code></pre>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"message"</span>: <span class="string">"Category updated successfully"</span>
}</code></pre>
                    </div>
                </div>

                <!-- Delete Category -->
                <div id="categories-delete" class="step">
                    <h1 class="text-3xl font-bold mb-6">Delete Category</h1>
                    <p class="mb-4">This endpoint deletes a product category by ID.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">POST</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProductCategories.php?action=deleteCategory</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the category to delete.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Sample Request</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"id"</span>: <span class="string">"01h2c5n3a9s4f6g7h8j9k0l1m2"</span>
}</code></pre>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"message"</span>: <span class="string">"Category deleted successfully"</span>
}</code></pre>
                    </div>
                </div>
            </section>

            <!-- Packages Section -->
            <section id="packages-section" class="section">
                <!-- Get Package Names -->
                <div id="packages-get-names" class="step">
                    <h1 class="text-3xl font-bold mb-6">Get Package Names</h1>
                    <p class="mb-4">This endpoint retrieves all package names.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">GET</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProductPackages.php?action=getPackageNames</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <p class="mb-4">Returns an array of package name objects.</p>

                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"packageNames"</span>: [
        {
            <span class="key">"uuid_id"</span>: <span class="string">"01h2c5n3b9s4f6g7h8j9k0l1m3"</span>,
            <span class="key">"package_name"</span>: <span class="string">"Kilogram"</span>,
            <span class="key">"created_at"</span>: <span class="string">"2023-06-01 10:00:00"</span>,
            <span class="key">"updated_at"</span>: <span class="string">"2023-06-01 10:00:00"</span>
        },
        {
            <span class="key">"uuid_id"</span>: <span class="string">"01h2c5n3c9s4f6g7h8j9k0l1m4"</span>,
            <span class="key">"package_name"</span>: <span class="string">"Liter"</span>,
            <span class="key">"created_at"</span>: <span class="string">"2023-06-01 10:05:00"</span>,
            <span class="key">"updated_at"</span>: <span class="string">"2023-06-01 10:05:00"</span>
        }
    ]
}</code></pre>
                    </div>
                </div>

                <!-- Get Package Name -->
                <div id="packages-get-name" class="step">
                    <h1 class="text-3xl font-bold mb-6">Get Package Name</h1>
                    <p class="mb-4">This endpoint retrieves a specific package name by ID.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">GET</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProductPackages.php?action=getPackageName&id={package_name_id}</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the package name.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <p class="mb-4">Returns the requested package name object.</p>

                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"data"</span>: {
        <span class="key">"uuid_id"</span>: <span class="string">"01h2c5n3b9s4f6g7h8j9k0l1m3"</span>,
        <span class="key">"package_name"</span>: <span class="string">"Kilogram"</span>,
        <span class="key">"created_at"</span>: <span class="string">"2023-06-01 10:00:00"</span>,
        <span class="key">"updated_at"</span>: <span class="string">"2023-06-01 10:00:00"</span>
    }
}</code></pre>
                    </div>
                </div>

                <!-- Create Package Name -->
                <div id="packages-create-name" class="step">
                    <h1 class="text-3xl font-bold mb-6">Create Package Name</h1>
                    <p class="mb-4">This endpoint creates a new package name.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">POST</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProductPackages.php?action=createPackageName</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>package_name</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The name of the package (e.g., "Kilogram", "Liter").</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Sample Request</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"package_name"</span>: <span class="string">"Meter"</span>
}</code></pre>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <p class="mb-4">Returns the ID of the newly created package name.</p>

                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"message"</span>: <span class="string">"Package name created successfully"</span>,
    <span class="key">"id"</span>: <span class="string">"01h2c5n3d9s4f6g7h8j9k0l1m5"</span>
}</code></pre>
                    </div>
                </div>

                <!-- Update Package Name -->
                <div id="packages-update-name" class="step">
                    <h1 class="text-3xl font-bold mb-6">Update Package Name</h1>
                    <p class="mb-4">This endpoint updates an existing package name.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">POST</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProductPackages.php?action=updatePackageName</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the package name.</td>
                                </tr>
                                <tr>
                                    <td>package_name</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The updated name of the package.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Sample Request</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"id"</span>: <span class="string">"01h2c5n3d9s4f6g7h8j9k0l1m5"</span>,
    <span class="key">"package_name"</span>: <span class="string">"Square Meter"</span>
}</code></pre>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"message"</span>: <span class="string">"Package name updated successfully"</span>
}</code></pre>
                    </div>
                </div>

                <!-- Delete Package Name -->
                <div id="packages-delete-name" class="step">
                    <h1 class="text-3xl font-bold mb-6">Delete Package Name</h1>
                    <p class="mb-4">This endpoint deletes a package name by ID.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">POST</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProductPackages.php?action=deletePackageName</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the package name to delete.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Sample Request</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"id"</span>: <span class="string">"01h2c5n3d9s4f6g7h8j9k0l1m5"</span>
}</code></pre>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"message"</span>: <span class="string">"Package name deleted successfully"</span>
}</code></pre>
                    </div>
                </div>

                <!-- Get SI Units -->
                <div id="packages-get-units" class="step">
                    <h1 class="text-3xl font-bold mb-6">Get SI Units</h1>
                    <p class="mb-4">This endpoint retrieves all SI units, optionally filtered by package name ID.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">GET</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProductPackages.php?action=getSIUnits</code>
                    </div>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProductPackages.php?action=getSIUnits&package_name_id={package_name_id}</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>package_name_id</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>Filter SI units by package name ID.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <p class="mb-4">Returns an array of SI unit objects.</p>

                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"siUnits"</span>: [
        {
            <span class="key">"uuid_id"</span>: <span class="string">"01h2c5n3e9s4f6g7h8j9k0l1m6"</span>,
            <span class="key">"si_unit"</span>: <span class="string">"kg"</span>,
            <span class="key">"created_at"</span>: <span class="string">"2023-06-01 10:10:00"</span>,
            <span class="key">"updated_at"</span>: <span class="string">"2023-06-01 10:10:00"</span>,
            <span class="key">"package_name"</span>: <span class="string">"Kilogram"</span>,
            <span class="key">"package_name_uuid_id"</span>: <span class="string">"01h2c5n3b9s4f6g7h8j9k0l1m3"</span>,
            <span class="key">"unit_of_measure"</span>: <span class="string">"Kilogram (kg)"</span>
        },
        {
            <span class="key">"uuid_id"</span>: <span class="string">"01h2c5n3f9s4f6g7h8j9k0l1m7"</span>,
            <span class="key">"si_unit"</span>: <span class="string">"g"</span>,
            <span class="key">"created_at"</span>: <span class="string">"2023-06-01 10:15:00"</span>,
            <span class="key">"updated_at"</span>: <span class="string">"2023-06-01 10:15:00"</span>,
            <span class="key">"package_name"</span>: <span class="string">"Kilogram"</span>,
            <span class="key">"package_name_uuid_id"</span>: <span class="string">"01h2c5n3b9s4f6g7h8j9k0l1m3"</span>,
            <span class="key">"unit_of_measure"</span>: <span class="string">"Kilogram (g)"</span>
        }
    ]
}</code></pre>
                    </div>
                </div>

                <!-- Get SI Unit -->
                <div id="packages-get-unit" class="step">
                    <h1 class="text-3xl font-bold mb-6">Get SI Unit</h1>
                    <p class="mb-4">This endpoint retrieves a specific SI unit by ID.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">GET</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProductPackages.php?action=getSIUnit&id={si_unit_id}</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the SI unit.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <p class="mb-4">Returns the requested SI unit object.</p>

                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"data"</span>: {
        <span class="key">"uuid_id"</span>: <span class="string">"01h2c5n3e9s4f6g7h8j9k0l1m6"</span>,
        <span class="key">"si_unit"</span>: <span class="string">"kg"</span>,
        <span class="key">"created_at"</span>: <span class="string">"2023-06-01 10:10:00"</span>,
        <span class="key">"updated_at"</span>: <span class="string">"2023-06-01 10:10:00"</span>,
        <span class="key">"package_name"</span>: <span class="string">"Kilogram"</span>,
        <span class="key">"package_name_uuid_id"</span>: <span class="string">"01h2c5n3b9s4f6g7h8j9k0l1m3"</span>,
        <span class="key">"unit_of_measure"</span>: <span class="string">"Kilogram (kg)"</span>
    }
}</code></pre>
                    </div>
                </div>

                <!-- Create SI Unit -->
                <div id="packages-create-unit" class="step">
                    <h1 class="text-3xl font-bold mb-6">Create SI Unit</h1>
                    <p class="mb-4">This endpoint creates a new SI unit for a package name.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">POST</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProductPackages.php?action=createSIUnit</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>package_name_id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the package name.</td>
                                </tr>
                                <tr>
                                    <td>si_unit</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The SI unit symbol (e.g., "kg", "g", "L").</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Sample Request</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"package_name_id"</span>: <span class="string">"01h2c5n3b9s4f6g7h8j9k0l1m3"</span>,
    <span class="key">"si_unit"</span>: <span class="string">"mg"</span>
}</code></pre>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <p class="mb-4">Returns the ID of the newly created SI unit.</p>

                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"message"</span>: <span class="string">"SI unit created successfully"</span>,
    <span class="key">"id"</span>: <span class="string">"01h2c5n3g9s4f6g7h8j9k0l1m8"</span>,
    <span class="key">"unit_of_measure"</span>: <span class="string">"Kilogram (mg)"</span>
}</code></pre>
                    </div>
                </div>

                <!-- Update SI Unit -->
                <div id="packages-update-unit" class="step">
                    <h1 class="text-3xl font-bold mb-6">Update SI Unit</h1>
                    <p class="mb-4">This endpoint updates an existing SI unit.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">POST</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProductPackages.php?action=updateSIUnit</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the SI unit.</td>
                                </tr>
                                <tr>
                                    <td>package_name_id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the package name.</td>
                                </tr>
                                <tr>
                                    <td>si_unit</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The updated SI unit symbol.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Sample Request</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"id"</span>: <span class="string">"01h2c5n3g9s4f6g7h8j9k0l1m8"</span>,
    <span class="key">"package_name_id"</span>: <span class="string">"01h2c5n3b9s4f6g7h8j9k0l1m3"</span>,
    <span class="key">"si_unit"</span>: <span class="string">"mcg"</span>
}</code></pre>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"message"</span>: <span class="string">"SI unit updated successfully"</span>,
    <span class="key">"unit_of_measure"</span>: <span class="string">"Kilogram (mcg)"</span>
}</code></pre>
                    </div>
                </div>

                <!-- Delete SI Unit -->
                <div id="packages-delete-unit" class="step">
                    <h1 class="text-3xl font-bold mb-6">Delete SI Unit</h1>
                    <p class="mb-4">This endpoint deletes an SI unit by ID.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">POST</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProductPackages.php?action=deleteSIUnit</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the SI unit to delete.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Sample Request</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"id"</span>: <span class="string">"01h2c5n3g9s4f6g7h8j9k0l1m8"</span>
}</code></pre>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"message"</span>: <span class="string">"SI unit deleted successfully"</span>
}</code></pre>
                    </div>
                </div>
            </section>

            <!-- Products Section -->
            <section id="products-section" class="section">
                <!-- Get Products -->
                <div id="products-get" class="step">
                    <h1 class="text-3xl font-bold mb-6">Get All Products</h1>
                    <p class="mb-4">This endpoint retrieves all products.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">GET</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProducts.php?action=getProducts</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <p class="mb-4">Returns an array of product objects with their details, including category, images, and units of measure.</p>

                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"products"</span>: [
        {
            <span class="key">"uuid_id"</span>: <span class="string">"01h2c5n3h9s4f6g7h8j9k0l1m9"</span>,
            <span class="key">"title"</span>: <span class="string">"Smartphone X"</span>,
            <span class="key">"uuid_category"</span>: <span class="string">"01h2c5n3a9s4f6g7h8j9k0l1m2"</span>,
            <span class="key">"description"</span>: <span class="string">"A high-end smartphone with advanced features."</span>,
            <span class="key">"meta_title"</span>: <span class="string">"Smartphone X - Latest Tech"</span>,
            <span class="key">"meta_description"</span>: <span class="string">"Discover the latest Smartphone X with cutting-edge technology."</span>,
            <span class="key">"meta_keywords"</span>: <span class="string">"smartphone, technology, mobile"</span>,
            <span class="key">"views"</span>: <span class="number">0</span>,
            <span class="key">"status"</span>: <span class="string">"published"</span>,
            <span class="key">"featured"</span>: <span class="number">1</span>,
            <span class="key">"created_at"</span>: <span class="string">"2023-06-01 11:00:00"</span>,
            <span class="key">"updated_at"</span>: <span class="string">"2023-06-01 11:00:00"</span>,
            <span class="key">"category_name"</span>: <span class="string">"Electronics"</span>,
            <span class="key">"images"</span>: [
                <span class="string">"https://your-domain.com/img/products/01h2c5n3h9s4f6g7h8j9k0l1m9/prod_12345.jpg"</span>
            ],
            <span class="key">"units_of_measure"</span>: [
                {
                    <span class="key">"id"</span>: <span class="string">"01h2c5n3i9s4f6g7h8j9k0l1n0"</span>,
                    <span class="key">"unit_of_measure_id"</span>: <span class="string">"01h2c5n3e9s4f6g7h8j9k0l1m6"</span>,
                    <span class="key">"price"</span>: <span class="string">"999.99"</span>,
                    <span class="key">"si_unit"</span>: <span class="string">"pc"</span>,
                    <span class="key">"package_name"</span>: <span class="string">"Piece"</span>,
                    <span class="key">"unit_of_measure"</span>: <span class="string">"Piece (pc)"</span>
                }
            ]
        }
    ]
}</code></pre>
                    </div>
                </div>

                <!-- Get Product -->
                <div id="products-get-single" class="step">
                    <h1 class="text-3xl font-bold mb-6">Get Product</h1>
                    <p class="mb-4">This endpoint retrieves a specific product by ID.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">GET</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProducts.php?action=getProduct&id={product_id}</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the product.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <p class="mb-4">Returns the requested product object with its details.</p>

                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"data"</span>: {
        <span class="key">"uuid_id"</span>: <span class="string">"01h2c5n3h9s4f6g7h8j9k0l1m9"</span>,
        <span class="key">"title"</span>: <span class="string">"Smartphone X"</span>,
        <span class="key">"uuid_category"</span>: <span class="string">"01h2c5n3a9s4f6g7h8j9k0l1m2"</span>,
        <span class="key">"description"</span>: <span class="string">"A high-end smartphone with advanced features."</span>,
        <span class="key">"meta_title"</span>: <span class="string">"Smartphone X - Latest Tech"</span>,
        <span class="key">"meta_description"</span>: <span class="string">"Discover the latest Smartphone X with cutting-edge technology."</span>,
        <span class="key">"meta_keywords"</span>: <span class="string">"smartphone, technology, mobile"</span>,
        <span class="key">"views"</span>: <span class="number">0</span>,
        <span class="key">"status"</span>: <span class="string">"published"</span>,
        <span class="key">"featured"</span>: <span class="number">1</span>,
        <span class="key">"created_at"</span>: <span class="string">"2023-06-01 11:00:00"</span>,
        <span class="key">"updated_at"</span>: <span class="string">"2023-06-01 11:00:00"</span>,
        <span class="key">"category_name"</span>: <span class="string">"Electronics"</span>,
        <span class="key">"images"</span>: [
            <span class="string">"https://your-domain.com/img/products/01h2c5n3h9s4f6g7h8j9k0l1m9/prod_12345.jpg"</span>
        ],
        <span class="key">"units_of_measure"</span>: [
            {
                <span class="key">"id"</span>: <span class="string">"01h2c5n3i9s4f6g7h8j9k0l1n0"</span>,
                <span class="key">"unit_of_measure_id"</span>: <span class="string">"01h2c5n3e9s4f6g7h8j9k0l1m6"</span>,
                <span class="key">"price"</span>: <span class="string">"999.99"</span>,
                <span class="key">"si_unit"</span>: <span class="string">"pc"</span>,
                <span class="key">"package_name"</span>: <span class="string">"Piece"</span>,
                <span class="key">"unit_of_measure"</span>: <span class="string">"Piece (pc)"</span>
            }
        ]
    }
}</code></pre>
                    </div>
                </div>

                <!-- Create Product -->
                <div id="products-create" class="step">
                    <h1 class="text-3xl font-bold mb-6">Create Product</h1>
                    <p class="mb-4">This endpoint creates a new product.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">POST</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProducts.php?action=createProduct</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>title</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The title of the product.</td>
                                </tr>
                                <tr>
                                    <td>category_id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the product category.</td>
                                </tr>
                                <tr>
                                    <td>description</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>A description of the product.</td>
                                </tr>
                                <tr>
                                    <td>meta_title</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The meta title for SEO.</td>
                                </tr>
                                <tr>
                                    <td>meta_description</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The meta description for SEO.</td>
                                </tr>
                                <tr>
                                    <td>meta_keywords</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The meta keywords for SEO.</td>
                                </tr>
                                <tr>
                                    <td>status</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The status of the product. Values: "published", "pending", or "draft". Default: "published".</td>
                                </tr>
                                <tr>
                                    <td>featured</td>
                                    <td>boolean</td>
                                    <td>Optional</td>
                                    <td>Whether the product is featured. Default: false.</td>
                                </tr>
                                <tr>
                                    <td>units_of_measure</td>
                                    <td>array</td>
                                    <td>Optional</td>
                                    <td>An array of objects containing unit_of_measure_id (string) and price (number).</td>
                                </tr>
                                <tr>
                                    <td>temp_images</td>
                                    <td>array</td>
                                    <td>Optional</td>
                                    <td>An array of objects containing temp_path (string) for temporarily uploaded images.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Sample Request</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"title"</span>: <span class="string">"Smartphone Y"</span>,
    <span class="key">"category_id"</span>: <span class="string">"01h2c5n3a9s4f6g7h8j9k0l1m2"</span>,
    <span class="key">"description"</span>: <span class="string">"A mid-range smartphone with great features."</span>,
    <span class="key">"meta_title"</span>: <span class="string">"Smartphone Y - Affordable Tech"</span>,
    <span class="key">"meta_description"</span>: <span class="string">"Discover the affordable Smartphone Y with great features."</span>,
    <span class="key">"meta_keywords"</span>: <span class="string">"smartphone, affordable, mobile"</span>,
    <span class="key">"status"</span>: <span class="string">"published"</span>,
    <span class="key">"featured"</span>: <span class="boolean">true</span>,
    <span class="key">"units_of_measure"</span>: [
        {
            <span class="key">"unit_of_measure_id"</span>: <span class="string">"01h2c5n3e9s4f6g7h8j9k0l1m6"</span>,
            <span class="key">"price"</span>: <span class="number">599.99</span>
        }
    ],
    <span class="key">"temp_images"</span>: [
        {
            <span class="key">"temp_path"</span>: <span class="string">"uploads/temp/temp_67890.jpg"</span>
        }
    ]
}</code></pre>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <p class="mb-4">Returns the ID of the newly created product.</p>

                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"message"</span>: <span class="string">"Product created successfully"</span>,
    <span class="key">"id"</span>: <span class="string">"01h2c5n3j9s4f6g7h8j9k0l1n1"</span>
}</code></pre>
                    </div>
                </div>

                <!-- Update Product -->
                <div id="products-update" class="step">
                    <h1 class="text-3xl font-bold mb-6">Update Product</h1>
                    <p class="mb-4">This endpoint updates an existing product.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">POST</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProducts.php?action=updateProduct</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the product.</td>
                                </tr>
                                <tr>
                                    <td>title</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The updated title of the product.</td>
                                </tr>
                                <tr>
                                    <td>category_id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the product category.</td>
                                </tr>
                                <tr>
                                    <td>description</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The updated description of the product.</td>
                                </tr>
                                <tr>
                                    <td>meta_title</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The updated meta title for SEO.</td>
                                </tr>
                                <tr>
                                    <td>meta_description</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The updated meta description for SEO.</td>
                                </tr>
                                <tr>
                                    <td>meta_keywords</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The updated meta keywords for SEO.</td>
                                </tr>
                                <tr>
                                    <td>status</td>
                                    <td>string</td>
                                    <td>Optional</td>
                                    <td>The updated status of the product. Values: "published", "pending", or "draft".</td>
                                </tr>
                                <tr>
                                    <td>featured</td>
                                    <td>boolean</td>
                                    <td>Optional</td>
                                    <td>Whether the product is featured.</td>
                                </tr>
                                <tr>
                                    <td>units_of_measure</td>
                                    <td>array</td>
                                    <td>Optional</td>
                                    <td>An array of objects containing unit_of_measure_id (string) and price (number).</td>
                                </tr>
                                <tr>
                                    <td>update_images</td>
                                    <td>boolean</td>
                                    <td>Optional</td>
                                    <td>Whether to update the product images. Default: false.</td>
                                </tr>
                                <tr>
                                    <td>existing_images</td>
                                    <td>array</td>
                                    <td>Optional</td>
                                    <td>An array of strings containing existing image URLs to keep.</td>
                                </tr>
                                <tr>
                                    <td>temp_images</td>
                                    <td>array</td>
                                    <td>Optional</td>
                                    <td>An array of objects containing temp_path (string) for temporarily uploaded images.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Sample Request</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"id"</span>: <span class="string">"01h2c5n3j9s4f6g7h8j9k0l1n1"</span>,
    <span class="key">"title"</span>: <span class="string">"Smartphone Y Pro"</span>,
    <span class="key">"category_id"</span>: <span class="string">"01h2c5n3a9s4f6g7h8j9k0l1m2"</span>,
    <span class="key">"description"</span>: <span class="string">"A updated mid-range smartphone with enhanced features."</span>,
    <span class="key">"meta_title"</span>: <span class="string">"Smartphone Y Pro - Enhanced Tech"</span>,
    <span class="key">"meta_description"</span>: <span class="string">"Discover the enhanced Smartphone Y Pro with great features."</span>,
    <span class="key">"meta_keywords"</span>: <span class="string">"smartphone, pro, mobile"</span>,
    <span class="key">"status"</span>: <span class="string">"published"</span>,
    <span class="key">"featured"</span>: <span class="boolean">true</span>,
    <span class="key">"units_of_measure"</span>: [
        {
            <span class="key">"unit_of_measure_id"</span>: <span class="string">"01h2c5n3e9s4f6g7h8j9k0l1m6"</span>,
            <span class="key">"price"</span>: <span class="number">699.99</span>
        }
    ],
    <span class="key">"update_images"</span>: <span class="boolean">true</span>,
    <span class="key">"existing_images"</span>: [
        <span class="string">"prod_12345.jpg"</span>
    ],
    <span class="key">"temp_images"</span>: [
        {
            <span class="key">"temp_path"</span>: <span class="string">"uploads/temp/temp_54321.jpg"</span>
        }
    ]
}</code></pre>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"message"</span>: <span class="string">"Product updated successfully"</span>
}</code></pre>
                    </div>
                </div>

                <!-- Delete Product -->
                <div id="products-delete" class="step">
                    <h1 class="text-3xl font-bold mb-6">Delete Product</h1>
                    <p class="mb-4">This endpoint deletes a product by ID.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">POST</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProducts.php?action=deleteProduct</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the product to delete.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Sample Request</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"id"</span>: <span class="string">"01h2c5n3j9s4f6g7h8j9k0l1n1"</span>
}</code></pre>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"message"</span>: <span class="string">"Product deleted successfully"</span>
}</code></pre>
                    </div>
                </div>

                <!-- Toggle Featured -->
                <div id="products-toggle-featured" class="step">
                    <h1 class="text-3xl font-bold mb-6">Toggle Featured</h1>
                    <p class="mb-4">This endpoint toggles the featured status of a product.</p>

                    <h2 class="text-xl font-semibold mt-6 mb-3">HTTP Request</h2>
                    <p class="font-semibold text-indigo-600 mb-2">POST</p>
                    <div class="code-block p-4 mb-6">
                        <code>/admin/fetch/manageProducts.php?action=toggleFeatured</code>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Arguments</h2>
                    <div class="table-container mb-6">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>id</td>
                                    <td>string</td>
                                    <td>Required</td>
                                    <td>The unique identifier (UUID) of the product.</td>
                                </tr>
                                <tr>
                                    <td>featured</td>
                                    <td>boolean</td>
                                    <td>Required</td>
                                    <td>The new featured status of the product. true to feature, false to unfeature.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Sample Request</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"id"</span>: <span class="string">"01h2c5n3h9s4f6g7h8j9k0l1m9"</span>,
    <span class="key">"featured"</span>: <span class="boolean">false</span>
}</code></pre>
                    </div>

                    <h2 class="text-xl font-semibold mt-6 mb-3">Response</h2>
                    <div class="code-block p-4 mb-6">
                        <pre class="json"><code>{
    <span class="key">"success"</span>: <span class="boolean">true</span>,
    <span class="key">"message"</span>: <span class="string">"Product removed from featured"</span>
}</code></pre>
                    </div>
                </div>
            </section>
        </div>

        <!-- Step Navigation -->
        <div class="step-navigation p-4 flex justify-between fixed bottom-0 left-0 md:left-64 right-0 bg-white shadow-lg">
            <button id="prev-btn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Previous</button>
            <div id="step-indicator" class="text-center py-2"></div>
            <button id="next-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Next</button>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize variables
            let currentSection = 'introduction-section';
            let currentStep = 'introduction';
            const sections = {
                'introduction-section': ['introduction'],
                'categories-section': ['categories-get', 'categories-get-single', 'categories-create', 'categories-update', 'categories-delete'],
                'packages-section': ['packages-get-names', 'packages-get-name', 'packages-create-name', 'packages-update-name', 'packages-delete-name', 'packages-get-units', 'packages-get-unit', 'packages-create-unit', 'packages-update-unit', 'packages-delete-unit'],
                'products-section': ['products-get', 'products-get-single', 'products-create', 'products-update', 'products-delete', 'products-toggle-featured']
            };

            // All steps in order
            const allSteps = [].concat(...Object.values(sections));

            // Initialize the UI
            updateStepIndicator();

            // Mobile menu toggle
            $('.mobile-nav-toggle').click(function() {
                $('.sidebar').toggleClass('active');
                $('.overlay').toggleClass('active');
            });

            $('.overlay').click(function() {
                $('.sidebar').removeClass('active');
                $('.overlay').removeClass('active');
            });

            // Navigate to step when clicking on a nav link
            $('.nav-link').click(function(e) {
                e.preventDefault();
                const targetId = $(this).attr('href').substring(1);
                navigateToStep(targetId);

                // Close mobile menu after navigation
                $('.sidebar').removeClass('active');
                $('.overlay').removeClass('active');
            });

            // Next button action
            $('#next-btn').click(function() {
                const currentIndex = allSteps.indexOf(currentStep);
                if (currentIndex < allSteps.length - 1) {
                    navigateToStep(allSteps[currentIndex + 1]);
                }
            });

            // Previous button action
            $('#prev-btn').click(function() {
                const currentIndex = allSteps.indexOf(currentStep);
                if (currentIndex > 0) {
                    navigateToStep(allSteps[currentIndex - 1]);
                }
            });

            // Function to navigate to a specific step
            function navigateToStep(stepId) {
                // Find which section contains this step
                let targetSection = '';
                for (const [section, steps] of Object.entries(sections)) {
                    if (steps.includes(stepId)) {
                        targetSection = section;
                        break;
                    }
                }

                if (!targetSection) return;

                // Update active section if needed
                if (targetSection !== currentSection) {
                    $('.section').removeClass('active');
                    $(`#${targetSection}`).addClass('active');
                    currentSection = targetSection;
                }

                // Update active step
                $('.step').removeClass('active');
                $(`#${stepId}`).addClass('active');
                currentStep = stepId;

                // Update navigation buttons and step indicator
                updateStepIndicator();

                // Update active navigation link
                $('.nav-link').parent().removeClass('active');
                $(`.nav-link[href="#${stepId}"]`).parent().addClass('active');

                // Scroll to top
                window.scrollTo(0, 0);
            }

            // Update step indicator and navigation buttons
            function updateStepIndicator() {
                const currentIndex = allSteps.indexOf(currentStep);
                const totalSteps = allSteps.length;

                // Update step indicator
                $('#step-indicator').text(`Step ${currentIndex + 1} of ${totalSteps}`);

                // Update button states
                $('#prev-btn').prop('disabled', currentIndex === 0).css('opacity', currentIndex === 0 ? 0.5 : 1);
                $('#next-btn').prop('disabled', currentIndex === totalSteps - 1).css('opacity', currentIndex === totalSteps - 1 ? 0.5 : 1);
            }

            // Handle keyboard navigation
            $(document).keydown(function(e) {
                // Right arrow key for next, left arrow key for previous
                if (e.keyCode === 39) { // Right arrow
                    $('#next-btn').click();
                } else if (e.keyCode === 37) { // Left arrow
                    $('#prev-btn').click();
                }
            });

            // Format JSON code blocks
            $('.json').each(function() {
                // The syntax highlighting is already applied in the HTML
            });
        });
    </script>
</body>

</html>