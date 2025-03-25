<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products API Documentation</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'rubik': ['Rubik', 'sans-serif'],
                    },
                    colors: {
                        'primary': '#2563eb',
                        'primary-dark': '#1d4ed8',
                        'secondary': '#334155',
                        'accent': '#f59e0b',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Rubik', sans-serif;
        }

        .sidebar {
            transition: transform 0.3s ease;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }
        }

        .code-block {
            background-color: #1e293b;
            color: #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
            overflow-x: auto;
        }

        .json-key {
            color: #38bdf8;
        }

        .json-string {
            color: #4ade80;
        }

        .json-number {
            color: #fb923c;
        }

        .json-boolean {
            color: #f472b6;
        }

        .json-null {
            color: #94a3b8;
        }

        .step-content {
            display: none;
        }

        .step-content.active {
            display: block;
        }

        .nav-link {
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            background-color: rgba(37, 99, 235, 0.1);
        }

        .nav-link.active {
            background-color: rgba(37, 99, 235, 0.1);
            border-left: 4px solid #2563eb;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            padding: 0.75rem;
            text-align: left;
        }

        th {
            background-color: #f8fafc;
            font-weight: 500;
        }

        tr {
            border-bottom: 1px solid #e2e8f0;
        }

        tr:last-child {
            border-bottom: none;
        }

        .table-container {
            overflow-x: auto;
            margin-bottom: 1.5rem;
        }

        /* HTTP Method Badges */
        .http-method {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .http-method.get {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .http-method.post {
            background-color: #dcfce7;
            color: #166534;
        }

        .http-method.put {
            background-color: #fef3c7;
            color: #92400e;
        }

        .http-method.delete {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        /* Mobile menu button */
        .menu-button {
            display: none;
        }

        @media (max-width: 768px) {
            .menu-button {
                display: block;
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 50;
                background-color: #2563eb;
                color: white;
                border-radius: 0.5rem;
                padding: 0.5rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }

            .content-wrapper {
                padding-top: 4rem;
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">
    <!-- Mobile Menu Button -->
    <button class="menu-button" id="menuToggle">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="sidebar fixed md:static w-64 bg-white shadow-lg h-screen overflow-y-auto z-40" id="sidebar">
            <div class="p-4 border-b border-gray-200">
                <h1 class="text-xl font-bold text-primary">Products API</h1>
                <p class="text-sm text-gray-600 mt-1">Documentation</p>
            </div>
            <nav class="p-4">
                <ul>
                    <li class="mb-1">
                        <a href="#introduction" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Introduction</a>
                    </li>
                    <li class="mb-4">
                        <div class="px-4 py-2 text-sm font-medium text-gray-500 uppercase">Categories</div>
                        <ul class="ml-2">
                            <li>
                                <a href="#categories-overview" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Overview</a>
                            </li>
                            <li>
                                <a href="#get-categories" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Get Categories</a>
                            </li>
                            <li>
                                <a href="#get-category" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Get Category</a>
                            </li>
                            <li>
                                <a href="#create-category" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Create Category</a>
                            </li>
                            <li>
                                <a href="#update-category" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Update Category</a>
                            </li>
                            <li>
                                <a href="#delete-category" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Delete Category</a>
                            </li>
                        </ul>
                    </li>
                    <li class="mb-4">
                        <div class="px-4 py-2 text-sm font-medium text-gray-500 uppercase">Package Definitions</div>
                        <ul class="ml-2">
                            <li>
                                <a href="#package-overview" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Overview</a>
                            </li>
                            <li>
                                <a href="#get-package-names" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Get Package Names</a>
                            </li>
                            <li>
                                <a href="#create-package-name" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Create Package Name</a>
                            </li>
                            <li>
                                <a href="#get-si-units" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Get SI Units</a>
                            </li>
                            <li>
                                <a href="#create-si-unit" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Create SI Unit</a>
                            </li>
                        </ul>
                    </li>
                    <li class="mb-4">
                        <div class="px-4 py-2 text-sm font-medium text-gray-500 uppercase">Products</div>
                        <ul class="ml-2">
                            <li>
                                <a href="#products-overview" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Overview</a>
                            </li>
                            <li>
                                <a href="#get-products" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Get Products</a>
                            </li>
                            <li>
                                <a href="#get-product" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Get Product</a>
                            </li>
                            <li>
                                <a href="#create-product" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Create Product</a>
                            </li>
                            <li>
                                <a href="#update-product" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Update Product</a>
                            </li>
                            <li>
                                <a href="#delete-product" class="nav-link block px-4 py-2 rounded-md text-gray-700 hover:text-primary">Delete Product</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 content-wrapper max-h-screen overflow-auto">
            <div class="max-w-4xl mx-auto px-4 py-8 md:px-8">
                <!-- Step Navigation -->
                <div class="flex justify-between mb-8">
                    <button id="prevBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                    <div id="stepIndicator" class="text-sm text-gray-600">Step <span id="currentStep">1</span> of <span id="totalSteps">15</span></div>
                    <button id="nextBtn" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                </div>

                <!-- Step Content -->
                <div id="stepContent">
                    <!-- Introduction -->
                    <div class="step-content active" id="introduction">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Products API Documentation</h2>
                        <p class="mb-4">This documentation provides details on how to interact with the Products API. The API allows you to manage product categories, package definitions, and products.</p>
                        <p class="mb-4">The API is organized around RESTful principles. It accepts JSON-encoded request bodies, returns JSON-encoded responses, and uses standard HTTP response codes to indicate the success or failure of API requests.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Base URL</h3>
                        <div class="code-block mb-6">
                            <code>https://your-domain.com/admin/fetch/</code>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Authentication</h3>
                        <p class="mb-4">All API requests require authentication through session-based authentication. You must be logged in as an admin user to access these endpoints.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Error Handling</h3>
                        <p class="mb-4">The API uses conventional HTTP response codes to indicate the success or failure of an API request:</p>
                        <ul class="list-disc ml-6 mb-6">
                            <li class="mb-2"><strong>200 OK</strong> - The request was successful.</li>
                            <li class="mb-2"><strong>400 Bad Request</strong> - The request was invalid or cannot be served.</li>
                            <li class="mb-2"><strong>401 Unauthorized</strong> - Authentication failed or user doesn't have permissions.</li>
                            <li class="mb-2"><strong>404 Not Found</strong> - The requested resource doesn't exist.</li>
                            <li class="mb-2"><strong>409 Conflict</strong> - The request conflicts with the current state of the server.</li>
                            <li class="mb-2"><strong>500 Internal Server Error</strong> - Something went wrong on the server.</li>
                        </ul>

                        <p class="mb-4">All error responses include a JSON object with a <code>success</code> field set to <code>false</code> and a <code>message</code> field providing more details about the error.</p>
                    </div>

                    <!-- Categories Overview -->
                    <div class="step-content" id="categories-overview">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Categories</h2>
                        <p class="mb-4">Categories are used to organize products into logical groups. Examples include "Building Materials", "Tools & Equipment", "Safety Gear", etc.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Category Object</h3>
                        <p class="mb-4">A category object has the following properties:</p>

                        <div class="table-container">
                            <table class="w-full">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>uuid_id</td>
                                        <td>string</td>
                                        <td>Unique identifier for the category</td>
                                    </tr>
                                    <tr>
                                        <td>name</td>
                                        <td>string</td>
                                        <td>Name of the category</td>
                                    </tr>
                                    <tr>
                                        <td>description</td>
                                        <td>string</td>
                                        <td>Description of the category</td>
                                    </tr>
                                    <tr>
                                        <td>meta_title</td>
                                        <td>string</td>
                                        <td>SEO meta title</td>
                                    </tr>
                                    <tr>
                                        <td>meta_description</td>
                                        <td>string</td>
                                        <td>SEO meta description</td>
                                    </tr>
                                    <tr>
                                        <td>meta_keywords</td>
                                        <td>string</td>
                                        <td>SEO meta keywords (comma-separated)</td>
                                    </tr>
                                    <tr>
                                        <td>status</td>
                                        <td>string</td>
                                        <td>Status of the category (active or inactive)</td>
                                    </tr>
                                    <tr>
                                        <td>image_url</td>
                                        <td>string</td>
                                        <td>URL to the category image (if available)</td>
                                    </tr>
                                    <tr>
                                        <td>created_at</td>
                                        <td>string</td>
                                        <td>Creation timestamp</td>
                                    </tr>
                                    <tr>
                                        <td>updated_at</td>
                                        <td>string</td>
                                        <td>Last update timestamp</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Available Endpoints</h3>
                        <ul class="list-disc ml-6 mb-6">
                            <li class="mb-2"><a href="#get-categories" class="text-primary hover:underline">Get all categories</a></li>
                            <li class="mb-2"><a href="#get-category" class="text-primary hover:underline">Get a specific category</a></li>
                            <li class="mb-2"><a href="#create-category" class="text-primary hover:underline">Create a new category</a></li>
                            <li class="mb-2"><a href="#update-category" class="text-primary hover:underline">Update an existing category</a></li>
                            <li class="mb-2"><a href="#delete-category" class="text-primary hover:underline">Delete a category</a></li>
                        </ul>
                    </div>

                    <!-- Get Categories -->
                    <div class="step-content" id="get-categories">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Get All Categories</h2>
                        <p class="mb-4">This endpoint retrieves all product categories.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">HTTP Request</h3>
                        <div class="mb-2">
                            <span class="http-method get">GET</span>
                        </div>
                        <div class="code-block mb-6">
                            <code>https://your-domain.com/admin/fetch/manageProductCategories/getCategories</code>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Response</h3>
                        <p class="mb-4">Returns a JSON object containing an array of category objects.</p>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Sample Response</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"categories"</span>: [
        <span class="json-key">{
            "uuid_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e2"</span>,
            <span class="json-key">"name"</span>: <span class="json-string">"Building Materials"</span>,
            <span class="json-key">"description"</span>: <span class="json-string">"Essential materials for building projects"</span>,
            <span class="json-key">"meta_title"</span>: <span class="json-string">"Building Materials | Supplies"</span>,
            <span class="json-key">"meta_description"</span>: <span class="json-string">"High-quality building materials for projects"</span>,
            <span class="json-key">"meta_keywords"</span>: <span class="json-string">"cement, bricks, sand, aggregates, materials"</span>,
            <span class="json-key">"status"</span>: <span class="json-string">"active"</span>,
            <span class="json-key">"image_url"</span>: <span class="json-string">"https://your-domain.com/img/product-categories/018d3b4c-5f3e-7c10-b96e-f7e423d7f0e2/building-materials.jpg"</span>,
            <span class="json-key">"created_at"</span>: <span class="json-string">"2023-06-15 10:30:45"</span>,
            <span class="json-key">"updated_at"</span>: <span class="json-string">"2023-06-15 10:30:45"</span>
        }</span>,
        <span class="json-key">{
            "uuid_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e3"</span>,
            <span class="json-key">"name"</span>: <span class="json-string">"Tools & Equipment"</span>,
            <span class="json-key">"description"</span>: <span class="json-string">"Professional tools and equipment for construction"</span>,
            <span class="json-key">"meta_title"</span>: <span class="json-string">"Tools & Equipment"</span>,
            <span class="json-key">"meta_description"</span>: <span class="json-string">"Professional tools and equipment for builders"</span>,
            <span class="json-key">"meta_keywords"</span>: <span class="json-string">"tools, power tools, hand tools, equipment"</span>,
            <span class="json-key">"status"</span>: <span class="json-string">"active"</span>,
            <span class="json-key">"image_url"</span>: <span class="json-string">"https://your-domain.com/img/product-categories/018d3b4c-5f3e-7c10-b96e-f7e423d7f0e3/tools-equipment.jpg"</span>,
            <span class="json-key">"created_at"</span>: <span class="json-string">"2023-06-15 11:15:22"</span>,
            <span class="json-key">"updated_at"</span>: <span class="json-string">"2023-06-15 11:15:22"</span>
        }</span>
    ]
}</pre>
                        </div>
                    </div>

                    <!-- Get Category -->
                    <div class="step-content" id="get-category">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Get a Specific Category</h2>
                        <p class="mb-4">This endpoint retrieves a specific product category by its ID.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">HTTP Request</h3>
                        <div class="mb-2">
                            <span class="http-method get">GET</span>
                        </div>
                        <div class="code-block mb-6">
                            <code>https://your-domain.com/admin/fetch/manageProductCategories/getCategory?id={category_id}</code>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Arguments</h3>
                        <div class="table-container">
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
                                        <td>The UUID of the category to retrieve</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Response</h3>
                        <p class="mb-4">Returns a JSON object containing the requested category.</p>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Sample Response</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"data"</span>: <span class="json-key">{
        "uuid_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e2"</span>,
        <span class="json-key">"name"</span>: <span class="json-string">"Building Materials"</span>,
        <span class="json-key">"description"</span>: <span class="json-string">"Essential materials for building projects"</span>,
        <span class="json-key">"meta_title"</span>: <span class="json-string">"Building Materials | Supplies"</span>,
        <span class="json-key">"meta_description"</span>: <span class="json-string">"High-quality building materials for projects"</span>,
        <span class="json-key">"meta_keywords"</span>: <span class="json-string">"cement, bricks, sand, aggregates, materials"</span>,
        <span class="json-key">"status"</span>: <span class="json-string">"active"</span>,
        <span class="json-key">"image_url"</span>: <span class="json-string">"https://your-domain.com/img/product-categories/018d3b4c-5f3e-7c10-b96e-f7e423d7f0e2/building-materials.jpg"</span>,
        <span class="json-key">"has_image"</span>: <span class="json-boolean">true</span>,
        <span class="json-key">"created_at"</span>: <span class="json-string">"2023-06-15 10:30:45"</span>,
        <span class="json-key">"updated_at"</span>: <span class="json-string">"2023-06-15 10:30:45"</span>
    }</span>
}</pre>
                        </div>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Error Response (Category Not Found)</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">false</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"Category not found"</span>
}</pre>
                        </div>
                    </div>

                    <!-- Create Category -->
                    <div class="step-content" id="create-category">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Create a Category</h2>
                        <p class="mb-4">This endpoint creates a new product category.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">HTTP Request</h3>
                        <div class="mb-2">
                            <span class="http-method post">POST</span>
                        </div>
                        <div class="code-block mb-6">
                            <code>https://your-domain.com/admin/fetch/manageProductCategories/createCategory</code>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Arguments</h3>
                        <div class="table-container">
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
                                        <td>Name of the category</td>
                                    </tr>
                                    <tr>
                                        <td>description</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>Description of the category</td>
                                    </tr>
                                    <tr>
                                        <td>meta_title</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>SEO meta title</td>
                                    </tr>
                                    <tr>
                                        <td>meta_description</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>SEO meta description</td>
                                    </tr>
                                    <tr>
                                        <td>meta_keywords</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>SEO meta keywords (comma-separated)</td>
                                    </tr>
                                    <tr>
                                        <td>status</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>Status of the category (active or inactive). Defaults to active.</td>
                                    </tr>
                                    <tr>
                                        <td>temp_image_path</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>Path to a temporary uploaded image</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Sample Request</h3>
                        <div class="code-block mb-6">
                            <pre>curl -X POST "https://your-domain.com/admin/fetch/manageProductCategories/createCategory" \
-H "Content-Type: application/json" \
-d '{
    <span class="json-key">"name"</span>: <span class="json-string">"Safety Gear"</span>,
    <span class="json-key">"description"</span>: <span class="json-string">"Safety equipment and protective gear for workers"</span>,
    <span class="json-key">"meta_title"</span>: <span class="json-string">"Safety Gear & Equipment"</span>,
    <span class="json-key">"meta_description"</span>: <span class="json-string">"High-quality safety gear and protective equipment for sites"</span>,
    <span class="json-key">"meta_keywords"</span>: <span class="json-string">"safety helmets, safety boots, high-vis vests, protective gear"</span>,
    <span class="json-key">"status"</span>: <span class="json-string">"active"</span>
}'</pre>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Response</h3>
                        <p class="mb-4">Returns a JSON object containing the success status, message, and the ID of the newly created category.</p>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Sample Response</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"Category created successfully"</span>,
    <span class="json-key">"id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e4"</span>
}</pre>
                        </div>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Error Response (Duplicate Category)</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">false</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"A category with this name already exists"</span>
}</pre>
                        </div>
                    </div>

                    <!-- Update Category -->
                    <div class="step-content" id="update-category">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Update a Category</h2>
                        <p class="mb-4">This endpoint updates an existing product category.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">HTTP Request</h3>
                        <div class="mb-2">
                            <span class="http-method post">POST</span>
                        </div>
                        <div class="code-block mb-6">
                            <code>https://your-domain.com/admin/fetch/manageProductCategories/updateCategory</code>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Arguments</h3>
                        <div class="table-container">
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
                                        <td>The UUID of the category to update</td>
                                    </tr>
                                    <tr>
                                        <td>name</td>
                                        <td>string</td>
                                        <td>Required</td>
                                        <td>Name of the category</td>
                                    </tr>
                                    <tr>
                                        <td>description</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>Description of the category</td>
                                    </tr>
                                    <tr>
                                        <td>meta_title</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>SEO meta title</td>
                                    </tr>
                                    <tr>
                                        <td>meta_description</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>SEO meta description</td>
                                    </tr>
                                    <tr>
                                        <td>meta_keywords</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>SEO meta keywords (comma-separated)</td>
                                    </tr>
                                    <tr>
                                        <td>status</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>Status of the category (active or inactive)</td>
                                    </tr>
                                    <tr>
                                        <td>temp_image_path</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>Path to a temporary uploaded image</td>
                                    </tr>
                                    <tr>
                                        <td>remove_image</td>
                                        <td>boolean</td>
                                        <td>Optional</td>
                                        <td>Whether to remove the existing image (true or false)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Sample Request</h3>
                        <div class="code-block mb-6">
                            <pre>curl -X POST "https://your-domain.com/admin/fetch/manageProductCategories/updateCategory" \
-H "Content-Type: application/json" \
-d '{
    <span class="json-key">"id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e4"</span>,
    <span class="json-key">"name"</span>: <span class="json-string">"Safety Equipment"</span>,
    <span class="json-key">"description"</span>: <span class="json-string">"Safety equipment and protective gear for workers"</span>,
    <span class="json-key">"meta_title"</span>: <span class="json-string">"Safety Equipment & Gear"</span>,
    <span class="json-key">"meta_description"</span>: <span class="json-string">"High-quality safety equipment and protective gear for sites"</span>,
    <span class="json-key">"meta_keywords"</span>: <span class="json-string">"safety helmets, safety boots, high-vis vests, protective gear"</span>,
    <span class="json-key">"status"</span>: <span class="json-string">"active"</span>
}'</pre>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Response</h3>
                        <p class="mb-4">Returns a JSON object containing the success status and a message.</p>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Sample Response</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"Category updated successfully"</span>
}</pre>
                        </div>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Error Response (Category Not Found)</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">false</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"Category not found"</span>
}</pre>
                        </div>
                    </div>

                    <!-- Delete Category -->
                    <div class="step-content" id="delete-category">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Delete a Category</h2>
                        <p class="mb-4">This endpoint deletes a product category.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">HTTP Request</h3>
                        <div class="mb-2">
                            <span class="http-method post">POST</span>
                        </div>
                        <div class="code-block mb-6">
                            <code>https://your-domain.com/admin/fetch/manageProductCategories/deleteCategory</code>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Arguments</h3>
                        <div class="table-container">
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
                                        <td>The UUID of the category to delete</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Sample Request</h3>
                        <div class="code-block mb-6">
                            <pre>curl -X POST "https://your-domain.com/admin/fetch/manageProductCategories/deleteCategory" \
-H "Content-Type: application/json" \
-d '{
    <span class="json-key">"id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e4"</span>
}'</pre>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Response</h3>
                        <p class="mb-4">Returns a JSON object containing the success status and a message.</p>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Sample Response</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"Category deleted successfully"</span>
}</pre>
                        </div>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Error Response (Category Not Found)</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">false</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"Category not found"</span>
}</pre>
                        </div>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Error Response (Category In Use)</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">false</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"Cannot delete this category because it is being used by one or more products"</span>
}</pre>
                        </div>
                    </div>

                    <!-- Package Definitions Overview -->
                    <div class="step-content" id="package-overview">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Package Definitions</h2>
                        <p class="mb-4">Package definitions define how products are packaged and measured. This includes package names (e.g., Bag, Box, Truck) and SI units (e.g., kg, ton, cubic meter).</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Package Name Object</h3>
                        <p class="mb-4">A package name object has the following properties:</p>

                        <div class="table-container">
                            <table class="w-full">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>uuid_id</td>
                                        <td>string</td>
                                        <td>Unique identifier for the package name</td>
                                    </tr>
                                    <tr>
                                        <td>package_name</td>
                                        <td>string</td>
                                        <td>Name of the package (e.g., Bag, Box, Truck)</td>
                                    </tr>
                                    <tr>
                                        <td>created_at</td>
                                        <td>string</td>
                                        <td>Creation timestamp</td>
                                    </tr>
                                    <tr>
                                        <td>updated_at</td>
                                        <td>string</td>
                                        <td>Last update timestamp</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">SI Unit Object</h3>
                        <p class="mb-4">An SI unit object has the following properties:</p>

                        <div class="table-container">
                            <table class="w-full">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>uuid_id</td>
                                        <td>string</td>
                                        <td>Unique identifier for the SI unit</td>
                                    </tr>
                                    <tr>
                                        <td>package_name_uuid_id</td>
                                        <td>string</td>
                                        <td>UUID of the associated package name</td>
                                    </tr>
                                    <tr>
                                        <td>si_unit</td>
                                        <td>string</td>
                                        <td>SI unit (e.g., kg, ton, cubic meter)</td>
                                    </tr>
                                    <tr>
                                        <td>package_name</td>
                                        <td>string</td>
                                        <td>Name of the associated package</td>
                                    </tr>
                                    <tr>
                                        <td>unit_of_measure</td>
                                        <td>string</td>
                                        <td>Combined unit of measure (e.g., "Bag (50kg)")</td>
                                    </tr>
                                    <tr>
                                        <td>created_at</td>
                                        <td>string</td>
                                        <td>Creation timestamp</td>
                                    </tr>
                                    <tr>
                                        <td>updated_at</td>
                                        <td>string</td>
                                        <td>Last update timestamp</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Available Endpoints</h3>
                        <ul class="list-disc ml-6 mb-6">
                            <li class="mb-2"><a href="#get-package-names" class="text-primary hover:underline">Get all package names</a></li>
                            <li class="mb-2"><a href="#create-package-name" class="text-primary hover:underline">Create a new package name</a></li>
                            <li class="mb-2"><a href="#get-si-units" class="text-primary hover:underline">Get all SI units</a></li>
                            <li class="mb-2"><a href="#create-si-unit" class="text-primary hover:underline">Create a new SI unit</a></li>
                        </ul>
                    </div>

                    <!-- Get Package Names -->
                    <div class="step-content" id="get-package-names">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Get All Package Names</h2>
                        <p class="mb-4">This endpoint retrieves all package names for products.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">HTTP Request</h3>
                        <div class="mb-2">
                            <span class="http-method get">GET</span>
                        </div>
                        <div class="code-block mb-6">
                            <code>https://your-domain.com/admin/fetch/manageProductPackages/getPackageNames</code>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Response</h3>
                        <p class="mb-4">Returns a JSON object containing an array of package name objects.</p>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Sample Response</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"packageNames"</span>: [
        <span class="json-key">{
            "uuid_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e5"</span>,
            <span class="json-key">"package_name"</span>: <span class="json-string">"Bag"</span>,
            <span class="json-key">"created_at"</span>: <span class="json-string">"2023-06-15 10:30:45"</span>,
            <span class="json-key">"updated_at"</span>: <span class="json-string">"2023-06-15 10:30:45"</span>
        }</span>,
        <span class="json-key">{
            "uuid_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e6"</span>,
            <span class="json-key">"package_name"</span>: <span class="json-string">"Truck"</span>,
            <span class="json-key">"created_at"</span>: <span class="json-string">"2023-06-15 11:15:22"</span>,
            <span class="json-key">"updated_at"</span>: <span class="json-string">"2023-06-15 11:15:22"</span>
        }</span>,
        <span class="json-key">{
            "uuid_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e7"</span>,
            <span class="json-key">"package_name"</span>: <span class="json-string">"Pallet"</span>,
            <span class="json-key">"created_at"</span>: <span class="json-string">"2023-06-15 11:30:10"</span>,
            <span class="json-key">"updated_at"</span>: <span class="json-string">"2023-06-15 11:30:10"</span>
        }</span>
    ]
}</pre>
                        </div>
                    </div>

                    <!-- Create Package Name -->
                    <div class="step-content" id="create-package-name">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Create a Package Name</h2>
                        <p class="mb-4">This endpoint creates a new package name for products.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">HTTP Request</h3>
                        <div class="mb-2">
                            <span class="http-method post">POST</span>
                        </div>
                        <div class="code-block mb-6">
                            <code>https://your-domain.com/admin/fetch/manageProductPackages/createPackageName</code>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Arguments</h3>
                        <div class="table-container">
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
                                        <td>Name of the package (e.g., Box, Pallet, Truck)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Sample Request</h3>
                        <div class="code-block mb-6">
                            <pre>curl -X POST "https://your-domain.com/admin/fetch/manageProductPackages/createPackageName" \
-H "Content-Type: application/json" \
-d '{
    <span class="json-key">"package_name"</span>: <span class="json-string">"Container"</span>
}'</pre>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Response</h3>
                        <p class="mb-4">Returns a JSON object containing the success status, message, and the ID of the newly created package name.</p>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Sample Response</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"Package name created successfully"</span>,
    <span class="json-key">"id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e8"</span>
}</pre>
                        </div>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Error Response (Duplicate Package Name)</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">false</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"A package with this name already exists"</span>
}</pre>
                        </div>
                    </div>

                    <!-- Get SI Units -->
                    <div class="step-content" id="get-si-units">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Get All SI Units</h2>
                        <p class="mb-4">This endpoint retrieves all SI units for product packages.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">HTTP Request</h3>
                        <div class="mb-2">
                            <span class="http-method get">GET</span>
                        </div>
                        <div class="code-block mb-6">
                            <code>https://your-domain.com/admin/fetch/manageProductPackages/getSIUnits</code>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Query Parameters</h3>
                        <div class="table-container">
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
                                        <td>Filter SI units by package name ID</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Response</h3>
                        <p class="mb-4">Returns a JSON object containing an array of SI unit objects.</p>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Sample Response</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"siUnits"</span>: [
        <span class="json-key">{
            "uuid_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e9"</span>,
            <span class="json-key">"package_name_uuid_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e5"</span>,
            <span class="json-key">"si_unit"</span>: <span class="json-string">"50kg"</span>,
            <span class="json-key">"package_name"</span>: <span class="json-string">"Bag"</span>,
            <span class="json-key">"unit_of_measure"</span>: <span class="json-string">"Bag (50kg)"</span>,
            <span class="json-key">"created_at"</span>: <span class="json-string">"2023-06-15 10:35:20"</span>,
            <span class="json-key">"updated_at"</span>: <span class="json-string">"2023-06-15 10:35:20"</span>
        }</span>,
        <span class="json-key">{
            "uuid_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0ea"</span>,
            <span class="json-key">"package_name_uuid_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e6"</span>,
            <span class="json-key">"si_unit"</span>: <span class="json-string">"10ton"</span>,
            <span class="json-key">"package_name"</span>: <span class="json-string">"Truck"</span>,
            <span class="json-key">"unit_of_measure"</span>: <span class="json-string">"Truck (10ton)"</span>,
            <span class="json-key">"created_at"</span>: <span class="json-string">"2023-06-15 11:20:15"</span>,
            <span class="json-key">"updated_at"</span>: <span class="json-string">"2023-06-15 11:20:15"</span>
        }</span>,
        <span class="json-key">{
            "uuid_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0eb"</span>,
            <span class="json-key">"package_name_uuid_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e7"</span>,
            <span class="json-key">"si_unit"</span>: <span class="json-string">"1000kg"</span>,
            <span class="json-key">"package_name"</span>: <span class="json-string">"Pallet"</span>,
            <span class="json-key">"unit_of_measure"</span>: <span class="json-string">"Pallet (1000kg)"</span>,
            <span class="json-key">"created_at"</span>: <span class="json-string">"2023-06-15 11:35:30"</span>,
            <span class="json-key">"updated_at"</span>: <span class="json-string">"2023-06-15 11:35:30"</span>
        }</span>
    ]
}</pre>
                        </div>
                    </div>

                    <!-- Create SI Unit -->
                    <div class="step-content" id="create-si-unit">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Create an SI Unit</h2>
                        <p class="mb-4">This endpoint creates a new SI unit for a product package.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">HTTP Request</h3>
                        <div class="mb-2">
                            <span class="http-method post">POST</span>
                        </div>
                        <div class="code-block mb-6">
                            <code>https://your-domain.com/admin/fetch/manageProductPackages/createSIUnit</code>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Arguments</h3>
                        <div class="table-container">
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
                                        <td>UUID of the package name</td>
                                    </tr>
                                    <tr>
                                        <td>si_unit</td>
                                        <td>string</td>
                                        <td>Required</td>
                                        <td>SI unit (e.g., 50kg, 10ton, 1m³)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Sample Request</h3>
                        <div class="code-block mb-6">
                            <pre>curl -X POST "https://your-domain.com/admin/fetch/manageProductPackages/createSIUnit" \
-H "Content-Type: application/json" \
-d '{
    <span class="json-key">"package_name_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e8"</span>,
    <span class="json-key">"si_unit"</span>: <span class="json-string">"20ton"</span>
}'</pre>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Response</h3>
                        <p class="mb-4">Returns a JSON object containing the success status, message, the ID of the newly created SI unit, and the combined unit of measure.</p>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Sample Response</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"SI unit created successfully"</span>,
    <span class="json-key">"id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0ec"</span>,
    <span class="json-key">"unit_of_measure"</span>: <span class="json-string">"Container (20ton)"</span>
}</pre>
                        </div>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Error Response (Duplicate SI Unit)</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">false</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"This SI unit already exists for the selected package name"</span>
}</pre>
                        </div>
                    </div>

                    <!-- Products Overview -->
                    <div class="step-content" id="products-overview">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Products</h2>
                        <p class="mb-4">Products represent materials, tools, and services that can be sold or rented. Each product belongs to a category and can have multiple pricing options based on different units of measure.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Product Object</h3>
                        <p class="mb-4">A product object has the following properties:</p>

                        <div class="table-container">
                            <table class="w-full">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>uuid_id</td>
                                        <td>string</td>
                                        <td>Unique identifier for the product</td>
                                    </tr>
                                    <tr>
                                        <td>title</td>
                                        <td>string</td>
                                        <td>Title of the product</td>
                                    </tr>
                                    <tr>
                                        <td>uuid_category</td>
                                        <td>string</td>
                                        <td>UUID of the product category</td>
                                    </tr>
                                    <tr>
                                        <td>category_name</td>
                                        <td>string</td>
                                        <td>Name of the product category</td>
                                    </tr>
                                    <tr>
                                        <td>description</td>
                                        <td>string</td>
                                        <td>Description of the product</td>
                                    </tr>
                                    <tr>
                                        <td>meta_title</td>
                                        <td>string</td>
                                        <td>SEO meta title</td>
                                    </tr>
                                    <tr>
                                        <td>meta_description</td>
                                        <td>string</td>
                                        <td>SEO meta description</td>
                                    </tr>
                                    <tr>
                                        <td>meta_keywords</td>
                                        <td>string</td>
                                        <td>SEO meta keywords (comma-separated)</td>
                                    </tr>
                                    <tr>
                                        <td>status</td>
                                        <td>string</td>
                                        <td>Status of the product (published, pending, or draft)</td>
                                    </tr>
                                    <tr>
                                        <td>featured</td>
                                        <td>boolean</td>
                                        <td>Whether the product is featured</td>
                                    </tr>
                                    <tr>
                                        <td>images</td>
                                        <td>array</td>
                                        <td>Array of image URLs</td>
                                    </tr>
                                    <tr>
                                        <td>units_of_measure</td>
                                        <td>array</td>
                                        <td>Array of unit of measure objects with pricing</td>
                                    </tr>
                                    <tr>
                                        <td>created_at</td>
                                        <td>string</td>
                                        <td>Creation timestamp</td>
                                    </tr>
                                    <tr>
                                        <td>updated_at</td>
                                        <td>string</td>
                                        <td>Last update timestamp</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Unit of Measure Object</h3>
                        <p class="mb-4">A unit of measure object (within a product) has the following properties:</p>

                        <div class="table-container">
                            <table class="w-full">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>id</td>
                                        <td>string</td>
                                        <td>Unique identifier for the pricing record</td>
                                    </tr>
                                    <tr>
                                        <td>unit_of_measure_id</td>
                                        <td>string</td>
                                        <td>UUID of the unit of measure</td>
                                    </tr>
                                    <tr>
                                        <td>price</td>
                                        <td>number</td>
                                        <td>Price for this unit of measure</td>
                                    </tr>
                                    <tr>
                                        <td>si_unit</td>
                                        <td>string</td>
                                        <td>SI unit (e.g., 50kg, 10ton)</td>
                                    </tr>
                                    <tr>
                                        <td>package_name</td>
                                        <td>string</td>
                                        <td>Name of the package (e.g., Bag, Truck)</td>
                                    </tr>
                                    <tr>
                                        <td>unit_of_measure</td>
                                        <td>string</td>
                                        <td>Combined unit of measure (e.g., "Bag (50kg)")</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Available Endpoints</h3>
                        <ul class="list-disc ml-6 mb-6">
                            <li class="mb-2"><a href="#get-products" class="text-primary hover:underline">Get all products</a></li>
                            <li class="mb-2"><a href="#get-product" class="text-primary hover:underline">Get a specific product</a></li>
                            <li class="mb-2"><a href="#create-product" class="text-primary hover:underline">Create a new product</a></li>
                            <li class="mb-2"><a href="#update-product" class="text-primary hover:underline">Update an existing product</a></li>
                            <li class="mb-2"><a href="#delete-product" class="text-primary hover:underline">Delete a product</a></li>
                        </ul>
                    </div>

                    <!-- Get Products -->
                    <div class="step-content" id="get-products">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Get All Products</h2>
                        <p class="mb-4">This endpoint retrieves all products.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">HTTP Request</h3>
                        <div class="mb-2">
                            <span class="http-method get">GET</span>
                        </div>
                        <div class="code-block mb-6">
                            <code>https://your-domain.com/admin/fetch/manageProducts/getProducts</code>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Response</h3>
                        <p class="mb-4">Returns a JSON object containing an array of product objects.</p>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Sample Response</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"products"</span>: [
        <span class="json-key">{
            "uuid_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0ed"</span>,
            <span class="json-key">"title"</span>: <span class="json-string">"Portland Cement"</span>,
            <span class="json-key">"uuid_category"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e2"</span>,
            <span class="json-key">"description"</span>: <span class="json-string">"High-quality Portland cement for projects"</span>,
            <span class="json-key">"meta_title"</span>: <span class="json-string">"Portland Cement | Materials"</span>,
            <span class="json-key">"meta_description"</span>: <span class="json-string">"Premium Portland cement for all your needs"</span>,
            <span class="json-key">"meta_keywords"</span>: <span class="json-string">"portland cement, cement, building materials"</span>,
            <span class="json-key">"views"</span>: <span class="json-number">0</span>,
            <span class="json-key">"status"</span>: <span class="json-string">"published"</span>,
            <span class="json-key">"featured"</span>: <span class="json-number">1</span>,
            <span class="json-key">"created_at"</span>: <span class="json-string">"2023-06-15 12:30:45"</span>,
            <span class="json-key">"updated_at"</span>: <span class="json-string">"2023-06-15 12:30:45"</span>,
            <span class="json-key">"category_name"</span>: <span class="json-string">"Building Materials"</span>,
            <span class="json-key">"images"</span>: [
                <span class="json-string">"https://your-domain.com/img/products/018d3b4c-5f3e-7c10-b96e-f7e423d7f0ed/prod_1234567.jpg"</span>
            ],
            <span class="json-key">"units_of_measure"</span>: [
                <span class="json-key">{
                    "id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0ee"</span>,
                    <span class="json-key">"unit_of_measure_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e9"</span>,
                    <span class="json-key">"price"</span>: <span class="json-number">35000</span>,
                    <span class="json-key">"si_unit"</span>: <span class="json-string">"50kg"</span>,
                    <span class="json-key">"package_name"</span>: <span class="json-string">"Bag"</span>,
                    <span class="json-key">"unit_of_measure"</span>: <span class="json-string">"Bag (50kg)"</span>
                }</span>
            ]
        }</span>,
        <span class="json-key">{
            "uuid_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0ef"</span>,
            <span class="json-key">"title"</span>: <span class="json-string">"Sand"</span>,
            <span class="json-key">"uuid_category"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e2"</span>,
            <span class="json-key">"description"</span>: <span class="json-string">"Fine sand for concrete mixing and masonry work"</span>,
            <span class="json-key">"meta_title"</span>: <span class="json-string">"Sand | Building Materials"</span>,
            <span class="json-key">"meta_description"</span>: <span class="json-string">"Quality sand for concrete and masonry projects"</span>,
            <span class="json-key">"meta_keywords"</span>: <span class="json-string">"sand, building sand, masonry sand"</span>,
            <span class="json-key">"views"</span>: <span class="json-number">0</span>,
            <span class="json-key">"status"</span>: <span class="json-string">"published"</span>,
            <span class="json-key">"featured"</span>: <span class="json-number">0</span>,
            <span class="json-key">"created_at"</span>: <span class="json-string">"2023-06-15 13:15:22"</span>,
            <span class="json-key">"updated_at"</span>: <span class="json-string">"2023-06-15 13:15:22"</span>,
            <span class="json-key">"category_name"</span>: <span class="json-string">"Building Materials"</span>,
            <span class="json-key">"images"</span>: [
                <span class="json-string">"https://your-domain.com/img/products/018d3b4c-5f3e-7c10-b96e-f7e423d7f0ef/prod_7654321.jpg"</span>
            ],
            <span class="json-key">"units_of_measure"</span>: [
                <span class="json-key">{
                    "id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0f0"</span>,
                    <span class="json-key">"unit_of_measure_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0ea"</span>,
                    <span class="json-key">"price"</span>: <span class="json-number">450000</span>,
                    <span class="json-key">"si_unit"</span>: <span class="json-string">"10ton"</span>,
                    <span class="json-key">"package_name"</span>: <span class="json-string">"Truck"</span>,
                    <span class="json-key">"unit_of_measure"</span>: <span class="json-string">"Truck (10ton)"</span>
                }</span>
            ]
        }</span>
    ]
}</pre>
                        </div>
                    </div>

                    <!-- Get Product -->
                    <div class="step-content" id="get-product">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Get a Specific Product</h2>
                        <p class="mb-4">This endpoint retrieves a specific product by its ID.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">HTTP Request</h3>
                        <div class="mb-2">
                            <span class="http-method get">GET</span>
                        </div>
                        <div class="code-block mb-6">
                            <code>https://your-domain.com/admin/fetch/manageProducts/getProduct?id={product_id}</code>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Arguments</h3>
                        <div class="table-container">
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
                                        <td>The UUID of the product to retrieve</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Response</h3>
                        <p class="mb-4">Returns a JSON object containing the requested product.</p>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Sample Response</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"data"</span>: <span class="json-key">{
        "uuid_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0ed"</span>,
        <span class="json-key">"title"</span>: <span class="json-string">"Portland Cement"</span>,
        <span class="json-key">"uuid_category"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e2"</span>,
        <span class="json-key">"description"</span>: <span class="json-string">"High-quality Portland cement for projects"</span>,
        <span class="json-key">"meta_title"</span>: <span class="json-string">"Portland Cement | Materials"</span>,
        <span class="json-key">"meta_description"</span>: <span class="json-string">"Premium Portland cement for all your needs"</span>,
        <span class="json-key">"meta_keywords"</span>: <span class="json-string">"portland cement, cement, building materials"</span>,
        <span class="json-key">"views"</span>: <span class="json-number">0</span>,
        <span class="json-key">"status"</span>: <span class="json-string">"published"</span>,
        <span class="json-key">"featured"</span>: <span class="json-number">1</span>,
        <span class="json-key">"created_at"</span>: <span class="json-string">"2023-06-15 12:30:45"</span>,
        <span class="json-key">"updated_at"</span>: <span class="json-string">"2023-06-15 12:30:45"</span>,
        <span class="json-key">"category_name"</span>: <span class="json-string">"Building Materials"</span>,
        <span class="json-key">"images"</span>: [
            <span class="json-string">"https://your-domain.com/img/products/018d3b4c-5f3e-7c10-b96e-f7e423d7f0ed/prod_1234567.jpg"</span>
        ],
        <span class="json-key">"units_of_measure"</span>: [
            <span class="json-key">{
                "id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0ee"</span>,
                <span class="json-key">"unit_of_measure_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e9"</span>,
                <span class="json-key">"price"</span>: <span class="json-number">35000</span>,
                <span class="json-key">"si_unit"</span>: <span class="json-string">"50kg"</span>,
                <span class="json-key">"package_name"</span>: <span class="json-string">"Bag"</span>,
                <span class="json-key">"unit_of_measure"</span>: <span class="json-string">"Bag (50kg)"</span>
            }</span>
        ]
    }</span>
}</pre>
                        </div>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Error Response (Product Not Found)</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">false</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"Product not found"</span>
}</pre>
                        </div>
                    </div>

                    <!-- Create Product -->
                    <div class="step-content" id="create-product">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Create a Product</h2>
                        <p class="mb-4">This endpoint creates a new product.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">HTTP Request</h3>
                        <div class="mb-2">
                            <span class="http-method post">POST</span>
                        </div>
                        <div class="code-block mb-6">
                            <code>https://your-domain.com/admin/fetch/manageProducts/createProduct</code>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Arguments</h3>
                        <div class="table-container">
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
                                        <td>Title of the product</td>
                                    </tr>
                                    <tr>
                                        <td>category_id</td>
                                        <td>string</td>
                                        <td>Required</td>
                                        <td>UUID of the product category</td>
                                    </tr>
                                    <tr>
                                        <td>description</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>Description of the product</td>
                                    </tr>
                                    <tr>
                                        <td>meta_title</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>SEO meta title</td>
                                    </tr>
                                    <tr>
                                        <td>meta_description</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>SEO meta description</td>
                                    </tr>
                                    <tr>
                                        <td>meta_keywords</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>SEO meta keywords (comma-separated)</td>
                                    </tr>
                                    <tr>
                                        <td>status</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>Status of the product (published, pending, or draft). Defaults to published.</td>
                                    </tr>
                                    <tr>
                                        <td>featured</td>
                                        <td>boolean</td>
                                        <td>Optional</td>
                                        <td>Whether the product is featured. Defaults to false.</td>
                                    </tr>
                                    <tr>
                                        <td>units_of_measure</td>
                                        <td>array</td>
                                        <td>Optional</td>
                                        <td>Array of unit of measure objects with pricing</td>
                                    </tr>
                                    <tr>
                                        <td>temp_images</td>
                                        <td>array</td>
                                        <td>Optional</td>
                                        <td>Array of temporary image paths</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Sample Request</h3>
                        <div class="code-block mb-6">
                            <pre>curl -X POST "https://your-domain.com/admin/fetch/manageProducts/createProduct" \
-H "Content-Type: application/json" \
-d '{
    <span class="json-key">"title"</span>: <span class="json-string">"Reinforced Steel Bars"</span>,
    <span class="json-key">"category_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e2"</span>,
    <span class="json-key">"description"</span>: <span class="json-string">"High-quality reinforced steel bars for concrete reinforcement"</span>,
    <span class="json-key">"meta_title"</span>: <span class="json-string">"Reinforced Steel Bars | Materials"</span>,
    <span class="json-key">"meta_description"</span>: <span class="json-string">"Premium reinforced steel bars for concrete structures"</span>,
    <span class="json-key">"meta_keywords"</span>: <span class="json-string">"steel bars, rebar, reinforcement, materials"</span>,
    <span class="json-key">"status"</span>: <span class="json-string">"published"</span>,
    <span class="json-key">"featured"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"units_of_measure"</span>: [
        {
            <span class="json-key">"unit_of_measure_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0eb"</span>,
            <span class="json-key">"price"</span>: <span class="json-number">750000</span>
        }
    ]
}'</pre>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Response</h3>
                        <p class="mb-4">Returns a JSON object containing the success status, message, and the ID of the newly created product.</p>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Sample Response</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"Product created successfully"</span>,
    <span class="json-key">"id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0f1"</span>
}</pre>
                        </div>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Error Response (Invalid Category)</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">false</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"Invalid category selected"</span>
}</pre>
                        </div>
                    </div>

                    <!-- Update Product -->
                    <div class="step-content" id="update-product">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Update a Product</h2>
                        <p class="mb-4">This endpoint updates an existing product.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">HTTP Request</h3>
                        <div class="mb-2">
                            <span class="http-method post">POST</span>
                        </div>
                        <div class="code-block mb-6">
                            <code>https://your-domain.com/admin/fetch/manageProducts/updateProduct</code>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Arguments</h3>
                        <div class="table-container">
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
                                        <td>UUID of the product to update</td>
                                    </tr>
                                    <tr>
                                        <td>title</td>
                                        <td>string</td>
                                        <td>Required</td>
                                        <td>Title of the product</td>
                                    </tr>
                                    <tr>
                                        <td>category_id</td>
                                        <td>string</td>
                                        <td>Required</td>
                                        <td>UUID of the product category</td>
                                    </tr>
                                    <tr>
                                        <td>description</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>Description of the product</td>
                                    </tr>
                                    <tr>
                                        <td>meta_title</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>SEO meta title</td>
                                    </tr>
                                    <tr>
                                        <td>meta_description</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>SEO meta description</td>
                                    </tr>
                                    <tr>
                                        <td>meta_keywords</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>SEO meta keywords (comma-separated)</td>
                                    </tr>
                                    <tr>
                                        <td>status</td>
                                        <td>string</td>
                                        <td>Optional</td>
                                        <td>Status of the product (published, pending, or draft)</td>
                                    </tr>
                                    <tr>
                                        <td>featured</td>
                                        <td>boolean</td>
                                        <td>Optional</td>
                                        <td>Whether the product is featured</td>
                                    </tr>
                                    <tr>
                                        <td>units_of_measure</td>
                                        <td>array</td>
                                        <td>Optional</td>
                                        <td>Array of unit of measure objects with pricing</td>
                                    </tr>
                                    <tr>
                                        <td>update_images</td>
                                        <td>boolean</td>
                                        <td>Optional</td>
                                        <td>Whether to update the product images</td>
                                    </tr>
                                    <tr>
                                        <td>existing_images</td>
                                        <td>array</td>
                                        <td>Optional</td>
                                        <td>Array of existing image URLs to keep</td>
                                    </tr>
                                    <tr>
                                        <td>temp_images</td>
                                        <td>array</td>
                                        <td>Optional</td>
                                        <td>Array of temporary image paths to add</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Sample Request</h3>
                        <div class="code-block mb-6">
                            <pre>curl -X POST "https://your-domain.com/admin/fetch/manageProducts/updateProduct" \
-H "Content-Type: application/json" \
-d '{
    <span class="json-key">"id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0ed"</span>,
    <span class="json-key">"title"</span>: <span class="json-string">"Premium Portland Cement"</span>,
    <span class="json-key">"category_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e2"</span>,
    <span class="json-key">"description"</span>: <span class="json-string">"High-quality Portland cement for all projects"</span>,
    <span class="json-key">"meta_title"</span>: <span class="json-string">"Premium Portland Cement | Materials"</span>,
    <span class="json-key">"meta_description"</span>: <span class="json-string">"Premium Portland cement for all your needs"</span>,
    <span class="json-key">"meta_keywords"</span>: <span class="json-string">"portland cement, premium cement, cement, building materials"</span>,
    <span class="json-key">"status"</span>: <span class="json-string">"published"</span>,
    <span class="json-key">"featured"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"units_of_measure"</span>: [
        {
            <span class="json-key">"unit_of_measure_id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0e9"</span>,
            <span class="json-key">"price"</span>: <span class="json-number">37500</span>
        }
    ]
}'</pre>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Response</h3>
                        <p class="mb-4">Returns a JSON object containing the success status and a message.</p>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Sample Response</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"Product updated successfully"</span>
}</pre>
                        </div>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Error Response (Product Not Found)</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">false</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"Product not found"</span>
}</pre>
                        </div>
                    </div>

                    <!-- Delete Product -->
                    <div class="step-content" id="delete-product">
                        <h2 class="text-3xl font-bold text-secondary mb-6">Delete a Product</h2>
                        <p class="mb-4">This endpoint deletes a product.</p>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">HTTP Request</h3>
                        <div class="mb-2">
                            <span class="http-method post">POST</span>
                        </div>
                        <div class="code-block mb-6">
                            <code>https://your-domain.com/admin/fetch/manageProducts/deleteProduct</code>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Arguments</h3>
                        <div class="table-container">
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
                                        <td>The UUID of the product to delete</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Sample Request</h3>
                        <div class="code-block mb-6">
                            <pre>curl -X POST "https://your-domain.com/admin/fetch/manageProducts/deleteProduct" \
-H "Content-Type: application/json" \
-d '{
    <span class="json-key">"id"</span>: <span class="json-string">"018d3b4c-5f3e-7c10-b96e-f7e423d7f0ed"</span>
}'</pre>
                        </div>

                        <h3 class="text-xl font-semibold text-secondary mt-8 mb-4">Response</h3>
                        <p class="mb-4">Returns a JSON object containing the success status and a message.</p>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Sample Response</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"Product deleted successfully"</span>
}</pre>
                        </div>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Error Response (Product Not Found)</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">false</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"Product not found"</span>
}</pre>
                        </div>

                        <h4 class="text-lg font-medium text-secondary mt-6 mb-3">Error Response (Product In Use)</h4>
                        <div class="code-block mb-6">
                            <pre><span class="json-key">{
    "success"</span>: <span class="json-boolean">false</span>,
    <span class="json-key">"message"</span>: <span class="json-string">"Cannot delete this product because it is used elsewhere"</span>
}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');

            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('open');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768 && !sidebar.contains(event.target) && event.target !== menuToggle) {
                    sidebar.classList.remove('open');
                }
            });

            // Navigation links
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all links
                    navLinks.forEach(l => l.classList.remove('active'));

                    // Add active class to clicked link
                    this.classList.add('active');

                    // Get the target section ID
                    const targetId = this.getAttribute('href').substring(1);

                    // Find the index of the target section
                    const steps = document.querySelectorAll('.step-content');
                    let targetIndex = 0;
                    steps.forEach((step, index) => {
                        if (step.id === targetId) {
                            targetIndex = index;
                        }
                    });

                    // Update current step
                    currentStep = targetIndex + 1;
                    updateStepIndicator();

                    // Show the target section
                    showStep(currentStep);

                    // Close sidebar on mobile
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('open');
                    }
                });
            });

            // Step navigation
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const steps = document.querySelectorAll('.step-content');
            const totalStepsEl = document.getElementById('totalSteps');
            const currentStepEl = document.getElementById('currentStep');

            let currentStep = 1;
            totalStepsEl.textContent = steps.length;
            currentStepEl.textContent = currentStep;

            prevBtn.addEventListener('click', function() {
                if (currentStep > 1) {
                    currentStep--;
                    showStep(currentStep);
                    updateStepIndicator();
                }
            });

            nextBtn.addEventListener('click', function() {
                if (currentStep < steps.length) {
                    currentStep++;
                    showStep(currentStep);
                    updateStepIndicator();
                }
            });

            function showStep(stepNumber) {
                steps.forEach(step => step.classList.remove('active'));
                steps[stepNumber - 1].classList.add('active');

                // Update navigation buttons
                prevBtn.disabled = stepNumber === 1;
                nextBtn.disabled = stepNumber === steps.length;

                // Update active nav link
                const currentStepId = steps[stepNumber - 1].id;
                navLinks.forEach(link => {
                    const linkTarget = link.getAttribute('href').substring(1);
                    if (linkTarget === currentStepId) {
                        link.classList.add('active');
                    } else {
                        link.classList.remove('active');
                    }
                });

                // Scroll to top
                window.scrollTo(0, 0);
            }

            function updateStepIndicator() {
                currentStepEl.textContent = currentStep;
            }
        });
    </script>
</body>

</html>