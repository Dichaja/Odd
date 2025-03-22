<?php
function generateOTP($length = 6)
{
    $digits = '0123456789';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $digits[rand(0, strlen($digits) - 1)];
    }
    return $otp;
}
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
function isValidPhone($phone)
{
    return preg_match('/^\+[0-9]{10,15}$/', $phone);
}
function isStrongPassword($password)
{
    return (strlen($password) >= 8 &&
        preg_match('/[A-Z]/', $password) &&
        preg_match('/[a-z]/', $password) &&
        preg_match('/[0-9]/', $password) &&
        preg_match('/[^A-Za-z0-9]/', $password));
}
function sendEmailOTP($email, $otp)
{
    return true;
}
function sendSMSOTP($phone, $otp)
{
    return true;
}
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
<!-- Login: Username -->
<div id="login-step-username" class="auth-form active">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('login', 'username') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">Don't have an account?
            <a href="javascript:void(0)" onclick="showRegisterStep('username')" class="text-primary hover:text-red-700 font-medium">Create Account</a>
        </p>
        <form id="login-username-form" class="space-y-4" autocomplete="off" data-mode="login" data-step="username">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="login-username" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your username or email" autofocus autocomplete="off">
                </div>
            </div>
            <button type="button" onclick="handleLoginUsernameSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Continue</button>
        </form>
    </div>
</div>
<!-- Login: Password -->
<div id="login-step-password" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('login', 'password') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showLoginStep('username')" class="text-primary hover:text-red-700 font-medium"><i class="fas fa-arrow-left mr-2"></i>Back</a>
        </p>
        <form id="login-password-form" class="space-y-4" autocomplete="off" data-mode="login" data-step="password">
            <div class="relative">
                <p class="mb-4">Logging in as <strong id="login-username-display"></strong></p>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your password" id="login-password" autofocus autocomplete="off">
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePasswordVisibility('login-password')"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <div class="flex items-center justify-end">
                <a href="javascript:void(0)" onclick="showForgotPasswordOptions()" class="text-sm text-primary hover:text-red-700">Forgot Password?</a>
            </div>
            <button type="button" onclick="handleLoginPasswordSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Login</button>
        </form>
    </div>
</div>
<!-- Register: Username -->
<div id="register-step-username" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'username') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">Already have an account?
            <a href="javascript:void(0)" onclick="showLoginStep('username')" class="text-primary hover:text-red-700 font-medium">Sign In</a>
        </p>
        <form id="register-username-form" class="space-y-4" autocomplete="off" data-mode="register" data-step="username">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="register-username" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Choose a username (letters only)" autofocus autocomplete="off" minlength="3" pattern="[a-zA-Z]+">
                </div>
                <p class="text-xs text-gray-500 mt-1">Username must be at least 3 characters and contain only letters.</p>
            </div>
            <button type="button" onclick="handleRegisterUsernameSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Continue</button>
        </form>
    </div>
</div>
<!-- Register: Email -->
<div id="register-step-email" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'email') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showRegisterStep('username')" class="text-primary hover:text-red-700 font-medium"><i class="fas fa-arrow-left mr-2"></i>Back</a>
        </p>
        <form id="register-email-form" class="space-y-4" autocomplete="off" data-mode="register" data-step="email">
            <div>
                <p class="mb-4">Creating account for <strong id="register-username-display"></strong></p>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="email" id="register-email" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your email" autofocus autocomplete="off">
                </div>
            </div>
            <button type="button" onclick="handleRegisterEmailSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Continue</button>
        </form>
    </div>
</div>
<!-- Register: Email Verification -->
<div id="register-step-email-verify" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'email-verify') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showRegisterStep('email')" class="text-primary hover:text-red-700 font-medium"><i class="fas fa-arrow-left mr-2"></i>Back</a>
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
                <p class="mt-2 text-sm text-gray-500 text-center">
                    Didn't receive the code?
                    <button type="button" id="resend-email-otp" class="text-primary hover:text-red-700 text-sm">Resend</button>
                    <span id="email-otp-timer" class="text-sm"></span>
                </p>
                <p class="mt-2 text-xs text-gray-500 text-center">For demo purposes, the code is: <span id="demo-email-otp"></span></p>
            </div>
            <button type="button" onclick="handleEmailOTPSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Verify Email</button>
        </form>
    </div>
</div>
<!-- Register: Phone -->
<div id="register-step-phone" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'phone') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showRegisterStep('email-verify')" class="text-primary hover:text-red-700 font-medium"><i class="fas fa-arrow-left mr-2"></i>Back</a>
        </p>
        <form id="register-phone-form" class="space-y-4" autocomplete="off" data-mode="register" data-step="phone">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <input type="tel" id="phone" name="phone" required placeholder="Phone Number" class="w-full py-2 border border-gray-300 rounded-lg" autofocus autocomplete="off">
            </div>
            <button type="button" onclick="handleRegisterPhoneSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Continue</button>
        </form>
    </div>
</div>
<!-- Register: Phone Verification -->
<div id="register-step-phone-verify" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'phone-verify') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showRegisterStep('phone')" class="text-primary hover:text-red-700 font-medium"><i class="fas fa-arrow-left mr-2"></i>Back</a>
        </p>
        <form id="register-phone-verify-form" class="space-y-4" autocomplete="off" data-mode="register" data-step="phone-verify">
            <div>
                <p class="mb-4 text-center">We've sent a verification code to <strong id="register-phone-display"></strong></p>
                <p class="text-sm text-gray-500 mt-1 text-center mb-4">Enter the 6-digit code below</p>
                <div class="flex justify-between gap-2 mb-2">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="phone-otp" autofocus>
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="phone-otp">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="phone-otp">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="phone-otp">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="phone-otp">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="phone-otp">
                </div>
                <input type="hidden" id="phone-otp" value="">
                <p class="mt-2 text-sm text-gray-500 text-center">
                    Didn't receive the code?
                    <button type="button" id="resend-phone-otp" class="text-primary hover:text-red-700 text-sm">Resend</button>
                    <span id="phone-otp-timer" class="text-sm"></span>
                </p>
                <p class="mt-2 text-xs text-gray-500 text-center">For demo purposes, the code is: <span id="demo-phone-otp"></span></p>
            </div>
            <button type="button" onclick="handlePhoneOTPSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Verify Phone</button>
        </form>
    </div>
</div>
<!-- Register: Password -->
<div id="register-step-password" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'password') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showRegisterStep('phone-verify')" class="text-primary hover:text-red-700 font-medium"><i class="fas fa-arrow-left mr-2"></i>Back</a>
        </p>
        <form id="register-password-form" class="space-y-4" autocomplete="off" data-mode="register" data-step="password">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Create a password" id="register-password" autofocus autocomplete="new-password" oninput="checkPasswordStrength(this.value)">
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePasswordVisibility('register-password')"><i class="fas fa-eye"></i></button>
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
                    <input type="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Confirm your password" id="register-confirm-password" autocomplete="new-password">
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePasswordVisibility('register-confirm-password')"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <div class="flex items-start">
                <input type="checkbox" required class="mt-1 rounded border-gray-300 text-primary focus:ring-primary">
                <span class="ml-2 text-sm text-gray-600">I agree to the
                    <a href="terms-and-conditions" class="text-primary hover:text-red-700">Terms of Service</a> and
                    <a href="#" class="text-primary hover:text-red-700">Privacy Policy</a>
                </span>
            </div>
            <button type="button" onclick="handleRegisterPasswordSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Create Account</button>
        </form>
    </div>
</div>
<!-- Forgot Password: Options -->
<div id="forgot-password-options" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('forgot_password', 'options') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showLoginStep('password')" class="text-primary hover:text-red-700 font-medium"><i class="fas fa-arrow-left mr-2"></i>Back</a>
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
                        <input type="radio" name="reset_method" value="phone" class="mr-3 text-primary focus:ring-primary">
                        <div>
                            <p class="font-medium">Phone</p>
                            <p class="text-sm text-gray-500">Receive a verification code via SMS</p>
                        </div>
                    </label>
                </div>
            </div>
            <button type="button" onclick="handleForgotPasswordMethodSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Continue</button>
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
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showForgotPasswordOptions()" class="text-primary hover:text-red-700 font-medium"><i class="fas fa-arrow-left mr-2"></i>Back</a>
        </p>
        <form id="forgot-password-email-form-element" class="space-y-4" autocomplete="off" data-mode="forgot_password" data-step="email">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="email" id="forgot-email" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your email" autofocus autocomplete="off">
                </div>
            </div>
            <button type="button" onclick="handleForgotEmailSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Send Reset Code</button>
        </form>
    </div>
</div>
<!-- Forgot Password: Phone Form -->
<div id="forgot-password-phone-form" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('forgot_password', 'phone-form') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showForgotPasswordOptions()" class="text-primary hover:text-red-700 font-medium"><i class="fas fa-arrow-left mr-2"></i>Back</a>
        </p>
        <form id="forgot-password-phone-form-element" class="space-y-4" autocomplete="off" data-mode="forgot_password" data-step="phone">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <input type="tel" id="forgot-phone" name="forgot-phone" required placeholder="Phone Number" class="w-full py-2 border border-gray-300 rounded-lg" autofocus autocomplete="off">
            </div>
            <button type="button" onclick="handleForgotPhoneSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Send Reset Code</button>
        </form>
    </div>
</div>
<!-- Reset Password: Verify OTP -->
<div id="reset-password-verify" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('reset_password', 'verify') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" id="reset-back-link" class="text-primary hover:text-red-700 font-medium"><i class="fas fa-arrow-left mr-2"></i>Back</a>
        </p>
        <form id="reset-verify-form" class="space-y-4" autocomplete="off" data-mode="reset_password" data-step="verify_otp">
            <div>
                <p class="mb-4 text-center">We've sent a verification code to <strong id="reset-contact-display"></strong></p>
                <p class="text-sm text-gray-500 mt-1 text-center mb-4">Enter the 6-digit code below</p>
                <div class="flex justify-between gap-2 mb-2">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="reset-otp" autofocus>
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="reset-otp">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="reset-otp">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="reset-otp">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="reset-otp">
                    <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="reset-otp">
                </div>
                <input type="hidden" id="reset-otp" value="">
                <p class="mt-2 text-sm text-gray-500 text-center">
                    Didn't receive the code?
                    <button type="button" id="resend-reset-otp" class="text-primary hover:text-red-700 text-sm">Resend</button>
                    <span id="reset-otp-timer" class="text-sm"></span>
                </p>
                <p class="mt-2 text-xs text-gray-500 text-center">For demo purposes, the code is: <span id="demo-reset-otp"></span></p>
            </div>
            <button type="button" onclick="handleResetOTPSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Verify Code</button>
        </form>
    </div>
</div>
<!-- Reset Password: New Password -->
<div id="reset-password-form" class="auth-form" style="display:none">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('reset_password', 'form') ?></h2>
            <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
        </div>
        <p class="mb-4 text-center text-sm text-gray-600">
            <a href="javascript:void(0)" onclick="showResetVerifyForm()" class="text-primary hover:text-red-700 font-medium"><i class="fas fa-arrow-left mr-2"></i>Back</a>
        </p>
        <form id="reset-password-form-element" class="space-y-4" autocomplete="off" data-mode="reset_password" data-step="new_password">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter new password" id="new-password" autofocus autocomplete="new-password" oninput="checkPasswordStrength(this.value,'new-password')">
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePasswordVisibility('new-password')"><i class="fas fa-eye"></i></button>
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
                    <input type="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Confirm new password" id="confirm-new-password" autocomplete="new-password">
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePasswordVisibility('confirm-new-password')"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <button type="button" onclick="handleResetPasswordSubmit()" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Reset Password</button>
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
                    }
                }
            });
        });
    })();
    let registrationData = {};
    let loginData = {};
    let forgotPasswordData = {};
    let resetMethod = '';
    let emailOTP = '';
    let phoneOTP = '';
    let resetOTP = '';
    let emailOTPTimer;
    let phoneOTPTimer;
    let resetOTPTimer;

    function showLoginStep(step) {
        const steps = ['username', 'password'];
        steps.forEach(s => {
            const el = document.getElementById('login-step-' + s);
            if (s === step) {
                el.style.display = 'block';
                el.classList.add('active');
            } else {
                el.classList.remove('active');
                setTimeout(() => {
                    el.style.display = 'none';
                }, 300);
            }
        });
        const regSteps = ['username', 'email', 'email-verify', 'phone', 'phone-verify', 'password'];
        regSteps.forEach(s => {
            const el = document.getElementById('register-step-' + s);
            el.classList.remove('active');
            setTimeout(() => {
                el.style.display = 'none';
            }, 300);
        });
        const forgotSteps = ['options', 'email-form', 'phone-form'];
        forgotSteps.forEach(s => {
            const el = document.getElementById('forgot-password-' + s);
            if (el) {
                el.classList.remove('active');
                setTimeout(() => {
                    el.style.display = 'none';
                }, 300);
            }
        });
        document.getElementById('reset-password-verify').classList.remove('active');
        setTimeout(() => {
            document.getElementById('reset-password-verify').style.display = 'none';
        }, 300);
        document.getElementById('reset-password-form').classList.remove('active');
        setTimeout(() => {
            document.getElementById('reset-password-form').style.display = 'none';
        }, 300);
    }

    function showRegisterStep(step) {
        const regSteps = ['username', 'email', 'email-verify', 'phone', 'phone-verify', 'password'];
        regSteps.forEach(s => {
            const el = document.getElementById('register-step-' + s);
            if (s === step) {
                el.style.display = 'block';
                el.classList.add('active');
            } else {
                el.classList.remove('active');
                setTimeout(() => {
                    el.style.display = 'none';
                }, 300);
            }
        });
        const loginSteps = ['username', 'password'];
        loginSteps.forEach(s => {
            const el = document.getElementById('login-step-' + s);
            el.classList.remove('active');
            setTimeout(() => {
                el.style.display = 'none';
            }, 300);
        });
        const forgotSteps = ['options', 'email-form', 'phone-form'];
        forgotSteps.forEach(s => {
            const el = document.getElementById('forgot-password-' + s);
            if (el) {
                el.classList.remove('active');
                setTimeout(() => {
                    el.style.display = 'none';
                }, 300);
            }
        });
        document.getElementById('reset-password-verify').classList.remove('active');
        setTimeout(() => {
            document.getElementById('reset-password-verify').style.display = 'none';
        }, 300);
        document.getElementById('reset-password-form').classList.remove('active');
        setTimeout(() => {
            document.getElementById('reset-password-form').style.display = 'none';
        }, 300);
    }

    function showForgotPasswordOptions() {
        const loginSteps = ['username', 'password'];
        loginSteps.forEach(s => {
            const el = document.getElementById('login-step-' + s);
            el.classList.remove('active');
            setTimeout(() => {
                el.style.display = 'none';
            }, 300);
        });
        const regSteps = ['username', 'email', 'email-verify', 'phone', 'phone-verify', 'password'];
        regSteps.forEach(s => {
            const el = document.getElementById('register-step-' + s);
            el.classList.remove('active');
            setTimeout(() => {
                el.style.display = 'none';
            }, 300);
        });
        const opt = document.getElementById('forgot-password-options');
        opt.style.display = 'block';
        opt.classList.add('active');
        document.getElementById('forgot-password-email-form').classList.remove('active');
        setTimeout(() => {
            document.getElementById('forgot-password-email-form').style.display = 'none';
        }, 300);
        document.getElementById('forgot-password-phone-form').classList.remove('active');
        setTimeout(() => {
            document.getElementById('forgot-password-phone-form').style.display = 'none';
        }, 300);
        document.getElementById('reset-password-verify').classList.remove('active');
        setTimeout(() => {
            document.getElementById('reset-password-verify').style.display = 'none';
        }, 300);
        document.getElementById('reset-password-form').classList.remove('active');
        setTimeout(() => {
            document.getElementById('reset-password-form').style.display = 'none';
        }, 300);
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
            showForgotPasswordForm('phone');
        }
    }

    function showForgotPasswordForm(m) {
        resetMethod = m;
        document.getElementById('forgot-password-options').classList.remove('active');
        setTimeout(() => {
            document.getElementById('forgot-password-options').style.display = 'none';
        }, 300);
        if (m === 'email') {
            const ef = document.getElementById('forgot-password-email-form');
            ef.style.display = 'block';
            ef.classList.add('active');
            document.getElementById('forgot-password-phone-form').classList.remove('active');
            setTimeout(() => {
                document.getElementById('forgot-password-phone-form').style.display = 'none';
            }, 300);
        } else if (m === 'phone') {
            const pf = document.getElementById('forgot-password-phone-form');
            pf.style.display = 'block';
            pf.classList.add('active');
            document.getElementById('forgot-password-email-form').classList.remove('active');
            setTimeout(() => {
                document.getElementById('forgot-password-email-form').style.display = 'none';
            }, 300);
        }
    }

    function showResetVerifyForm() {
        document.getElementById('forgot-password-email-form').classList.remove('active');
        setTimeout(() => {
            document.getElementById('forgot-password-email-form').style.display = 'none';
        }, 300);
        document.getElementById('forgot-password-phone-form').classList.remove('active');
        setTimeout(() => {
            document.getElementById('forgot-password-phone-form').style.display = 'none';
        }, 300);
        const rv = document.getElementById('reset-password-verify');
        rv.style.display = 'block';
        rv.classList.add('active');
        document.getElementById('reset-back-link').onclick = function() {
            showForgotPasswordForm(resetMethod);
        };
        document.getElementById('reset-password-form').classList.remove('active');
        setTimeout(() => {
            document.getElementById('reset-password-form').style.display = 'none';
        }, 300);
    }

    function showResetPasswordForm() {
        document.getElementById('reset-password-verify').classList.remove('active');
        setTimeout(() => {
            document.getElementById('reset-password-verify').style.display = 'none';
        }, 300);
        const rf = document.getElementById('reset-password-form');
        rf.style.display = 'block';
        rf.classList.add('active');
    }

    function startOTPTimer(type, seconds) {
        let r = seconds;
        const te = document.getElementById(type + '-timer');
        const rb = document.getElementById('resend-' + type);
        rb.disabled = true;
        rb.classList.add('text-gray-400');
        let iv = setInterval(() => {
            r--;
            const m = Math.floor(r / 60);
            const s = r % 60;
            te.textContent = `(${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')})`;
            if (r <= 0) {
                clearInterval(iv);
                te.textContent = '';
                rb.disabled = false;
                rb.classList.remove('text-gray-400');
            }
        }, 1000);
        if (type === 'email-otp') {
            if (emailOTPTimer) clearInterval(emailOTPTimer);
            emailOTPTimer = iv;
        } else if (type === 'phone-otp') {
            if (phoneOTPTimer) clearInterval(phoneOTPTimer);
            phoneOTPTimer = iv;
        } else if (type === 'reset-otp') {
            if (resetOTPTimer) clearInterval(resetOTPTimer);
            resetOTPTimer = iv;
        }
    }

    function setupOTPInputs() {
        const inputs = document.querySelectorAll('.otp-input');
        inputs.forEach((inp, index) => {
            inp.addEventListener('keyup', (e) => {
                const t = inp.getAttribute('data-otp-target');
                if (inp.value.length === 1 && index < 5) {
                    inputs[index + 1].focus();
                }
                if (e.key === 'Backspace' && index > 0 && inp.value.length === 0) {
                    inputs[index - 1].focus();
                }
                updateOTPValue(t);
                const allFilled = Array.from(document.querySelectorAll(`.otp-input[data-otp-target="${t}"]`)).every(i => i.value.length === 1);
                if (allFilled) {
                    if (t === 'email-otp') {
                        handleEmailOTPSubmit();
                    } else if (t === 'phone-otp') {
                        handlePhoneOTPSubmit();
                    } else if (t === 'reset-otp') {
                        handleResetOTPSubmit();
                    }
                }
            });
            inp.addEventListener('input', () => {
                inp.value = inp.value.replace(/[^0-9]/g, '');
            });
            inp.addEventListener('paste', (e) => {
                e.preventDefault();
                const t = inp.getAttribute('data-otp-target');
                const pd = (e.clipboardData || window.clipboardData).getData('text');
                const all = document.querySelectorAll(`.otp-input[data-otp-target="${t}"]`);
                for (let i = 0; i < Math.min(pd.length, all.length); i++) {
                    if (/[0-9]/.test(pd[i])) {
                        all[i].value = pd[i];
                    }
                }
                updateOTPValue(t);
                const li = Math.min(pd.length - 1, all.length - 1);
                if (li < all.length - 1) {
                    all[li + 1].focus();
                } else {
                    all[li].focus();
                }
                const af = Array.from(all).every(i => i.value.length === 1);
                if (af) {
                    if (t === 'email-otp') {
                        handleEmailOTPSubmit();
                    } else if (t === 'phone-otp') {
                        handlePhoneOTPSubmit();
                    } else if (t === 'reset-otp') {
                        handleResetOTPSubmit();
                    }
                }
            });
        });
    }

    function updateOTPValue(t) {
        const ai = document.querySelectorAll(`.otp-input[data-otp-target="${t}"]`);
        const val = Array.from(ai).map(i => i.value).join('');
        document.getElementById(t).value = val;
    }
    // LOGIN
    function handleLoginUsernameSubmit() {
        const u = document.getElementById('login-username').value;
        if (!u) {
            notifications.error('Please enter your username or email');
            return;
        }
        loginData.username = u;
        document.getElementById('login-username-display').textContent = u;
        showLoginStep('password');
    }

    function handleLoginPasswordSubmit() {
        const p = document.getElementById('login-password').value;
        if (!p) {
            notifications.error('Please enter your password');
            return;
        }
        loginData.password = p;
        notifications.success('Login successful! Redirecting...');
        setTimeout(() => {
            closeAuthModal();
            notifications.info('Welcome back!');
        }, 1500);
    }
    // REGISTER
    function handleRegisterUsernameSubmit() {
        const u = document.getElementById('register-username').value;
        if (!u) {
            notifications.error('Please choose a username');
            return;
        }
        if (u.length < 3) {
            notifications.error('Username must be at least 3 characters long');
            return;
        }
        registrationData.username = u;
        document.getElementById('register-username-display').textContent = u;
        showRegisterStep('email');
    }

    function handleRegisterEmailSubmit() {
        const e = document.getElementById('register-email').value;
        if (!e) {
            notifications.error('Please enter your email address');
            return;
        }
        if (!isValidEmail(e)) {
            notifications.error('Please enter a valid email address');
            return;
        }
        registrationData.email = e;
        document.getElementById('register-email-display').textContent = e;
        emailOTP = generateOTP();
        document.getElementById('demo-email-otp').textContent = emailOTP;
        sendEmailOTP(e, emailOTP);
        startOTPTimer('email-otp', 120);
        showRegisterStep('email-verify');
        document.querySelectorAll('.otp-input[data-otp-target="email-otp"]').forEach(i => {
            i.value = '';
        });
        document.getElementById('email-otp').value = '';
    }

    function handleEmailOTPSubmit() {
        const o = document.getElementById('email-otp').value;
        if (!o || o.length !== 6) {
            notifications.error('Please enter the complete verification code');
            return;
        }
        if (o !== emailOTP) {
            notifications.error('Invalid verification code');
            return;
        }
        showRegisterStep('phone');
    }

    function handleRegisterPhoneSubmit() {
        const pi = document.querySelector('#phone');
        const iti = window.intlTelInputGlobals.getInstance(pi);
        if (!iti.isValidNumber()) {
            notifications.error('Please enter a valid phone number');
            return;
        }
        const pn = iti.getNumber();
        registrationData.phone = pn;
        document.getElementById('register-phone-display').textContent = pn;
        phoneOTP = generateOTP();
        document.getElementById('demo-phone-otp').textContent = phoneOTP;
        sendSMSOTP(pn, phoneOTP);
        startOTPTimer('phone-otp', 120);
        showRegisterStep('phone-verify');
        document.querySelectorAll('.otp-input[data-otp-target="phone-otp"]').forEach(i => {
            i.value = '';
        });
        document.getElementById('phone-otp').value = '';
    }

    function handlePhoneOTPSubmit() {
        const o = document.getElementById('phone-otp').value;
        if (!o || o.length !== 6) {
            notifications.error('Please enter the complete verification code');
            return;
        }
        if (o !== phoneOTP) {
            notifications.error('Invalid verification code');
            return;
        }
        showRegisterStep('password');
    }

    function handleRegisterPasswordSubmit() {
        const p = document.getElementById('register-password').value;
        const c = document.getElementById('register-confirm-password').value;
        if (!p) {
            notifications.error('Please create a password');
            return;
        }
        if (!isStrongPassword(p)) {
            notifications.error('Password must be at least 8 characters with uppercase, lowercase, number, and special character');
            return;
        }
        if (p !== c) {
            notifications.error('Passwords do not match');
            return;
        }
        registrationData.password = p;
        notifications.success('Account created successfully! Redirecting...');
        setTimeout(() => {
            closeAuthModal();
            notifications.info('Welcome to Zzimba Online!');
        }, 1500);
    }
    // FORGOT PASSWORD
    function handleForgotEmailSubmit() {
        const e = document.getElementById('forgot-email').value;
        if (!e) {
            notifications.error('Please enter your email address');
            return;
        }
        if (!isValidEmail(e)) {
            notifications.error('Please enter a valid email address');
            return;
        }
        forgotPasswordData.email = e;
        forgotPasswordData.contact = e;
        resetOTP = generateOTP();
        document.getElementById('demo-reset-otp').textContent = resetOTP;
        sendEmailOTP(e, resetOTP);
        document.getElementById('reset-contact-display').textContent = e;
        document.getElementById('reset-back-link').onclick = function() {
            showForgotPasswordForm('email');
        };
        startOTPTimer('reset-otp', 120);
        showResetVerifyForm();
        document.querySelectorAll('.otp-input[data-otp-target="reset-otp"]').forEach(i => {
            i.value = '';
        });
        document.getElementById('reset-otp').value = '';
    }

    function handleForgotPhoneSubmit() {
        const pi = document.querySelector('#forgot-phone');
        const iti = window.intlTelInputGlobals.getInstance(pi);
        if (!iti.isValidNumber()) {
            notifications.error('Please enter a valid phone number');
            return;
        }
        const pn = iti.getNumber();
        forgotPasswordData.phone = pn;
        forgotPasswordData.contact = pn;
        resetOTP = generateOTP();
        document.getElementById('demo-reset-otp').textContent = resetOTP;
        sendSMSOTP(pn, resetOTP);
        document.getElementById('reset-contact-display').textContent = pn;
        document.getElementById('reset-back-link').onclick = function() {
            showForgotPasswordForm('phone');
        };
        startOTPTimer('reset-otp', 120);
        showResetVerifyForm();
        document.querySelectorAll('.otp-input[data-otp-target="reset-otp"]').forEach(i => {
            i.value = '';
        });
        document.getElementById('reset-otp').value = '';
    }
    // RESET PASSWORD
    function handleResetOTPSubmit() {
        const o = document.getElementById('reset-otp').value;
        if (!o || o.length !== 6) {
            notifications.error('Please enter the complete verification code');
            return;
        }
        if (o !== resetOTP) {
            notifications.error('Invalid verification code');
            return;
        }
        showResetPasswordForm();
    }

    function handleResetPasswordSubmit() {
        const np = document.getElementById('new-password').value;
        const cp = document.getElementById('confirm-new-password').value;
        if (!np) {
            notifications.error('Please create a new password');
            return;
        }
        if (!isStrongPassword(np)) {
            notifications.error('Password must be at least 8 characters with uppercase, lowercase, number, and special character');
            return;
        }
        if (np !== cp) {
            notifications.error('Passwords do not match');
            return;
        }
        notifications.success('Password reset successfully!');
        setTimeout(() => {
            showLoginStep('username');
            notifications.info('Please login with your new password');
        }, 1500);
    }
    document.addEventListener('DOMContentLoaded', () => {
        setupOTPInputs();
        document.getElementById('resend-email-otp').addEventListener('click', () => {
            emailOTP = generateOTP();
            document.getElementById('demo-email-otp').textContent = emailOTP;
            sendEmailOTP(registrationData.email, emailOTP);
            startOTPTimer('email-otp', 120);
            notifications.info('Verification code resent to your email');
        });
        document.getElementById('resend-phone-otp').addEventListener('click', () => {
            phoneOTP = generateOTP();
            document.getElementById('demo-phone-otp').textContent = phoneOTP;
            sendSMSOTP(registrationData.phone, phoneOTP);
            startOTPTimer('phone-otp', 120);
            notifications.info('Verification code resent to your phone');
        });
        document.getElementById('resend-reset-otp').addEventListener('click', () => {
            resetOTP = generateOTP();
            document.getElementById('demo-reset-otp').textContent = resetOTP;
            if (resetMethod === 'email') {
                sendEmailOTP(forgotPasswordData.email, resetOTP);
            } else {
                sendSMSOTP(forgotPasswordData.phone, resetOTP);
            }
            startOTPTimer('reset-otp', 120);
            notifications.info('Verification code resent');
        });
    });
</script>