<?php
$pageTitle = 'About Us';
$activeNav = 'about';
require_once __DIR__ . '/config/config.php';
ob_start();
?>

<style>
    .about-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 2rem;
    }

    .about-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .about-title {
        font-size: 3rem;
        color: #C00000;
        margin-bottom: 1rem;
    }

    .about-subtitle {
        font-size: 1.5rem;
        color: #4B5563;
    }

    .about-section {
        display: flex;
        align-items: center;
        margin-bottom: 4rem;
    }

    .about-section:nth-child(even) {
        flex-direction: row-reverse;
    }

    .about-content {
        flex: 1;
        padding: 2rem;
    }

    .about-image {
        flex: 1;
        text-align: center;
    }

    .about-image img {
        max-width: 100%;
        border-radius: 1rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .about-text {
        font-size: 1.125rem;
        color: #4B5563;
        line-height: 1.8;
        margin-bottom: 1.5rem;
    }

    .about-cta {
        display: inline-block;
        background-color: #C00000;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        text-decoration: none;
        font-weight: bold;
        transition: background-color 0.3s ease;
    }

    .about-cta:hover {
        background-color: #A00000;
    }

    @media (max-width: 768px) {
        .about-section {
            flex-direction: column !important;
        }

        .about-image {
            margin-bottom: 2rem;
        }
    }
</style>

<div class="about-container">
    <div class="about-header">
        <h1 class="about-title">Our Story</h1>
        <p class="about-subtitle">Building Dreams, Connecting Communities</p>
    </div>

    <div class="about-section">
        <div class="about-content">
            <p class="about-text">
                Once upon a time, in the heart of Uganda, we had a dream. A dream of a digital space where buyers and sellers could connect, where businesses could flourish, and communities could thrive. This dream gave birth to ZzimbaOnline.com, a digital marketplace that has since transformed the way people shop and sell.
            </p>
            <p class="about-text">
                We started with a simple idea: to make it easier for people to find the products and services they need. We envisioned a platform that would connect local businesses with a wider audience, empowering them to grow and prosper.
            </p>
        </div>
        <div class="about-image">
            <img src="<?= BASE_URL ?>/img/about.jpg" alt="Our Dream" />
        </div>
    </div>

    <div class="about-section">
        <div class="about-content">
            <h2 class="about-subtitle">Marketplace</h2>
            <p class="about-text">
                Today, Zzimba Online is a bustling hub of activity, bringing together a diverse community of buyers and sellers. We've witnessed countless success stories, from small businesses expanding their reach to individuals finding unique products they never knew existed.
            </p>
            <p class="about-text">
                But our journey is far from over. We're constantly striving to improve our platform and offer an even better experience for our users. We're committed to innovation, customer satisfaction, and building a strong, sustainable business.
            </p>
        </div>
        <div class="about-image">
            <img src="<?= BASE_URL ?>/img/about.jpg" alt="Our Marketplace" />
        </div>
    </div>

    <div class="about-section">
        <div class="about-content">
            <h2 class="about-subtitle">Join Us on This Exciting Journey</h2>
            <p class="about-text">
                Be a part of something special. Whether you're a buyer looking for unique products or a vendor aiming to expand your business, Zzimba Online is the place for you.
            </p>
            <a href="#" class="about-cta" id="join-zzimba-btn">Join Today</a>
        </div>
        <div class="about-image">
            <img src="<?= BASE_URL ?>/img/about.jpg" alt="Join Our Community" />
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        notifications.info('Discover the story behind Zzimba Online!', 'About Us');

        // Get the "Join Zzimba Online Today" button
        const joinButton = document.getElementById('join-zzimba-btn');

        joinButton.addEventListener('click', function(e) {
            e.preventDefault(); 

            openAuthModal();

            switchForm('register-form');
        });
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>