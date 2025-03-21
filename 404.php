<?php
$pageTitle = $pageTitle ?? 'Resource Not Found';
$activeNav = $activeNav ?? '404';
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

    .error-svg .animate-float {
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-20px);
        }
    }
</style>

<div class="error-container">
    <svg class="error-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="200" height="200" fill="#f3f4f6" />
        <g class="animate-float">
            <path d="M60,100 L100,60 L140,100 L100,140 Z" fill="#C00000" />
            <text x="100" y="110" font-family="Arial" font-size="40" fill="white" text-anchor="middle">404</text>
        </g>
        <path d="M20,180 Q100,120 180,180" stroke="#1a1a1a" stroke-width="4" fill="none" />
    </svg>
    <div class="error-code">404</div>
    <h1 class="error-message">Oops! Page Not Found</h1>
    <p class="error-description">
        We're sorry, but the page you're looking for doesn't exist or has been moved.
        Don't worry, you can find many other great products on our home page.
    </p>
    <a href="<?= BASE_URL ?>" class="home-button">
        <i class="fas fa-home"></i> Back to Home
    </a>
</div>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>