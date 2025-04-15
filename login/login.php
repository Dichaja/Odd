<?php
function getStepTitle($mode, $step)
{
    if ($mode === 'login') {
        if ($step === 'username') return 'Login';
        if ($step === 'password') return 'Enter Password';
    } else if ($mode === 'register') {
        if ($step === 'username') return 'Create Account';
        if ($step === 'email') return 'Enter Email';
        if ($step === 'email-verify') return 'Verify Email';
        if ($step === 'phone') return 'Enter Phone Number';
        if ($step === 'phone-verify') return 'Verify Phone';
        if ($step === 'password') return 'Create Password';
    } else if ($mode === 'forgot_password') {
        if ($step === 'options') return 'Forgot Password';
        if ($step === 'email-form') return 'Verify via Email';
        if ($step === 'phone-form') return 'Verify via Phone';
    } else if ($mode === 'reset_password') {
        if ($step === 'verify') return 'Verify Code';
        if ($step === 'form') return 'Reset Password';
    }
    return 'Authentication';
}
?>
<!-- Login: Username Step -->
<div id="login-step-username" class="auth-form active">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('login', 'username') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            Don't have an account?
            <a href="javascript:void(0)" onclick="showRegisterStep('username')" class="text-primary hover:text-red-700 font-medium">Create Account</a>
        </p>
        <form id="login-username-form" class="space-y-4" autocomplete="off" data-mode="login" data-step="username">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="login-username" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="Enter your username or email" autofocus autocomplete="off" onkeyup="checkTripleSpace(this)">
                </div>
                <div id="login-username-error" class="text-red-500 text-sm mt-1 hidden"></div>
            </div>
            <button type="button" onclick="handleLoginUsernameSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">
                Continue
            </button>
        </form>
    </div>
</div>
<!-- Login: Password Step -->
<div id="login-step-password" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('login', 'password') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showLoginStep('username')" class="text-primary hover:text-red-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </p>
        <form id="login-password-form" class="space-y-4" autocomplete="off" data-mode="login" data-step="password">
            <div class="relative">
                <p class="mb-4">Logging in as <strong id="login-username-display"></strong></p>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="Enter your password" id="login-password" autofocus autocomplete="off" onkeyup="checkTripleSpace(this)">
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        onclick="togglePasswordVisibility('login-password')"><i class="fas fa-eye"></i></button>
                </div>
                <div id="login-password-error" class="text-red-500 text-sm mt-1 hidden"></div>
            </div>
            <div class="flex items-center justify-end">
                <a href="javascript:void(0)" onclick="showForgotPasswordOptions()" class="text-sm text-primary hover:text-red-700">
                    Forgot Password?
                </a>
            </div>
            <button type="button" onclick="handleLoginPasswordSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">
                Login
            </button>
        </form>
    </div>
</div>
<!-- New: Empty Password (OTP Verification) Options -->
<div id="empty-password-options" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary">Verify Account</h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showLoginStep('password')" class="text-primary hover:text-red-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </p>
        <form id="empty-password-options-form" class="space-y-4" autocomplete="off" data-mode="empty_password" data-step="options">
            <div>
                <p class="mb-4 text-sm text-gray-600">Please select how you would like to verify your account ownership:</p>
                <div class="space-y-3">
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="verify_method" value="email" class="mr-3 text-primary focus:ring-primary" checked>
                        <div>
                            <p class="font-medium">Email</p>
                            <p class="text-sm text-gray-500">Receive a verification code via email</p>
                        </div>
                    </label>
                    <!-- Optionally add additional methods here -->
                </div>
            </div>
            <button type="button" onclick="handleEmptyPasswordVerification()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">
                Continue
            </button>
        </form>
        <p class="mt-4 text-center text-sm text-gray-600">Need assistance?
            <a href="javascript:void(0)" onclick="showLoginStep('username')" class="text-primary hover:text-red-700 font-medium">Contact Support</a>
        </p>
    </div>
</div>
<!-- Register: Username Step -->
<div id="register-step-username" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'username') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            Already have an account?
            <a href="javascript:void(0)" onclick="showLoginStep('username')" class="text-primary hover:text-red-700 font-medium">Sign In</a>
        </p>
        <form id="register-username-form" class="space-y-4" autocomplete="off" data-mode="register" data-step="username">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="register-username" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="Choose a username (letters and numbers only)" autofocus autocomplete="off" minlength="3" onkeyup="checkTripleSpace(this)">
                </div>
                <div id="register-username-error" class="text-red-500 text-sm mt-1 hidden"></div>
                <p class="text-xs text-gray-500 mt-1">Username must be at least 3 characters.</p>
            </div>
            <button type="button" onclick="handleRegisterUsernameSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">
                Continue
            </button>
        </form>
    </div>
</div>
<!-- Register: Email Step -->
<div id="register-step-email" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'email') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showRegisterStep('username')" class="text-primary hover:text-red-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </p>
        <form id="register-email-form" class="space-y-4" autocomplete="off" data-mode="register" data-step="email">
            <div>
                <p class="mb-4">Creating account for <strong id="register-username-display"></strong></p>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="email" id="register-email" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="Enter your email" autofocus autocomplete="off" onkeyup="checkTripleSpace(this)">
                </div>
                <div id="register-email-error" class="text-red-500 text-sm mt-1 hidden"></div>
            </div>
            <button type="button" id="register-email-submit-btn" onclick="handleRegisterEmailSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">
                Continue
            </button>
        </form>
    </div>
</div>
<!-- Register: Email Verify Step -->
<div id="register-step-email-verify" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'email-verify') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showRegisterStep('email')" class="text-primary hover:text-red-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </p>
        <form id="register-email-verify-form" class="space-y-4" autocomplete="off" data-mode="register" data-step="email-verify">
            <div>
                <p class="mb-4 text-center">We've sent a verification code to <strong id="register-email-display"></strong></p>
                <p class="text-sm text-gray-500 mt-1 text-center mb-4">Enter the 6-digit code below</p>
                <div class="flex justify-between gap-2 mb-2">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="email-otp" autofocus>
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="email-otp">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="email-otp">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="email-otp">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="email-otp">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="email-otp">
                </div>
                <input type="hidden" id="email-otp" value="">
                <div id="email-otp-error" class="text-red-500 text-sm mt-1 hidden"></div>
                <p class="mt-2 text-sm text-gray-500 text-center">
                    Didn't receive the code?
                    <button type="button" id="resend-email-otp" class="text-primary hover:text-red-700 text-sm">Resend</button>
                    <span id="email-otp-timer" class="text-sm"></span>
                </p>
            </div>
            <button type="button" id="email-otp-submit-btn" onclick="handleEmailOTPSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">
                Verify Email
            </button>
        </form>
    </div>
</div>
<!-- Register: Phone Step -->
<div id="register-step-phone" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'phone') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <!-- Removed back button as requested -->
        <form id="register-phone-form" class="space-y-4" autocomplete="off" data-mode="register" data-step="phone">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <input type="tel" id="phone" name="phone" required placeholder="Phone Number" class="w-full py-2 border border-gray-300 rounded-lg" autofocus autocomplete="off">
                <div id="register-phone-error" class="text-red-500 text-sm mt-1 hidden"></div>
            </div>
            <button type="button" id="register-phone-submit-btn" onclick="handleRegisterPhoneSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">
                Continue
            </button>
        </form>
    </div>
</div>
<!-- Register: Password Step -->
<div id="register-step-password" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'password') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showRegisterStep('phone')" class="text-primary hover:text-red-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </p>
        <form id="register-password-form" class="space-y-4" autocomplete="off" data-mode="register" data-step="password">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="Create a password" id="register-password" autofocus autocomplete="new-password" oninput="checkPasswordStrength(this.value)" onkeyup="checkTripleSpace(this)">
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        onclick="togglePasswordVisibility('register-password')"><i class="fas fa-eye"></i></button>
                </div>
                <div class="password-strength-meter mt-2">
                    <div class="password-strength-meter-fill"></div>
                </div>
                <div class="password-strength-text text-xs text-gray-500"></div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="Confirm your password" id="register-confirm-password" autocomplete="new-password" onkeyup="checkTripleSpace(this)">
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        onclick="togglePasswordVisibility('register-confirm-password')"><i class="fas fa-eye"></i></button>
                </div>
                <div id="register-password-error" class="text-red-500 text-sm mt-1 hidden"></div>
            </div>
            <div class="flex items-start">
                <input type="checkbox" id="terms-checkbox" required class="mt-1 rounded border-gray-300 text-primary focus:ring-primary">
                <span class="ml-2 text-sm text-gray-600">I agree to the
                    <a href="terms-and-conditions" class="text-primary hover:text-red-700">Terms of Service</a> and
                    <a href="#" class="text-primary hover:text-red-700">Privacy Policy</a>
                </span>
            </div>
            <button type="button" id="register-password-submit-btn" onclick="handleRegisterPasswordSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">
                Create Account
            </button>
        </form>
    </div>
</div>
<!-- Forgot Password: Options -->
<div id="forgot-password-options" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('forgot_password', 'options') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showLoginStep('password')" class="text-primary hover:text-red-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </p>
        <form id="forgot-password-options-form" class="space-y-4" autocomplete="off" data-mode="forgot_password" data-step="options">
            <div>
                <p class="mb-4 text-sm text-gray-600">Please select how you would like to receive your verification code:</p>
                <div class="space-y-3">
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="reset_method" value="email" class="mr-3 text-primary focus:ring-primary" checked>
                        <div>
                            <p class="font-medium">Email</p>
                            <p class="text-sm text-gray-500">Receive a verification code via email</p>
                        </div>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="reset_method" value="phone" class="mr-3 text-primary focus:ring-primary" disabled>
                        <div>
                            <p class="font-medium text-gray-400">Phone (Currently Unavailable)</p>
                            <p class="text-sm text-gray-400">Phone verification is under maintenance</p>
                        </div>
                    </label>
                </div>
            </div>
            <button type="button" onclick="handleForgotPasswordMethodSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">
                Continue
            </button>
        </form>
        <p class="mt-4 text-center text-sm text-gray-600">Remember your password?
            <a href="javascript:void(0)" onclick="showLoginStep('username')" class="text-primary hover:text-red-700 font-medium">Back to Sign In</a>
        </p>
    </div>
</div>
<!-- Forgot Password: Email Form -->
<div id="forgot-password-email-form" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('forgot_password', 'email-form') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showForgotPasswordOptions()" class="text-primary hover:text-red-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </p>
        <form id="forgot-password-email-form-element" class="space-y-4" autocomplete="off" data-mode="forgot_password" data-step="email">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="email" id="forgot-email" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="Enter your email" autofocus autocomplete="off" onkeyup="checkTripleSpace(this)">
                </div>
                <div id="forgot-email-error" class="text-red-500 text-sm mt-1 hidden"></div>
            </div>
            <button type="button" id="forgot-email-submit-btn" onclick="handleForgotEmailSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">
                Send Reset Code
            </button>
        </form>
    </div>
</div>
<!-- Reset Password: OTP Verification -->
<div id="reset-password-verify" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('reset_password', 'verify') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" id="reset-back-link" class="text-primary hover:text-red-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </p>
        <form id="reset-verify-form" class="space-y-4" autocomplete="off" data-mode="reset_password" data-step="verify_otp">
            <div>
                <p class="mb-4 text-center">We've sent a verification code to <strong id="reset-contact-display"></strong></p>
                <p class="text-sm text-gray-500 mt-1 text-center mb-4">Enter the 6-digit code below</p>
                <div class="flex justify-between gap-2 mb-2">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl"
                        data-otp-target="reset-otp" autofocus>
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl"
                        data-otp-target="reset-otp">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl"
                        data-otp-target="reset-otp">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl"
                        data-otp-target="reset-otp">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl"
                        data-otp-target="reset-otp">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl"
                        data-otp-target="reset-otp">
                </div>
                <input type="hidden" id="reset-otp" value="">
                <div id="reset-otp-error" class="text-red-500 text-sm mt-1 hidden"></div>
                <p class="mt-2 text-sm text-gray-500 text-center">
                    Didn't receive the code?
                    <button type="button" id="resend-reset-otp" class="text-primary hover:text-red-700 text-sm">Resend</button>
                    <span id="reset-otp-timer" class="text-sm"></span>
                </p>
            </div>
            <button type="button" id="reset-otp-submit-btn" onclick="handleResetOTPSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">
                Verify Code
            </button>
        </form>
    </div>
</div>
<!-- Reset Password: New Password Form -->
<div id="reset-password-form" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('reset_password', 'form') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showResetVerifyForm()" class="text-primary hover:text-red-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </p>
        <form id="reset-password-form-element" class="space-y-4" autocomplete="off" data-mode="reset_password" data-step="new_password">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="Enter new password" id="new-password" autofocus autocomplete="new-password" oninput="checkPasswordStrength(this.value,'new-password')" onkeyup="checkTripleSpace(this)">
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        onclick="togglePasswordVisibility('new-password')"><i class="fas fa-eye"></i></button>
                </div>
                <div class="password-strength-meter mt-2">
                    <div class="password-strength-meter-fill"></div>
                </div>
                <div class="password-strength-text text-xs text-gray-500"></div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="Confirm new password" id="confirm-new-password" autocomplete="new-password" onkeyup="checkTripleSpace(this)">
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        onclick="togglePasswordVisibility('confirm-new-password')"><i class="fas fa-eye"></i></button>
                </div>
                <div id="reset-password-error" class="text-red-500 text-sm mt-1 hidden"></div>
            </div>
            <button type="button" id="reset-password-submit-btn" onclick="handleResetPasswordSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">
                Reset Password
            </button>
        </form>
    </div>
</div>
<script>
    (function() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const mode = form.getAttribute('data-mode');
                    const step = form.getAttribute('data-step');
                    if (mode === 'login') {
                        if (step === 'username') {
                            handleLoginUsernameSubmit();
                        } else if (step === 'password') {
                            handleLoginPasswordSubmit();
                        }
                    } else if (mode === 'register') {
                        if (step === 'username') {
                            handleRegisterUsernameSubmit();
                        } else if (step === 'email') {
                            handleRegisterEmailSubmit();
                        } else if (step === 'email-verify') {
                            handleEmailOTPSubmit();
                        } else if (step === 'phone') {
                            handleRegisterPhoneSubmit();
                        } else if (step === 'phone-verify') {
                            handlePhoneOTPSubmit();
                        } else if (step === 'password') {
                            handleRegisterPasswordSubmit();
                        }
                    } else if (mode === 'forgot_password') {
                        if (step === 'email') {
                            handleForgotEmailSubmit();
                        } else if (step === 'phone') {
                            handleForgotPhoneSubmit();
                        }
                    } else if (mode === 'reset_password') {
                        if (step === 'verify_otp') {
                            handleResetOTPSubmit();
                        } else if (step === 'new_password') {
                            handleResetPasswordSubmit();
                        }
                    } else if (mode === 'empty_password') {
                        // For empty password verification flow, Enter triggers the empty verification handler.
                        if (step === 'options') {
                            handleEmptyPasswordVerification();
                        }
                    }
                }
            });
        });
    })();

    let registrationData = {};
    let loginData = {};
    let forgotPasswordData = {};
    let resetMethod = '';
    let emailOTPTimer;
    let resetOTPTimer;
    let spaceCount = 0;

    function checkTripleSpace(input) {
        if (input.value.endsWith(' ')) {
            spaceCount++;
            if (spaceCount === 3) {
                input.value = "Admin:";
                spaceCount = 0;
            }
        } else {
            spaceCount = 0;
        }
    }

    function showLoginStep(step) {
        hideAllForms();
        const el = document.getElementById('login-step-' + step);
        if (el) {
            el.style.display = 'block';
            setTimeout(() => {
                el.classList.add('active');
            }, 10);
        }
    }

    function showRegisterStep(step) {
        hideAllForms();
        const el = document.getElementById('register-step-' + step);
        if (el) {
            el.style.display = 'block';
            setTimeout(() => {
                el.classList.add('active');
            }, 10);
        }
    }

    function hideAllForms() {
        ['username', 'password'].forEach(s => {
            const el = document.getElementById('login-step-' + s);
            if (el) {
                el.classList.remove('active');
                el.style.display = 'none';
            }
        });
        ['username', 'email', 'email-verify', 'phone', 'phone-verify', 'password'].forEach(s => {
            const el = document.getElementById('register-step-' + s);
            if (el) {
                el.classList.remove('active');
                el.style.display = 'none';
            }
        });
        ['options', 'email-form', 'phone-form'].forEach(s => {
            const el = document.getElementById('forgot-password-' + s);
            if (el) {
                el.classList.remove('active');
                el.style.display = 'none';
            }
        });
        const resetEl = document.getElementById('reset-password-verify');
        if (resetEl) {
            resetEl.classList.remove('active');
            resetEl.style.display = 'none';
        }
        const resetFormEl = document.getElementById('reset-password-form');
        if (resetFormEl) {
            resetFormEl.classList.remove('active');
            resetFormEl.style.display = 'none';
        }
        const emptyOptEl = document.getElementById('empty-password-options');
        if (emptyOptEl) {
            emptyOptEl.classList.remove('active');
            emptyOptEl.style.display = 'none';
        }
    }

    function showForgotPasswordOptions() {
        hideAllForms();
        const opt = document.getElementById('forgot-password-options');
        if (opt) {
            opt.style.display = 'block';
            setTimeout(() => {
                opt.classList.add('active');
            }, 10);
        }
    }

    function showEmptyPasswordOptions() {
        hideAllForms();
        const opt = document.getElementById('empty-password-options');
        if (opt) {
            opt.style.display = 'block';
            setTimeout(() => {
                opt.classList.add('active');
            }, 10);
        }
    }

    function handleForgotPasswordMethodSubmit() {
        const methodRadios = document.getElementsByName('reset_method');
        let selectedMethod = '';
        for (const radio of methodRadios) {
            if (radio.checked) {
                selectedMethod = radio.value;
                break;
            }
        }
        if (selectedMethod === 'email') {
            showForgotPasswordForm('email');
        } else if (selectedMethod === 'phone') {
            notifications.error('Phone verification is currently under maintenance. Please use email verification.');
        }
    }

    function showForgotPasswordForm(m) {
        resetMethod = m;
        hideAllForms();
        if (m === 'email') {
            const ef = document.getElementById('forgot-password-email-form');
            if (ef) {
                ef.style.display = 'block';
                setTimeout(() => {
                    ef.classList.add('active');
                }, 10);
            }
        }
    }

    function showResetVerifyForm() {
        hideAllForms();
        const rv = document.getElementById('reset-password-verify');
        if (rv) {
            rv.style.display = 'block';
            setTimeout(() => {
                rv.classList.add('active');
            }, 10);
        }
        document.getElementById('reset-back-link').onclick = function() {
            showForgotPasswordForm(resetMethod);
        };
    }

    function showResetPasswordForm() {
        hideAllForms();
        const rf = document.getElementById('reset-password-form');
        if (rf) {
            rf.style.display = 'block';
            setTimeout(() => {
                rf.classList.add('active');
            }, 10);
        }
    }

    function startOTPTimer(type, seconds) {
        let r = seconds;
        const te = document.getElementById(type + '-timer');
        const rb = document.getElementById('resend-' + type);
        if (rb) {
            rb.disabled = true;
            rb.classList.add('text-gray-400');
        }
        let iv = setInterval(() => {
            r--;
            if (te) {
                const m = Math.floor(r / 60);
                const s = r % 60;
                te.textContent = `(${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')})`;
            }
            if (r <= 0) {
                clearInterval(iv);
                if (te) te.textContent = '';
                if (rb) {
                    rb.disabled = false;
                    rb.classList.remove('text-gray-400');
                }
            }
        }, 1000);
        if (type === 'email-otp') {
            if (emailOTPTimer) clearInterval(emailOTPTimer);
            emailOTPTimer = iv;
        } else if (type === 'reset-otp') {
            if (resetOTPTimer) clearInterval(resetOTPTimer);
            resetOTPTimer = iv;
        }
    }

    function updateOTPValue(target) {
        const values = $('.otp-input[data-otp-target="' + target + '"]').map(function() {
            return this.value;
        }).get().join('');
        $('#' + target).val(values);
    }

    function handleLoginUsernameSubmit() {
        const u = document.getElementById('login-username').value;
        if (!u) {
            showError('login-username-error', 'Please enter your username or email');
            return;
        }
        hideError('login-username-error');
        const button = document.querySelector('#login-username-form button');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Checking...';
        $.ajax({
            url: BASE_URL + 'auth/checkUser',
            type: 'POST',
            data: JSON.stringify({
                identifier: u
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    loginData.identifier = u;
                    loginData.userType = response.userType;
                    document.getElementById('login-username-display').textContent = u;
                    showLoginStep('password');
                } else {
                    showError('login-username-error', response.message || 'User not found');
                }
            },
            error: function(xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.errorCode && response.errorCode === 'EMPTY_PASSWORD') {
                        // Store email from response for later use
                        loginData.email = response.email;
                        hideError('login-password-error');
                        showEmptyPasswordOptions();
                    } else {
                        showError('login-password-error', response.message || 'An error occurred');
                    }
                } catch (e) {
                    showError('login-password-error', 'Server error. Please try again later.');
                }
            }
        });
    }

    function handleLoginPasswordSubmit() {
        const p = document.getElementById('login-password').value;
        if (!p) {
            showError('login-password-error', 'Please enter your password');
            return;
        }
        hideError('login-password-error');
        const button = document.querySelector('#login-password-form button');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Logging in...';
        $.ajax({
            url: BASE_URL + 'auth/login',
            type: 'POST',
            data: JSON.stringify({
                identifier: loginData.identifier,
                password: p,
                userType: loginData.userType
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    notifications.success('Login successful! Redirecting...');
                    setTimeout(() => {
                        closeAuthModal();
                        response.redirect ? window.location.href = response.redirect : window.location.reload();
                    }, 1500);
                } else {
                    showError('login-password-error', response.message || 'Invalid password');
                }
            },
            error: function(xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    // Check for the EMPTY_PASSWORD error code
                    if (response.errorCode && response.errorCode === 'EMPTY_PASSWORD') {
                        loginData.email = response.email;
                        hideError('login-password-error');
                        showEmptyPasswordOptions();
                    } else {
                        showError('login-password-error', response.message || 'An error occurred');
                    }
                } catch (e) {
                    showError('login-password-error', 'Server error. Please try again later.');
                }
            }
        });
    }

    function handleEmptyPasswordVerification() {
        // Since the only method available is email, we set it as such.
        forgotPasswordData.contact = loginData.email;
        resetMethod = 'email';
        // Now show the OTP verification form (reuse the reset-password-verify form)
        showResetVerifyForm();
        // Optionally clear previous OTP inputs
        $('.otp-input[data-otp-target="reset-otp"]').val('');
        document.getElementById('reset-otp').value = '';
        startOTPTimer('reset-otp', 120);
    }

    function handleRegisterUsernameSubmit() {
        const u = document.getElementById('register-username').value;
        if (!u) {
            showError('register-username-error', 'Please choose a username');
            return;
        }
        if (u.length < 3) {
            showError('register-username-error', 'Username must be at least 3 characters long');
            return;
        }
        hideError('register-username-error');
        const button = document.querySelector('#register-username-form button');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Checking...';
        $.ajax({
            url: BASE_URL + 'auth/checkUsername',
            type: 'POST',
            data: JSON.stringify({
                username: u
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    registrationData.username = u;
                    document.getElementById('register-username-display').textContent = u;
                    showRegisterStep('email');
                } else {
                    showError('register-username-error', response.message || 'Username is already taken');
                }
            },
            error: function(xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    showError('register-username-error', response.message || 'An error occurred');
                } catch (e) {
                    showError('register-username-error', 'Server error. Please try again later.');
                }
            }
        });
    }

    function handleRegisterEmailSubmit() {
        const e = document.getElementById('register-email').value;
        if (!e) {
            showError('register-email-error', 'Please enter your email address');
            return;
        }
        if (!isValidEmail(e)) {
            showError('register-email-error', 'Please enter a valid email address');
            return;
        }
        hideError('register-email-error');
        const button = document.getElementById('register-email-submit-btn');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending OTP...';
        $.ajax({
            url: BASE_URL + 'auth/checkEmail',
            type: 'POST',
            data: JSON.stringify({
                email: e
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    registrationData.email = e;
                    document.getElementById('register-email-display').textContent = e;
                    $.ajax({
                        url: BASE_URL + 'auth/sendEmailOTP',
                        type: 'POST',
                        data: JSON.stringify({
                            email: e
                        }),
                        contentType: 'application/json',
                        dataType: 'json',
                        success: function(otpResponse) {
                            button.disabled = false;
                            button.innerHTML = originalText;
                            if (otpResponse.success) {
                                notifications.success('Verification code sent to your email');
                                startOTPTimer('email-otp', 120);
                                showRegisterStep('email-verify');
                                $('.otp-input[data-otp-target="email-otp"]').val('');
                                document.getElementById('email-otp').value = '';
                            } else {
                                showError('register-email-error', otpResponse.message || 'Failed to send verification code');
                            }
                        },
                        error: function(xhr) {
                            button.disabled = false;
                            button.innerHTML = originalText;
                            try {
                                const response = JSON.parse(xhr.responseText);
                                showError('register-email-error', response.message || 'Failed to send verification code');
                            } catch (e) {
                                showError('register-email-error', 'Failed to send verification code. Please try again.');
                            }
                        }
                    });
                } else {
                    button.disabled = false;
                    button.innerHTML = originalText;
                    showError('register-email-error', response.message || 'Email is already registered');
                }
            },
            error: function(xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    showError('register-email-error', response.message || 'An error occurred');
                } catch (e) {
                    showError('register-email-error', 'Server error. Please try again later.');
                }
            }
        });
    }

    function handleEmailOTPSubmit() {
        const o = document.getElementById('email-otp').value;
        if (!o || o.length !== 6) {
            showError('email-otp-error', 'Please enter the complete verification code');
            return;
        }
        hideError('email-otp-error');
        const button = document.getElementById('email-otp-submit-btn');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Verifying...';
        $.ajax({
            url: BASE_URL + 'auth/verifyEmailOTP',
            type: 'POST',
            data: JSON.stringify({
                email: registrationData.email,
                otp: o
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    notifications.success('Email verified successfully');
                    registrationData.emailVerified = true;
                    showRegisterStep('phone');
                } else {
                    showError('email-otp-error', response.message || 'Invalid verification code');
                }
            },
            error: function(xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    showError('email-otp-error', response.message || 'An error occurred');
                } catch (e) {
                    showError('email-otp-error', 'Server error. Please try again later.');
                }
            }
        });
    }

    function handleRegisterPhoneSubmit() {
        const pi = document.querySelector('#phone');
        const iti = window.intlTelInputGlobals.getInstance(pi);
        if (!iti.isValidNumber()) {
            showError('register-phone-error', 'Please enter a valid phone number');
            return;
        }
        const pn = iti.getNumber();
        hideError('register-phone-error');
        const button = document.getElementById('register-phone-submit-btn');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Checking...';
        $.ajax({
            url: BASE_URL + 'auth/checkPhone',
            type: 'POST',
            data: JSON.stringify({
                phone: pn
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    registrationData.phone = pn;
                    showRegisterStep('password');
                } else {
                    showError('register-phone-error', response.message || 'Phone number is already registered');
                }
            },
            error: function(xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    showError('register-phone-error', response.message || 'An error occurred');
                } catch (e) {
                    showError('register-phone-error', 'Server error. Please try again later.');
                }
            }
        });
    }

    function handleRegisterPasswordSubmit() {
        const p = document.getElementById('register-password').value;
        const c = document.getElementById('register-confirm-password').value;
        const termsChecked = document.getElementById('terms-checkbox').checked;
        if (!p) {
            showError('register-password-error', 'Please create a password');
            return;
        }
        if (!isStrongPassword(p)) {
            showError('register-password-error', 'Password must be at least 8 characters with uppercase, lowercase, number, and special character');
            return;
        }
        if (p !== c) {
            showError('register-password-error', 'Passwords do not match');
            return;
        }
        if (!termsChecked) {
            showError('register-password-error', 'You must agree to the Terms of Service and Privacy Policy');
            return;
        }
        hideError('register-password-error');
        const button = document.getElementById('register-password-submit-btn');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating account...';
        $.ajax({
            url: BASE_URL + 'auth/register',
            type: 'POST',
            data: JSON.stringify({
                username: registrationData.username,
                email: registrationData.email,
                phone: registrationData.phone,
                password: p
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    notifications.success('Account created successfully! Redirecting...');
                    setTimeout(() => {
                        closeAuthModal();
                        response.redirect ? window.location.href = response.redirect : window.location.reload();
                    }, 1500);
                } else {
                    showError('register-password-error', response.message || 'Registration failed');
                }
            },
            error: function(xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    showError('register-password-error', response.message || 'An error occurred');
                } catch (e) {
                    showError('register-password-error', 'Server error. Please try again later.');
                }
            }
        });
    }

    function handleForgotEmailSubmit() {
        const e = document.getElementById('forgot-email').value;
        if (!e) {
            showError('forgot-email-error', 'Please enter your email address');
            return;
        }
        if (!isValidEmail(e)) {
            showError('forgot-email-error', 'Please enter a valid email address');
            return;
        }
        hideError('forgot-email-error');
        const button = document.getElementById('forgot-email-submit-btn');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
        $.ajax({
            url: BASE_URL + 'auth/sendResetEmail',
            type: 'POST',
            data: JSON.stringify({
                email: e
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    notifications.success('Reset code sent to your email');
                    forgotPasswordData.email = e;
                    forgotPasswordData.contact = e;
                    document.getElementById('reset-contact-display').textContent = e;
                    document.getElementById('reset-back-link').onclick = function() {
                        showForgotPasswordForm('email');
                    };
                    startOTPTimer('reset-otp', 120);
                    showResetVerifyForm();
                    $('.otp-input[data-otp-target="reset-otp"]').val('');
                    document.getElementById('reset-otp').value = '';
                } else {
                    showError('forgot-email-error', response.message || 'Email not found');
                }
            },
            error: function(xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    showError('forgot-email-error', response.message || 'An error occurred');
                } catch (e) {
                    showError('forgot-email-error', 'Server error. Please try again later.');
                }
            }
        });
    }

    function handleResetOTPSubmit() {
        const o = document.getElementById('reset-otp').value;
        if (!o || o.length !== 6) {
            showError('reset-otp-error', 'Please enter the complete verification code');
            return;
        }
        hideError('reset-otp-error');
        const button = document.getElementById('reset-otp-submit-btn');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Verifying...';
        $.ajax({
            url: BASE_URL + 'auth/verifyResetOTP',
            type: 'POST',
            data: JSON.stringify({
                contact: forgotPasswordData.contact,
                contactType: resetMethod,
                otp: o
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    notifications.success('Code verified successfully');
                    forgotPasswordData.otpVerified = true;
                    showResetPasswordForm();
                } else {
                    showError('reset-otp-error', response.message || 'Invalid verification code');
                }
            },
            error: function(xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    showError('reset-otp-error', response.message || 'An error occurred');
                } catch (e) {
                    showError('reset-otp-error', 'Server error. Please try again later.');
                }
            }
        });
    }

    function handleResetPasswordSubmit() {
        const np = document.getElementById('new-password').value;
        const cp = document.getElementById('confirm-new-password').value;
        if (!np) {
            showError('reset-password-error', 'Please create a new password');
            return;
        }
        if (!isStrongPassword(np)) {
            showError('reset-password-error', 'Password must be at least 8 characters with uppercase, lowercase, number, and special character');
            return;
        }
        if (np !== cp) {
            showError('reset-password-error', 'Passwords do not match');
            return;
        }
        hideError('reset-password-error');
        const button = document.getElementById('reset-password-submit-btn');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Resetting password...';
        $.ajax({
            url: BASE_URL + 'auth/resetPassword',
            type: 'POST',
            data: JSON.stringify({
                contact: forgotPasswordData.contact,
                contactType: resetMethod,
                password: np
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    notifications.success('Password reset successfully!');
                    setTimeout(() => {
                        showLoginStep('username');
                        notifications.info('Please login with your new password');
                    }, 1500);
                } else {
                    showError('reset-password-error', response.message || 'Password reset failed');
                }
            },
            error: function(xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    showError('reset-password-error', response.message || 'An error occurred');
                } catch (e) {
                    showError('reset-password-error', 'Server error. Please try again later.');
                }
            }
        });
    }

    function showError(elementId, message) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
        }
    }

    function hideError(elementId) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.classList.add('hidden');
        }
    }

    function isValidEmail(email) {
        const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }

    function isStrongPassword(password) {
        return (password.length >= 8 &&
            /[A-Z]/.test(password) &&
            /[a-z]/.test(password) &&
            /[0-9]/.test(password) &&
            /[^A-Za-z0-9]/.test(password));
    }

    // Delegated event for Resend Email OTP
    $(document).on('click', '#resend-email-otp', function() {
        const button = $(this);
        if (button.prop('disabled')) return;
        console.log("Resend email OTP clicked");
        const originalText = button.html();
        button.prop('disabled', true).addClass('text-gray-400').html('<i class="fas fa-spinner fa-spin mr-1"></i>Resending...');
        $.ajax({
            url: BASE_URL + 'auth/sendEmailOTP',
            type: 'POST',
            data: JSON.stringify({
                email: registrationData.email
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    startOTPTimer('email-otp', 120);
                    notifications.success('Verification code resent to your email');
                } else {
                    button.prop('disabled', false).removeClass('text-gray-400');
                    notifications.error(response.message || 'Failed to resend code');
                }
                button.html(originalText);
            },
            error: function() {
                button.prop('disabled', false).removeClass('text-gray-400').html(originalText);
                notifications.error('Failed to resend code. Please try again.');
            }
        });
    });

    // Delegated event for Resend Reset OTP
    $(document).on('click', '#resend-reset-otp', function() {
        const button = $(this);
        if (button.prop('disabled')) return;
        console.log("Resend reset OTP clicked");
        if (!resetMethod) {
            resetMethod = 'email';
        }
        const originalText = button.html();
        button.prop('disabled', true).addClass('text-gray-400').html('<i class="fas fa-spinner fa-spin mr-1"></i>Resending...');
        const endpoint = resetMethod === 'email' ? 'sendResetEmail' : 'sendResetPhone';
        const dataPayload = resetMethod === 'email' ? {
            email: forgotPasswordData.email
        } : {
            phone: forgotPasswordData.phone
        };
        $.ajax({
            url: BASE_URL + 'auth/' + endpoint,
            type: 'POST',
            data: JSON.stringify(dataPayload),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    startOTPTimer('reset-otp', 120);
                    notifications.success('Verification code resent');
                } else {
                    button.prop('disabled', false).removeClass('text-gray-400');
                    notifications.error(response.message || 'Failed to resend code');
                }
                button.html(originalText);
            },
            error: function() {
                button.prop('disabled', false).removeClass('text-gray-400').html(originalText);
                notifications.error('Failed to resend code. Please try again.');
            }
        });
    });
</script>