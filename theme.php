<?php
$pageTitle = $pageTitle ?? 'Theme Documentation';
$activeNav = $activeNav ?? 'theme';
ob_start();
?>
<div class="max-w-3xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-secondary mb-4">Zzimba Online – Theme Documentation</h1>
    <p class="text-gray-700 mb-6">
        The following information outlines the design standards for the Zzimba Online web application.
        These guidelines help maintain visual consistency and a strong brand presence throughout the platform.
    </p>

    <hr class="my-6 border-gray-200"> 

    <section class="mb-8">
        <h2 class="text-xl font-semibold text-primary mb-4">Brand Colors</h2>
        <ul class="list-none space-y-2 text-gray-700">
            <li>
                <span class="inline-block w-4 h-4 bg-[#C00000] mr-2 align-middle"></span>
                <strong>Primary:</strong> #C00000
            </li>
            <li>
                <span class="inline-block w-4 h-4 bg-[#1a1a1a] mr-2 align-middle"></span>
                <strong>Secondary:</strong> #1a1a1a
            </li>
            <li>
                <span class="inline-block w-4 h-4 bg-[#4B5563] mr-2 align-middle"></span>
                <strong>Gray Text:</strong> #4B5563
            </li>
        </ul>
        <p class="mt-4">
            The primary color (<strong>#C00000</strong>) is used for buttons, highlights, and important action elements.
            The secondary color (<strong>#1a1a1a</strong>) is reserved for headers, footers, and other background sections that emphasize contrast.
            Grays are used for less prominent text, icons, and borders.
        </p>
    </section>

    <section class="mb-8">
        <h2 class="text-xl font-semibold text-primary mb-4">Typography</h2>
        <p class="text-gray-700 mb-3">
            The primary font is <strong>Rubik</strong>, imported from Google Fonts.
            Font weights range from 300 (light) to 700 (bold), ensuring good readability across headings, paragraphs, and emphasis elements.
        </p>
        <ul class="list-disc list-inside text-gray-700">
            <li><strong>Default Body Text:</strong> 1rem (16px) – for most paragraphs and longer content.</li>
            <li><strong>Headings (H1, H2, etc.):</strong> 1.25rem (20px) and above, with bolder weights to ensure hierarchy.</li>
            <li><strong>Small Text / Labels:</strong> 0.875rem (14px) – used for helper text, labels, or captions.</li>
        </ul>
    </section>

    <section class="mb-8">
        <h2 class="text-xl font-semibold text-primary mb-4">Spacing &amp; Layout</h2>
        <p class="text-gray-700">
            The design leverages a consistent spacing scale (often multiples of 4px in Tailwind).
            Common spacing includes 1rem, 1.5rem, and 2rem for margins, padding, and sections.
        </p>
        <ul class="list-disc list-inside text-gray-700 mt-3">
            <li><strong>Section Spacing:</strong> 2rem (32px) to separate distinct content blocks.</li>
            <li><strong>Inner Spacing for Cards, Forms, etc.:</strong> 1rem (16px) or 1.5rem (24px) for a balanced look.</li>
        </ul>
    </section>

    <section class="mb-8">
        <h2 class="text-xl font-semibold text-primary mb-4">Iconography</h2>
        <p class="text-gray-700">
            <strong>Font Awesome</strong> is integrated for icons. They primarily adopt <em>secondary color (#1a1a1a)</em>,
            but can switch to <em>primary color (#C00000)</em> on hover or when indicating an active state.
        </p>
    </section>

    <section class="mb-8">
        <h2 class="text-xl font-semibold text-primary mb-4">Use of Tailwind CSS</h2>
        <p class="text-gray-700">
            Tailwind CSS is employed to maintain consistency in colors, spacing, and typography through utility classes.
            Custom configurations in <code>tailwind.config</code> override defaults to include the brand colors and the Rubik font.
        </p>
    </section>

    <hr class="my-6 border-gray-200">

    <p class="text-gray-700">
        Overall, Zzimba Online’s visual identity focuses on bold use of the red primary color for calls to action,
        a dark secondary color for structure, and clean sans-serif typography for clarity and modern appeal.
    </p>
</div>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>