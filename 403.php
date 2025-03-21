<?php
$pageTitle = $pageTitle ?? 'Unauthorized Access';
$activeNav = $activeNav ?? '403';
require_once __DIR__ . '/config/config.php';
ob_start();
?>
<style>
    .error-container {
        min-height: calc(100vh - 80px - 320px);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 2rem;
    }

    .error-code {
        font-size: 8rem;
        font-weight: bold;
        color: #C00000;
        line-height: 1;
    }

    .error-message {
        font-size: 2rem;
        color: #1a1a1a;
        margin-bottom: 1.5rem;
    }

    .error-description {
        font-size: 1.125rem;
        color: #4B5563;
        max-width: 600px;
        margin-bottom: 2rem;
    }

    .home-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        font-weight: 500;
        text-decoration: none;
        color: white;
        background-color: #C00000;
        border-radius: 0.375rem;
        transition: background-color 0.3s ease;
    }

    .home-button:hover {
        background-color: #A00000;
    }

    .home-button i {
        margin-right: 0.5rem;
    }

    .error-svg {
        width: 200px;
        height: 200px;
        margin-bottom: 2rem;
    }

    .error-svg .animate-lock {
        animation: lock 2s ease-in-out infinite;
    }

    @keyframes lock {

        0%,
        100% {
            transform: translateY(0) rotate(0);
        }

        50% {
            transform: translateY(-10px) rotate(5deg);
        }
    }
</style>

<div class="error-container">
    <svg class="error-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="200" height="200" fill="#f3f4f6" />
        <g class="animate-lock">
            <rect x="70" y="80" width="60" height="80" rx="10" fill="#C00000" />
            <circle cx="100" cy="120" r="10" fill="white" />
            <rect x="95" y="120" width="10" height="20" fill="white" />
            <path d="M70,80 V60 Q70,40 100,40 Q130,40 130,60 V80" stroke="#1a1a1a" stroke-width="10" fill="none" />
        </g>
    </svg>
    <div class="error-code">403</div>
    <h1 class="error-message">Access Forbidden</h1>
    <p class="error-description">
        Sorry, you don't have permission to access this page.
        If you believe this is an error, please contact the site administrator.
    </p>
    <a href="<?= BASE_URL ?>" class="home-button">
        <i class="fas fa-home"></i> Back to Home
    </a>
</div>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>