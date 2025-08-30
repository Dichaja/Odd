<?php
function getStepTitle($mode, $step)
{
    $titles = [
        'login' => [
            'identifier' => 'Login',
            'password' => 'Enter Password'
        ],
        'register' => [
            'username' => 'Create Account',
            'verification-method' => 'Choose Verification Method',
            'email' => 'Enter Email',
            'email-verify' => 'Verify Email',
            'phone' => 'Enter Phone Number',
            'phone-verify' => 'Verify Phone',
            'password' => 'Create Password'
        ],
        'forgot_password' => [
            'options' => 'Forgot Password',
            'email-form' => 'Verify via Email',
            'phone-form' => 'Verify via Phone'
        ],
        'reset_password' => [
            'verify' => 'Verify Code',
            'form' => 'Reset Password'
        ]
    ];
    return $titles[$mode][$step] ?? 'Authentication';
}
?>
<div x-data="authUI()" x-init="init()" class="relative">
    <div x-show="step==='start'" :class="step==='start'?'active':''" class="auth-form">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-secondary">Welcome</h2>
                <button @click="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <i data-lucide="user-plus" class="w-6 h-6 text-primary"></i>
                        <div>
                            <p class="font-semibold">New user?</p>
                            <p class="text-sm text-gray-600">Create an account to get started.</p>
                        </div>
                    </div>
                    <button @click="go('register-username')"
                        class="mt-4 w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Create
                        Account</button>
                </div>
                <div class="rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <i data-lucide="log-in" class="w-6 h-6 text-secondary"></i>
                        <div>
                            <p class="font-semibold">Already have an account?</p>
                            <p class="text-sm text-gray-600">Log in to continue.</p>
                        </div>
                    </div>
                    <button @click="go('login-identifier')"
                        class="mt-4 w-full bg-secondary text-white py-2 rounded-lg hover:bg-gray-800 transition-colors">Login</button>
                </div>
            </div>
        </div>
    </div>

    <div id="login-step-identifier" x-show="step==='login-identifier'" :class="step==='login-identifier'?'active':''"
        class="auth-form" style="display:none">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('login', 'identifier') ?></h2>
                <button @click="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <p class="mb-4 text-center text-sm text-gray-600">
                Don't have an account?
                <a href="javascript:void(0)" @click="go('register-username')"
                    class="text-primary hover:text-red-700 font-medium">Create Account</a>
            </p>

            <form id="login-identifier-form" class="space-y-4" autocomplete="off" data-mode="login"
                data-step="identifier" x-data="{ method: 'username' }">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">How would you like to login?</label>

                    <div class="grid grid-cols-3 gap-2 md:gap-3 mb-4">
                        <label class="flex items-center justify-center cursor-pointer p-2 rounded-lg border text-sm"
                            :class="{'border-primary ring-1 ring-primary': method==='username'}">
                            <input type="radio" name="login_method" value="username" class="sr-only" x-model="method">
                            <div class="flex items-center gap-2">
                                <i data-lucide="user" class="w-4 h-4 text-gray-500"></i>
                                <span class="font-medium">Username</span>
                            </div>
                        </label>

                        <label class="flex items-center justify-center cursor-pointer p-2 rounded-lg border text-sm"
                            :class="{'border-primary ring-1 ring-primary': method==='email'}">
                            <input type="radio" name="login_method" value="email" class="sr-only" x-model="method">
                            <div class="flex items-center gap-2">
                                <i data-lucide="mail" class="w-4 h-4 text-gray-500"></i>
                                <span class="font-medium">Email</span>
                            </div>
                        </label>

                        <label class="flex items-center justify-center cursor-pointer p-2 rounded-lg border text-sm"
                            :class="{'border-primary ring-1 ring-primary': method==='phone'}">
                            <input type="radio" name="login_method" value="phone" class="sr-only" x-model="method">
                            <div class="flex items-center gap-2">
                                <i data-lucide="phone" class="w-4 h-4 text-gray-500"></i>
                                <span class="font-medium">Phone</span>
                            </div>
                        </label>
                    </div>

                    <div id="username-input" class="login-input-group" x-show="method==='username'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <div class="relative">
                            <i data-lucide="user"
                                class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" id="login-username" required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                placeholder="Enter your username" autofocus autocomplete="off"
                                onkeyup="checkTripleSpace(this)">
                        </div>
                    </div>

                    <div id="email-input" class="login-input-group" x-show="method==='email'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <div class="relative">
                            <i data-lucide="mail"
                                class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="email" id="login-email" required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                placeholder="Enter your email address" autocomplete="off"
                                onkeyup="checkTripleSpace(this)">
                        </div>
                    </div>

                    <div id="phone-input" class="login-input-group" x-show="method==='phone'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <div class="flex">
                            <div
                                class="flex items-center px-3 py-2 border border-r-0 border-gray-300 rounded-l-lg bg-gray-50">
                                <span class="text-gray-700 font-medium">+256</span>
                            </div>
                            <input type="text" id="login-phone" required maxlength="9" minlength="9"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-primary focus:border-primary"
                                placeholder="7XXXXXXXX" autocomplete="off" oninput="validatePhoneInput(this)"
                                onkeyup="checkTripleSpace(this)">
                        </div>
                    </div>

                    <div id="login-identifier-error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <button type="button" @click="handleLoginIdentifierSubmit()"
                    class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Continue</button>
            </form>
        </div>
    </div>

    <div id="login-step-password" x-show="step==='login-password'" :class="step==='login-password'?'active':''"
        class="auth-form" style="display:none">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('login', 'password') ?></h2>
                <button @click="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <p class="mb-4 text-center text-sm text-gray-600">
                <a href="javascript:void(0)" @click="showLoginStep('identifier')"
                    class="text-primary hover:text-red-700 font-medium">
                    <i data-lucide="arrow-left" class="w-4 h-4 inline-block mr-1"></i>Back
                </a>
            </p>
            <form id="login-password-form" class="space-y-4" autocomplete="off" data-mode="login" data-step="password">
                <div class="relative">
                    <p class="mb-4">Logging in as <strong id="login-identifier-display"></strong></p>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <i data-lucide="lock"
                            class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" required
                            class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                            placeholder="Enter your password" id="login-password" autofocus autocomplete="off"
                            onkeyup="checkTripleSpace(this)">
                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            onclick="togglePasswordVisibility('login-password')"><i data-lucide="eye"
                                class="w-5 h-5"></i></button>
                    </div>
                    <div id="login-password-error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <div class="flex items-center justify-end">
                    <a href="javascript:void(0)" @click="showForgotPasswordOptions()"
                        class="text-sm text-primary hover:text-red-700">Forgot Password?</a>
                </div>
                <button type="button" @click="handleLoginPasswordSubmit()"
                    class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Login</button>
            </form>
        </div>
    </div>

    <div id="empty-password-options" x-show="step==='empty-options'" :class="step==='empty-options'?'active':''"
        class="auth-form" style="display:none">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-secondary">Verify Account</h2>
                <button @click="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <p class="mb-4 text-center text-sm text-gray-600">
                <a href="javascript:void(0)" @click="showLoginStep('password')"
                    class="text-primary hover:text-red-700 font-medium">
                    <i data-lucide="arrow-left" class="w-4 h-4 inline-block mr-1"></i>Back
                </a>
            </p>
            <form id="empty-password-options-form" class="space-y-4" autocomplete="off" data-mode="empty_password"
                data-step="options">
                <div>
                    <p class="mb-4 text-sm text-gray-600">Logging in as <strong id="empty-username-display"></strong>
                    </p>
                    <p class="mb-4 text-sm text-gray-600">Please select how you would like to verify your account
                        ownership:</p>
                    <div class="space-y-3">
                        <label id="empty-password-email-option"
                            class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input id="empty-password-email" type="radio" name="verify_method" value="email"
                                class="mr-3 text-primary focus:ring-primary" checked>
                            <div>
                                <p class="font-medium">Email</p>
                                <p class="text-sm text-gray-500">Receive a verification code via <span
                                        id="empty-password-email-hint"></span></p>
                            </div>
                        </label>
                        <label id="empty-password-phone-option"
                            class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input id="empty-password-phone" type="radio" name="verify_method" value="phone"
                                class="mr-3 text-primary focus:ring-primary">
                            <div>
                                <p class="font-medium">Phone</p>
                                <p class="text-sm text-gray-500">Receive a verification code via <span
                                        id="empty-password-phone-hint"></span></p>
                            </div>
                        </label>
                    </div>
                </div>
                <div id="empty-password-error" class="text-red-500 text-sm mt-1 hidden"></div>
                <button id="empty-password-continue" type="button"
                    class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Continue</button>
                <div id="empty-password-loading" class="text-sm text-gray-500 mt-2 hidden">Sending OTP...</div>
            </form>
            <p class="mt-4 text-center text-sm text-gray-600">Need assistance?
                <a href="javascript:void(0)" @click="showLoginStep('identifier')"
                    class="text-primary hover:text-red-700 font-medium">Contact Support</a>
            </p>
        </div>
    </div>

    <div id="register-step-username" x-show="step==='register-username'" :class="step==='register-username'?'active':''"
        class="auth-form" style="display:none">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'username') ?></h2>
                <button @click="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <p class="mb-4 text-center text-sm text-gray-600">
                Already have an account?
                <a href="javascript:void(0)" @click="showLoginStep('identifier')"
                    class="text-primary hover:text-red-700 font-medium">Sign In</a>
            </p>
            <form id="register-username-form" class="space-y-4" autocomplete="off" data-mode="register"
                data-step="username">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <div class="relative">
                        <i data-lucide="user"
                            class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="register-username" required
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                            placeholder="Choose a username (letters and numbers only)" autofocus autocomplete="off"
                            minlength="3" onkeyup="checkTripleSpace(this)">
                    </div>
                    <div id="register-username-error" class="text-red-500 text-sm mt-1 hidden"></div>
                    <p class="text-xs text-gray-500 mt-1">Username must be at least 3 characters.</p>
                </div>
                <button type="button" @click="handleRegisterUsernameSubmit()"
                    class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Continue</button>
            </form>
        </div>
    </div>

    <div id="register-step-verification-method" x-show="step==='register-verification-method'"
        :class="step==='register-verification-method'?'active':''" class="auth-form" style="display:none">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'verification-method') ?>
                </h2>
                <button @click="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <p class="mb-4 text-center text-sm text-gray-600">
                <a href="javascript:void(0)" @click="showRegisterStep('username')"
                    class="text-primary hover:text-red-700 font-medium">
                    <i data-lucide="arrow-left" class="w-4 h-4 inline-block mr-1"></i>Back
                </a>
            </p>
            <p class="mb-4 text-sm text-gray-600">Creating account for <strong
                    id="register-username-display-method"></strong></p>
            <form id="register-verification-method-form" class="space-y-4" autocomplete="off" data-mode="register"
                data-step="verification-method">
                <div>
                    <p class="mb-4 text-sm text-gray-600">How would you like to verify your identity?</p>
                    <div class="space-y-3">
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="verification_method" value="email"
                                class="mr-3 text-primary focus:ring-primary" checked>
                            <div>
                                <p class="font-medium">Email Address</p>
                                <p class="text-sm text-gray-500">Verify using your email address</p>
                            </div>
                        </label>
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="verification_method" value="phone"
                                class="mr-3 text-primary focus:ring-primary">
                            <div>
                                <p class="font-medium">Phone Number</p>
                                <p class="text-sm text-gray-500">Verify using your phone number</p>
                            </div>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">You can add the other contact method later in your profile
                        settings.</p>
                </div>
                <div id="register-verification-method-error" class="text-red-500 text-sm mt-1 hidden"></div>
                <button type="button" @click="handleVerificationMethodSubmit()"
                    class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Continue</button>
            </form>
        </div>
    </div>

    <div id="register-step-email" x-show="step==='register-email'" :class="step==='register-email'?'active':''"
        class="auth-form" style="display:none">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'email') ?></h2>
                <button @click="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <p class="mb-4 text-center text-sm text-gray-600">
                <a href="javascript:void(0)" @click="showRegisterStep('verification-method')"
                    class="text-primary hover:text-red-700 font-medium">
                    <i data-lucide="arrow-left" class="w-4 h-4 inline-block mr-1"></i>Back
                </a>
            </p>
            <p class="mb-4 text-sm text-gray-600">Creating account for <strong id="register-username-display"></strong>
            </p>
            <form id="register-email-form" class="space-y-4" autocomplete="off" data-mode="register" data-step="email">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <div class="relative">
                        <i data-lucide="mail"
                            class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="email" id="register-email" required
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                            placeholder="Enter your email" autofocus autocomplete="off"
                            onkeyup="checkTripleSpace(this)">
                    </div>
                    <div id="register-email-error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <button type="button" id="register-email-submit-btn" @click="handleRegisterEmailSubmit()"
                    class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Continue</button>
            </form>
        </div>
    </div>

    <div id="register-step-email-verify" x-show="step==='register-email-verify'"
        :class="step==='register-email-verify'?'active':''" class="auth-form" style="display:none">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'email-verify') ?></h2>
                <button @click="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <p class="mb-4 text-center text-sm text-gray-600">
                <a href="javascript:void(0)" @click="showRegisterStep('email')"
                    class="text-primary hover:text-red-700 font-medium">
                    <i data-lucide="arrow-left" class="w-4 h-4 inline-block mr-1"></i>Back
                </a>
            </p>
            <p class="mb-4 text-center text-sm text-gray-600">Verifying for <strong
                    id="register-username-display-verify"></strong></p>
            <p class="mb-4 text-center">We've sent a verification code to <strong id="register-email-display"></strong>
            </p>
            <p class="text-sm text-gray-500 mt-1 text-center mb-4">Enter the 6-digit code below</p>
            <form id="register-email-verify-form" class="space-y-4" autocomplete="off" data-mode="register"
                data-step="email-verify">
                <div id="email-otp-inputs"></div>
                <input type="hidden" id="email-otp" value="">
                <div id="email-otp-error" class="text-red-500 text-sm mt-1 hidden"></div>
                <p class="mt-2 text-sm text-gray-500 text-center">
                    Didn't receive the code?
                    <button type="button" id="resend-email-otp"
                        class="text-primary hover:text-red-700 text-sm">Resend</button>
                    <span id="email-otp-timer" class="text-sm"></span>
                </p>
                <button type="button" id="email-otp-submit-btn" @click="handleEmailOTPSubmit()"
                    class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Verify
                    Email</button>
            </form>
        </div>
    </div>

    <div id="register-step-phone" x-show="step==='register-phone'" :class="step==='register-phone'?'active':''"
        class="auth-form" style="display:none">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'phone') ?></h2>
                <button @click="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <p class="mb-4 text-center text-sm text-gray-600">
                <a href="javascript:void(0)" @click="showRegisterStep('verification-method')"
                    class="text-primary hover:text-red-700 font-medium">
                    <i data-lucide="arrow-left" class="w-4 h-4 inline-block mr-1"></i>Back
                </a>
            </p>
            <p class="mb-4 text-sm text-gray-600">Creating account for <strong
                    id="register-username-display-phone"></strong></p>
            <form id="register-phone-form" class="space-y-4" autocomplete="off" data-mode="register" data-step="phone">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required placeholder="Phone Number"
                        class="w-full py-2 border border-gray-300 rounded-lg" autofocus autocomplete="off">
                    <div id="register-phone-error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <button type="button" id="register-phone-submit-btn" @click="handleRegisterPhoneSubmit()"
                    class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Continue</button>
            </form>
        </div>
    </div>

    <div id="register-step-phone-verify" x-show="step==='register-phone-verify'"
        :class="step==='register-phone-verify'?'active':''" class="auth-form" style="display:none">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'phone-verify') ?></h2>
                <button @click="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <p class="mb-4 text-center text-sm text-gray-600">
                <a href="javascript:void(0)" @click="showRegisterStep('phone')"
                    class="text-primary hover:text-red-700 font-medium">
                    <i data-lucide="arrow-left" class="w-4 h-4 inline-block mr-1"></i>Back
                </a>
            </p>
            <p class="mb-4 text-sm text-gray-600">Creating account for <strong
                    id="register-username-display-phone-verify"></strong></p>
            <p class="mb-4 text-center">We've sent a verification code to <strong id="register-phone-display"></strong>
            </p>
            <p class="text-sm text-gray-500 mt-1 text-center mb-4">Enter the 6-digit code below</p>
            <form id="register-phone-verify-form" class="space-y-4" autocomplete="off" data-mode="register"
                data-step="phone-verify">
                <div id="phone-otp-inputs"></div>
                <input type="hidden" id="phone-otp" value="">
                <div id="phone-otp-error" class="text-red-500 text-sm mt-1 hidden"></div>
                <p class="mt-2 text-sm text-gray-500 text-center">
                    Didn't receive the code?
                    <button type="button" id="resend-phone-otp"
                        class="text-primary hover:text-red-700 text-sm">Resend</button>
                    <span id="phone-otp-timer" class="text-sm"></span>
                </p>
                <button type="button" id="phone-otp-submit-btn" @click="handlePhoneOTPSubmit()"
                    class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Verify
                    Phone</button>
            </form>
        </div>
    </div>

    <div id="register-step-password" x-show="step==='register-password'" :class="step==='register-password'?'active':''"
        class="auth-form" style="display:none">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('register', 'password') ?></h2>
                <button @click="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <p class="mb-4 text-center text-sm text-gray-600">
                <a href="javascript:void(0)" @click="goBackFromPassword()"
                    class="text-primary hover:text-red-700 font-medium">
                    <i data-lucide="arrow-left" class="w-4 h-4 inline-block mr-1"></i>Back
                </a>
            </p>
            <p class="mb-4 text-sm text-gray-600">Creating account for <strong
                    id="register-username-display-password"></strong></p>
            <form id="register-password-form" class="space-y-4" autocomplete="off" data-mode="register"
                data-step="password">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <i data-lucide="lock"
                            class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" required
                            class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                            placeholder="Create a password" id="register-password" autofocus autocomplete="new-password"
                            oninput="checkPasswordStrength(this.value)" onkeyup="checkTripleSpace(this)">
                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            onclick="togglePasswordVisibility('register-password')"><i data-lucide="eye"
                                class="w-5 h-5"></i></button>
                    </div>
                    <div class="password-strength-meter mt-2">
                        <div class="password-strength-meter-fill"></div>
                    </div>
                    <div class="password-strength-text text-xs text-gray-500"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <div class="relative">
                        <i data-lucide="lock"
                            class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" required
                            class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                            placeholder="Confirm your password" id="register-confirm-password"
                            autocomplete="new-password" onkeyup="checkTripleSpace(this)">
                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            onclick="togglePasswordVisibility('register-confirm-password')"><i data-lucide="eye"
                                class="w-5 h-5"></i></button>
                    </div>
                    <div id="register-password-error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <div class="flex items-start">
                    <input type="checkbox" id="terms-checkbox" required
                        class="mt-1 rounded border-gray-300 text-primary focus:ring-primary">
                    <span class="ml-2 text-sm text-gray-600">I agree to the
                        <a href="terms-and-conditions" class="text-primary hover:text-red-700">Terms of Service</a> and
                        <a href="#" class="text-primary hover:text-red-700">Privacy Policy</a>
                    </span>
                </div>
                <button type="button" id="register-password-submit-btn" @click="handleRegisterPasswordSubmit()"
                    class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Create
                    Account</button>
            </form>
        </div>
    </div>

    <div id="forgot-password-options" x-show="step==='forgot-options'" :class="step==='forgot-options'?'active':''"
        class="auth-form" style="display:none">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('forgot_password', 'options') ?></h2>
                <button @click="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <p class="mb-4 text-center text-sm text-gray-600">
                <a href="javascript:void(0)" @click="showLoginStep('password')"
                    class="text-primary hover:text-red-700 font-medium">
                    <i data-lucide="arrow-left" class="w-4 h-4 inline-block mr-1"></i>Back
                </a>
            </p>
            <p class="mb-2 text-sm text-gray-600">Resetting password for <strong id="forgot-username-display"></strong>
            </p>
            <form id="forgot-password-options-form" class="space-y-4" autocomplete="off" data-mode="forgot_password"
                data-step="options">
                <div>
                    <p class="mb-4 text-sm text-gray-600">Please select how you would like to receive your verification
                        code:</p>
                    <div class="space-y-3">
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="reset_method" value="email"
                                class="mr-3 text-primary focus:ring-primary" checked>
                            <div>
                                <p class="font-medium">Email</p>
                                <p class="text-sm text-gray-500">Receive a verification code via email</p>
                            </div>
                        </label>
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="reset_method" value="phone"
                                class="mr-3 text-primary focus:ring-primary">
                            <div>
                                <p class="font-medium">Phone</p>
                                <p class="text-sm text-gray-500">Receive a verification code via SMS</p>
                            </div>
                        </label>
                    </div>
                </div>
                <button type="button" @click="handleForgotPasswordMethodSubmit()"
                    class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Continue</button>
            </form>
            <p class="mt-4 text-center text-sm text-gray-600">Remember your password?
                <a href="javascript:void(0)" @click="showLoginStep('identifier')"
                    class="text-primary hover:text-red-700 font-medium">Back to Sign In</a>
            </p>
        </div>
    </div>

    <div id="forgot-password-email-form" x-show="step==='forgot-email'" :class="step==='forgot-email'?'active':''"
        class="auth-form" style="display:none">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('forgot_password', 'email-form') ?></h2>
                <button @click="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <p class="mb-4 text-center text-sm text-gray-600">
                <a href="javascript:void(0)" @click="showForgotPasswordOptions()"
                    class="text-primary hover:text-red-700 font-medium">
                    <i data-lucide="arrow-left" class="w-4 h-4 inline-block mr-1"></i>Back
                </a>
            </p>
            <p class="mb-2 text-sm text-gray-600">Resetting password for <strong
                    id="forgot-username-display-email"></strong></p>
            <form id="forgot-password-email-form-element" class="space-y-4" autocomplete="off"
                data-mode="forgot_password" data-step="email">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <div class="relative">
                        <i data-lucide="mail"
                            class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="email" id="forgot-email" required
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                            placeholder="Enter your email" autofocus autocomplete="off"
                            onkeyup="checkTripleSpace(this)">
                    </div>
                    <div id="forgot-email-error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <button type="button" id="forgot-email-submit-btn" @click="handleForgotEmailSubmit()"
                    class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Send Reset
                    Code</button>
            </form>
        </div>
    </div>

    <div id="forgot-password-phone-form" x-show="step==='forgot-phone'" :class="step==='forgot-phone'?'active':''"
        class="auth-form" style="display:none">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('forgot_password', 'phone-form') ?></h2>
                <button @click="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <p class="mb-4 text-center text-sm text-gray-600">
                <a href="javascript:void(0)" @click="showForgotPasswordOptions()"
                    class="text-primary hover:text-red-700 font-medium">
                    <i data-lucide="arrow-left" class="w-4 h-4 inline-block mr-1"></i>Back
                </a>
            </p>
            <p class="mb-2 text-sm text-gray-600">Resetting password for <strong
                    id="forgot-username-display-phone"></strong></p>
            <form id="forgot-password-phone-form-element" class="space-y-4" autocomplete="off"
                data-mode="forgot_password" data-step="phone">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" id="forgot-phone" name="forgot-phone" required placeholder="Phone Number"
                        class="w-full py-2 border border-gray-300 rounded-lg" autofocus autocomplete="off">
                    <div id="forgot-phone-error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <button type="button" id="forgot-phone-submit-btn" @click="handleForgotPhoneSubmit()"
                    class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Send Reset
                    Code</button>
            </form>
        </div>
    </div>

    <div id="reset-password-verify" x-show="step==='reset-verify'" :class="step==='reset-verify'?'active':''"
        class="auth-form" style="display:none">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('reset_password', 'verify') ?></h2>
                <button @click="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <p class="mb-4 text-center text-sm text-gray-600">
                <a href="javascript:void(0)" id="reset-back-link" class="text-primary hover:text-red-700 font-medium">
                    <i data-lucide="arrow-left" class="w-4 h-4 inline-block mr-1"></i>Back
                </a>
            </p>
            <p class="mb-2 text-sm text-gray-600">Resetting password for <strong
                    id="forgot-username-display-reset"></strong></p>
            <p class="mb-4 text-center">We've sent a verification code to <strong id="reset-contact-display"></strong>
            </p>
            <p class="text-sm text-gray-500 mt-1 text-center mb-4">Enter the 6-digit code below</p>
            <form id="reset-verify-form" class="space-y-4" autocomplete="off" data-mode="reset_password"
                data-step="verify_otp">
                <div id="reset-otp-inputs"></div>
                <input type="hidden" id="reset-otp" value="">
                <div id="reset-otp-error" class="text-red-500 text-sm mt-1 hidden"></div>
                <p class="mt-2 text-sm text-gray-500 text-center">
                    Didn't receive the code?
                    <button type="button" id="resend-reset-otp"
                        class="text-primary hover:text-red-700 text-sm">Resend</button>
                    <span id="reset-otp-timer" class="text-sm"></span>
                </p>
                <button type="button" id="reset-otp-submit-btn" @click="handleResetOTPSubmit()"
                    class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Verify
                    Code</button>
            </form>
        </div>
    </div>

    <div id="reset-password-form" x-show="step==='reset-form'" :class="step==='reset-form'?'active':''"
        class="auth-form" style="display:none">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle('reset_password', 'form') ?></h2>
                <button @click="closeAuthModal()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <p class="mb-4 text-center text-sm text-gray-600">
                <a href="javascript:void(0)" @click="showResetVerifyForm()"
                    class="text-primary hover:text-red-700 font-medium">
                    <i data-lucide="arrow-left" class="w-4 h-4 inline-block mr-1"></i>Back
                </a>
            </p>
            <p class="mb-2 text-sm text-gray-600">Resetting password for <strong
                    id="forgot-username-display-reset-form"></strong></p>
            <form id="reset-password-form-element" class="space-y-4" autocomplete="off" data-mode="reset_password"
                data-step="new_password">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <div class="relative">
                        <i data-lucide="lock"
                            class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" required
                            class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                            placeholder="Enter new password" id="new-password" autofocus autocomplete="new-password"
                            oninput="checkPasswordStrength(this.value,'new-password')" onkeyup="checkTripleSpace(this)">
                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            onclick="togglePasswordVisibility('new-password')"><i data-lucide="eye"
                                class="w-5 h-5"></i></button>
                    </div>
                    <div class="password-strength-meter mt-2">
                        <div class="password-strength-meter-fill"></div>
                    </div>
                    <div class="password-strength-text text-xs text-gray-500"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <div class="relative">
                        <i data-lucide="lock"
                            class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" required
                            class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                            placeholder="Confirm new password" id="confirm-new-password" autocomplete="new-password"
                            onkeyup="checkTripleSpace(this)">
                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            onclick="togglePasswordVisibility('confirm-new-password')"><i data-lucide="eye"
                                class="w-5 h-5"></i></button>
                    </div>
                    <div id="reset-password-error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <button type="button" id="reset-password-submit-btn" @click="handleResetPasswordSubmit()"
                    class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Reset
                    Password</button>
            </form>
        </div>
    </div>
</div>

<script>
    const SPINNER = '<svg class="animate-spin inline h-5 w-5 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>';

    function authUI() {
        return {
            step: 'start',
            __method: 'username',
            init() {
                if (window.lucide && lucide.createIcons) lucide.createIcons();
                this.$watch('step', () => { if (window.lucide && lucide.createIcons) lucide.createIcons(); });
                this.$root.__method = 'username';
                window.__auth = { go: (s) => { this.go(s); }, get step() { return this.step; } };
            },
            go(s) { this.step = s; }
        }
    }

    (function () {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const mode = form.getAttribute('data-mode');
                    const step = form.getAttribute('data-step');
                    if (mode === 'login') {
                        if (step === 'identifier') {
                            handleLoginIdentifierSubmit();
                        } else if (step === 'password') {
                            handleLoginPasswordSubmit();
                        }
                    } else if (mode === 'register') {
                        if (step === 'username') {
                            handleRegisterUsernameSubmit();
                        } else if (step === 'verification-method') {
                            handleVerificationMethodSubmit();
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
    let phoneOTPTimer;
    let resetOTPTimer;
    let spaceCount = 0;

    function validatePhoneInput(input) {
        input.value = input.value.replace(/\D/g, '');
        if (input.value.length > 9) {
            input.value = input.value.substring(0, 9);
        }
    }

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
        const key = step === 'identifier' ? 'login-identifier' : 'login-' + step;
        if (typeof Alpine !== 'undefined') { Alpine.store?.auth ? Alpine.store('auth').go(key) : window.__auth?.go(key); } else { window.__auth?.go(key); }
        if (step === 'identifier') {
            if (typeof window.sessionTracker !== 'undefined') {
                window.sessionTracker.trackLoginModalOpen();
            }
        }
    }

    function showRegisterStep(step) {
        const key = 'register-' + step;
        if (typeof Alpine !== 'undefined') { Alpine.store?.auth ? Alpine.store('auth').go(key) : window.__auth?.go(key); } else { window.__auth?.go(key); }
    }

    function hideAllForms() { }

    function showForgotPasswordOptions() {
        hideAllForms();
        if (typeof Alpine !== 'undefined') { Alpine.store?.auth ? Alpine.store('auth').go('forgot-options') : window.__auth?.go('forgot-options'); } else { window.__auth?.go('forgot-options'); }
        const el = document.getElementById('forgot-password-options');
        if (el) { document.getElementById('forgot-username-display').textContent = loginData.identifier || ''; }
        if (typeof window.sessionTracker !== 'undefined') {
            window.sessionTracker.trackPasswordResetRequest();
        }
    }

    function showEmptyPasswordOptions() {
        hideAllForms();
        if (typeof Alpine !== 'undefined') { Alpine.store?.auth ? Alpine.store('auth').go('empty-options') : window.__auth?.go('empty-options'); } else { window.__auth?.go('empty-options'); }
        const emailOption = document.getElementById('empty-password-email-option');
        const phoneOption = document.getElementById('empty-password-phone-option');
        const emailInput = document.getElementById('empty-password-email');
        const phoneInput = document.getElementById('empty-password-phone');
        const hasEmail = !!loginData.email;
        const hasPhone = !!loginData.phone;
        if (hasEmail && hasPhone) {
            emailOption.style.display = 'flex';
            phoneOption.style.display = 'flex';
            emailInput.checked = true;
        } else if (hasEmail) {
            emailOption.style.display = 'flex';
            phoneOption.style.display = 'none';
            emailInput.checked = true;
        } else if (hasPhone) {
            phoneOption.style.display = 'flex';
            emailOption.style.display = 'none';
            phoneInput.checked = true;
        }
        document.getElementById('empty-username-display').textContent = loginData.identifier || '';
        const emailHintSpan = document.getElementById('empty-password-email-hint');
        const phoneHintSpan = document.getElementById('empty-password-phone-hint');
        emailHintSpan.textContent = loginData.email || '';
        phoneHintSpan.textContent = loginData.phone || '';
    }

    function showResetVerifyForm() {
        hideAllForms();
        if (typeof Alpine !== 'undefined') { Alpine.store?.auth ? Alpine.store('auth').go('reset-verify') : window.__auth?.go('reset-verify'); } else { window.__auth?.go('reset-verify'); }
        const rv = document.getElementById('reset-password-verify');
        if (rv) {
            document.getElementById('forgot-username-display-reset').textContent = loginData.identifier || '';
        }
        document.getElementById('reset-back-link').onclick = function () {
            showForgotPasswordForm(resetMethod);
        };
    }

    function showResetPasswordForm() {
        hideAllForms();
        if (typeof Alpine !== 'undefined') { Alpine.store?.auth ? Alpine.store('auth').go('reset-form') : window.__auth?.go('reset-form'); } else { window.__auth?.go('reset-form'); }
        const rf = document.getElementById('reset-password-form');
        if (rf) {
            document.getElementById('forgot-username-display-reset-form').textContent = loginData.identifier || '';
        }
    }

    function renderOtpInputs(target) {
        const container = document.getElementById(target + '-inputs');
        if (!container) return;
        container.innerHTML = `
            <div class="flex justify-between gap-2 mb-2">
                <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="${target}" autofocus>
                <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="${target}">
                <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="${target}">
                <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="${target}">
                <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="${target}">
                <input type="text" maxlength="1" class="otp-input w-full text-center py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-xl" data-otp-target="${target}">
            </div>
        `;
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
                const m = Math.floor(r / 60); const s = r % 60;
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
        } else if (type === 'phone-otp') {
            if (phoneOTPTimer) clearInterval(phoneOTPTimer);
            phoneOTPTimer = iv;
        } else if (type === 'reset-otp') {
            if (resetOTPTimer) clearInterval(resetOTPTimer);
            resetOTPTimer = iv;
        } {
            if (resetOTPTimer) clearInterval(resetOTPTimer);
            resetOTPTimer = iv;
        }
    }

    function updateOTPValue(target) {
        const values = $('.otp-input[data-otp-target="' + target + '"]').map(function () {
            return this.value;
        }).get().join('');
        $('#' + target).val(values);
    }

    function resendOtp(endpoint, payload, timerType, successMsg, errorMsg) {
        const button = $('#resend-' + timerType);
        if (button.prop('disabled')) return;
        const originalText = button.html();
        button.prop('disabled', true).addClass('text-gray-400').html(SPINNER + 'Resending...');
        $.ajax({
            url: BASE_URL + 'auth/' + endpoint,
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    startOTPTimer(timerType, 120);
                    notifications.success(successMsg);
                } else {
                    button.prop('disabled', false).removeClass('text-gray-400');
                    notifications.error(response.message || errorMsg);
                }
                button.html(originalText);
            },
            error: function () {
                button.prop('disabled', false).removeClass('text-gray-400').html(originalText);
                notifications.error(errorMsg);
            }
        });
    }

    function categorizeError(errorMessage, errorCode) {
        const identifierErrors = [
            'user not found', 'account not found', 'username not found', 'email not found', 'phone not found', 'invalid username', 'invalid email', 'invalid phone', 'username does not exist', 'email does not exist', 'phone does not exist', 'account does not exist', 'no account found', 'user does not exist'
        ];
        const passwordErrors = [
            'invalid password', 'incorrect password', 'wrong password', 'password mismatch', 'authentication failed', 'login failed', 'invalid credentials'
        ];
        if (errorCode === 'EMPTY_PASSWORD') return 'empty_password';
        const lowerMessage = errorMessage.toLowerCase();
        for (let error of identifierErrors) { if (lowerMessage.includes(error)) return 'identifier'; }
        for (let error of passwordErrors) { if (lowerMessage.includes(error)) return 'password'; }
        return 'general';
    }

    function handleLoginIdentifierSubmit() {
        const selectedMethod = document.querySelector('input[name="login_method"]:checked').value;
        let identifier = '';
        let identifierType = '';
        if (selectedMethod === 'username') {
            identifier = document.getElementById('login-username').value;
            identifierType = 'username';
        } else if (selectedMethod === 'email') {
            identifier = document.getElementById('login-email').value;
            identifierType = 'email';
        } else if (selectedMethod === 'phone') {
            const phoneDigits = document.getElementById('login-phone').value;
            if (phoneDigits.length !== 9 || !/^\d{9}$/.test(phoneDigits)) {
                showError('login-identifier-error', 'Please enter exactly 9 digits for your phone number');
                return;
            }
            identifier = '+256' + phoneDigits;
            identifierType = 'phone';
        }
        if (!identifier) {
            showError('login-identifier-error', `Please enter your ${selectedMethod}`);
            return;
        }
        trackUserEvent('login_identifier_submit', { identifier, identifierType });
        hideError('login-identifier-error');
        const button = document.querySelector('#login-identifier-form button');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = SPINNER + 'Checking...';

        $.ajax({
            url: BASE_URL + 'auth/checkUser',
            type: 'POST',
            data: JSON.stringify({ identifier: identifier, identifierType: identifierType }),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    loginData.identifier = identifier;
                    loginData.identifierType = identifierType;
                    loginData.userType = response.userType;
                    document.getElementById('login-identifier-display').textContent = identifier;
                    trackUserEvent('login_identifier_success', { identifier, identifierType });
                    showLoginStep('password');
                } else {
                    const errorCategory = categorizeError(response.message || 'User not found', response.errorCode);
                    trackUserEvent('login_identifier_failed', { identifier, identifierType, errorMessage: response.message || 'User not found' });
                    if (errorCategory === 'identifier') {
                        showError('login-identifier-error', response.message || 'User not found');
                    } else {
                        showError('login-identifier-error', response.message || 'User not found');
                    }
                }
            },
            error: function (xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    const errorCategory = categorizeError(response.message || 'An error occurred', response.errorCode);
                    if (errorCategory === 'empty_password') {
                        loginData.email = response.email;
                        loginData.phone = response.phone;
                        loginData.identifier = identifier;
                        loginData.identifierType = identifierType;
                        hideError('login-identifier-error');
                        showEmptyPasswordOptions();
                    } else if (errorCategory === 'identifier') {
                        trackUserEvent('login_identifier_failed', { identifier, identifierType, errorMessage: response.message || 'User not found' });
                        showError('login-identifier-error', response.message || 'User not found');
                    } else {
                        trackUserEvent('login_identifier_failed', { identifier, identifierType, errorMessage: response.message || 'An error occurred' });
                        showError('login-identifier-error', 'Server error. Please try again later.');
                    }
                } catch (e) {
                    trackUserEvent('login_identifier_failed', { identifier, identifierType, errorMessage: 'Server error' });
                    showError('login-identifier-error', 'Server error. Please try again later.');
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
        button.innerHTML = SPINNER + 'Logging in...';
        trackUserEvent('login_password_submit');
        $.ajax({
            url: BASE_URL + 'auth/login',
            type: 'POST',
            data: JSON.stringify({
                identifier: loginData.identifier,
                identifierType: loginData.identifierType,
                password: p,
                userType: loginData.userType
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    trackUserEvent('login_password_success');
                    trackUserEvent('login_success');
                    notifications.success('Login successful!');
                    setTimeout(() => {
                        closeAuthModal();
                    }, 1000);
                } else {
                    const errorCategory = categorizeError(response.message || 'Invalid password', response.errorCode);
                    trackUserEvent('login_password_failed', { errorMessage: response.message || 'Invalid password' });
                    if (errorCategory === 'password') {
                        showError('login-password-error', response.message || 'Invalid password');
                    } else {
                        showError('login-password-error', response.message || 'Login failed');
                    }
                }
            },
            error: function (xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    const errorCategory = categorizeError(response.message || 'An error occurred', response.errorCode);
                    if (errorCategory === 'empty_password') {
                        loginData.email = response.email;
                        loginData.phone = response.phone;
                        hideError('login-password-error');
                        showEmptyPasswordOptions();
                    } else if (errorCategory === 'password') {
                        trackUserEvent('login_password_failed', { errorMessage: response.message || 'Invalid password' });
                        showError('login-password-error', response.message || 'Invalid password');
                    } else {
                        trackUserEvent('login_password_failed', { errorMessage: response.message || 'An error occurred' });
                        showError('login-password-error', 'Server error. Please try again later.');
                    }
                } catch (e) {
                    trackUserEvent('login_password_failed', { errorMessage: 'Server error' });
                    showError('login-password-error', 'Server error. Please try again later.');
                }
            }
        });
    }

    $(document).ready(function () {
        const continueBtn = document.getElementById('empty-password-continue');
        if (continueBtn) {
            continueBtn.addEventListener('click', () => {
                const method = document.getElementById('empty-password-email').checked ? 'email' : 'phone';
                const identifier = method === 'email' ? loginData.email : loginData.phone;
                if (!identifier) {
                    showError('empty-password-error', 'No contact method available.');
                    return;
                }
                hideError('empty-password-error');
                const loader = document.getElementById('empty-password-loading');
                loader.classList.remove('hidden');
                if (method === 'email') {
                    showForgotPasswordForm('email');
                    document.getElementById('forgot-email').value = identifier;
                } else {
                    showForgotPasswordForm('phone');
                    const phoneInput = document.getElementById('forgot-phone');
                    const itiPhone = window.intlTelInputGlobals.getInstance(phoneInput);
                    itiPhone.setNumber(identifier);
                }
                loader.classList.add('hidden');
            });
        }
    });

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
        hideAllForms();
        if (m === 'email') {
            if (typeof Alpine !== 'undefined') { Alpine.store?.auth ? Alpine.store('auth').go('forgot-email') : window.__auth?.go('forgot-email'); } else { window.__auth?.go('forgot-email'); }
            const ef = document.getElementById('forgot-password-email-form');
            if (ef) {
                document.getElementById('forgot-username-display-email').textContent = loginData.identifier || '';
            }
        } else if (m === 'phone') {
            if (typeof Alpine !== 'undefined') { Alpine.store?.auth ? Alpine.store('auth').go('forgot-phone') : window.__auth?.go('forgot-phone'); } else { window.__auth?.go('forgot-phone'); }
            const pf = document.getElementById('forgot-password-phone-form');
            if (pf) {
                document.getElementById('forgot-username-display-phone').textContent = loginData.identifier || '';
            }
        }
    }

    function renderOtpStartForReset() {
        renderOtpInputs('reset-otp');
        $('.otp-input[data-otp-target="reset-otp"]').val('');
        document.getElementById('reset-otp').value = '';
        startOTPTimer('reset-otp', 120);
    }

    function showResetBackLink(to) {
        document.getElementById('reset-back-link').onclick = function () { showForgotPasswordForm(to); };
    }

    function showResetTargetDisplay(val) {
        document.getElementById('reset-contact-display').textContent = val;
    }

    function showResetUserDisplay() {
        document.getElementById('forgot-username-display-reset').textContent = loginData.identifier || '';
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
        button.innerHTML = SPINNER + 'Checking...';
        trackUserEvent('register_username_submit', { username: u });
        $.ajax({
            url: BASE_URL + 'auth/checkUsername',
            type: 'POST',
            data: JSON.stringify({ username: u }),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    registrationData.username = u;
                    document.getElementById('register-username-display-method').textContent = u;
                    document.getElementById('register-username-display').textContent = u;
                    document.getElementById('register-username-display-verify').textContent = u;
                    document.getElementById('register-username-display-phone').textContent = u;
                    document.getElementById('register-username-display-phone-verify').textContent = u;
                    document.getElementById('register-username-display-password').textContent = u;
                    trackUserEvent('register_username_success', { username: u });
                    showRegisterStep('verification-method');
                } else {
                    trackUserEvent('register_username_failed', { username: u, errorMessage: response.message || 'Username is already taken' });
                    showError('register-username-error', response.message || 'Username is already taken');
                }
            },
            error: function (xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    trackUserEvent('register_username_failed', { username: u, errorMessage: response.message || 'An error occurred' });
                    showError('register-username-error', response.message || 'An error occurred');
                } catch (e) {
                    trackUserEvent('register_username_failed', { username: u, errorMessage: 'Server error' });
                    showError('register-username-error', 'Server error. Please try again later.');
                }
            }
        });
    }

    function handleVerificationMethodSubmit() {
        const selectedMethod = document.querySelector('input[name="verification_method"]:checked').value;
        registrationData.verificationMethod = selectedMethod;
        if (selectedMethod === 'email') {
            showRegisterStep('email');
        } else if (selectedMethod === 'phone') {
            showRegisterStep('phone');
        }
    }

    function goBackFromPassword() {
        if (registrationData.verificationMethod === 'email') {
            showRegisterStep('email-verify');
        } else if (registrationData.verificationMethod === 'phone') {
            showRegisterStep('phone-verify');
        }
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
        button.innerHTML = SPINNER + 'Sending OTP...';
        trackUserEvent('register_email_submit', { email: e });
        $.ajax({
            url: BASE_URL + 'auth/checkEmail',
            type: 'POST',
            data: JSON.stringify({ email: e }),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    registrationData.email = e;
                    document.getElementById('register-email-display').textContent = e;
                    $.ajax({
                        url: BASE_URL + 'auth/sendEmailOTP',
                        type: 'POST',
                        data: JSON.stringify({ email: e }),
                        contentType: 'application/json',
                        dataType: 'json',
                        success: function (otpResponse) {
                            button.disabled = false;
                            button.innerHTML = originalText;
                            if (otpResponse.success) {
                                trackUserEvent('register_email_otp_sent', { email: e });
                                notifications.success('Verification code sent to your email');
                                renderOtpInputs('email-otp');
                                startOTPTimer('email-otp', 120);
                                showRegisterStep('email-verify');
                                $('.otp-input[data-otp-target="email-otp"]').val('');
                                document.getElementById('email-otp').value = '';
                            } else {
                                trackUserEvent('register_email_otp_failed', { email: e, errorMessage: otpResponse.message || 'Failed to send verification code' });
                                showError('register-email-error', otpResponse.message || 'Failed to send verification code');
                            }
                        },
                        error: function (xhr) {
                            button.disabled = false;
                            button.innerHTML = originalText;
                            try {
                                const response = JSON.parse(xhr.responseText);
                                trackUserEvent('register_email_otp_failed', { email: e, errorMessage: response.message || 'Failed to send verification code' });
                                showError('register-email-error', response.message || 'Failed to send verification code');
                            } catch (e) {
                                trackUserEvent('register_email_otp_failed', { email: e, errorMessage: 'Failed to send verification code. Please try again.' });
                                showError('register-email-error', 'Failed to send verification code. Please try again.');
                            }
                        }
                    });
                } else {
                    button.disabled = false;
                    button.innerHTML = originalText;
                    trackUserEvent('register_email_check_failed', { email: e, errorMessage: response.message || 'Email is already registered' });
                    showError('register-email-error', response.message || 'Email is already registered');
                }
            },
            error: function (xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    trackUserEvent('register_email_check_failed', { email: e, errorMessage: response.message || 'An error occurred' });
                    showError('register-email-error', response.message || 'An error occurred');
                } catch (e) {
                    trackUserEvent('register_email_check_failed', { email: e, errorMessage: 'Server error' });
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
        button.innerHTML = SPINNER + 'Verifying...';
        trackUserEvent('email_otp_submit', { email: registrationData.email });
        $.ajax({
            url: BASE_URL + 'auth/verifyEmailOTP',
            type: 'POST',
            data: JSON.stringify({ email: registrationData.email, otp: o }),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    trackUserEvent('email_otp_success');
                    notifications.success('Email verified successfully');
                    registrationData.emailVerified = true;
                    showRegisterStep('password');
                } else {
                    trackUserEvent('email_otp_failed', { errorMessage: response.message || 'Invalid verification code' });
                    showError('email-otp-error', response.message || 'Invalid verification code');
                }
            },
            error: function (xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    trackUserEvent('email_otp_failed', { errorMessage: response.message || 'An error occurred' });
                    showError('email-otp-error', response.message || 'An error occurred');
                } catch (e) {
                    trackUserEvent('email_otp_failed', { errorMessage: 'Server error' });
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
        button.innerHTML = SPINNER + 'Checking...';
        trackUserEvent('register_phone_submit', { phone: pn });
        $.ajax({
            url: BASE_URL + 'auth/checkPhone',
            type: 'POST',
            data: JSON.stringify({ phone: pn }),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    registrationData.phone = pn;
                    document.getElementById('register-phone-display').textContent = pn;
                    $.ajax({
                        url: BASE_URL + 'auth/sendPhoneOTP',
                        type: 'POST',
                        data: JSON.stringify({ phone: pn }),
                        contentType: 'application/json',
                        dataType: 'json',
                        success: function (otpResponse) {
                            if (otpResponse.success) {
                                trackUserEvent('register_phone_otp_sent', { phone: pn });
                                notifications.success('Verification code sent to your phone');
                                renderOtpInputs('phone-otp');
                                startOTPTimer('phone-otp', 120);
                                showRegisterStep('phone-verify');
                                $('.otp-input[data-otp-target="phone-otp"]').val('');
                                document.getElementById('phone-otp').value = '';
                            } else {
                                trackUserEvent('register_phone_otp_failed', { phone: pn, errorMessage: otpResponse.message || 'Failed to send verification code' });
                                showError('register-phone-error', otpResponse.message || 'Failed to send verification code');
                            }
                        },
                        error: function (xhr) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                trackUserEvent('register_phone_otp_failed', { phone: pn, errorMessage: response.message || 'Failed to send verification code' });
                                showError('register-phone-error', response.message || 'Failed to send verification code');
                            } catch (e) {
                                trackUserEvent('register_phone_otp_failed', { phone: pn, errorMessage: 'Failed to send verification code. Please try again.' });
                                showError('register-phone-error', 'Failed to send verification code. Please try again.');
                            }
                        }
                    });
                } else {
                    trackUserEvent('register_phone_check_failed', { phone: pn, errorMessage: response.message || 'Phone number is already registered' });
                    showError('register-phone-error', response.message || 'Phone number is already registered');
                }
            },
            error: function (xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    trackUserEvent('register_phone_check_failed', { phone: pn, errorMessage: response.message || 'An error occurred' });
                    showError('register-phone-error', response.message || 'An error occurred');
                } catch (e) {
                    trackUserEvent('register_phone_check_failed', { phone: pn, errorMessage: 'Server error' });
                    showError('register-phone-error', 'Server error. Please try again later.');
                }
            }
        });
    }

    function handlePhoneOTPSubmit() {
        const o = document.getElementById('phone-otp').value;
        if (!o || o.length !== 6) {
            showError('phone-otp-error', 'Please enter the complete verification code');
            return;
        }
        hideError('phone-otp-error');
        const button = document.getElementById('phone-otp-submit-btn');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = SPINNER + 'Verifying...';
        trackUserEvent('phone_otp_submit', { phone: registrationData.phone });
        $.ajax({
            url: BASE_URL + 'auth/verifyPhoneOTP',
            type: 'POST',
            data: JSON.stringify({ phone: registrationData.phone, otp: o }),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    trackUserEvent('phone_otp_success');
                    notifications.success('Phone verified successfully');
                    registrationData.phoneVerified = true;
                    showRegisterStep('password');
                } else {
                    trackUserEvent('phone_otp_failed', { errorMessage: response.message || 'Invalid verification code' });
                    showError('phone-otp-error', response.message || 'Invalid verification code');
                }
            },
            error: function (xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    trackUserEvent('phone_otp_failed', { errorMessage: response.message || 'An error occurred' });
                    showError('phone-otp-error', response.message || 'An error occurred');
                } catch (e) {
                    trackUserEvent('phone_otp_failed', { errorMessage: 'Server error' });
                    showError('phone-otp-error', 'Server error. Please try again later.');
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
        button.innerHTML = SPINNER + 'Creating account...';
        trackUserEvent('register_password_submit');
        const registrationPayload = {
            username: registrationData.username,
            password: p,
            verificationMethod: registrationData.verificationMethod
        };
        if (registrationData.verificationMethod === 'email') {
            registrationPayload.email = registrationData.email;
        } else if (registrationData.verificationMethod === 'phone') {
            registrationPayload.phone = registrationData.phone;
        }
        $.ajax({
            url: BASE_URL + 'auth/register',
            type: 'POST',
            data: JSON.stringify(registrationPayload),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    trackUserEvent('registration_complete', { status: 'success' });
                    notifications.success('Account created successfully!');
                    setTimeout(() => {
                        closeAuthModal();
                    }, 1000);
                } else {
                    trackUserEvent('registration_complete', { status: 'failed', errorMessage: response.message || 'Registration failed' });
                    showError('register-password-error', response.message || 'Registration failed');
                }
            },
            error: function (xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    trackUserEvent('registration_complete', { status: 'failed', errorMessage: response.message || 'An error occurred' });
                    showError('register-password-error', response.message || 'An error occurred');
                } catch (e) {
                    trackUserEvent('registration_complete', { status: 'failed', errorMessage: 'Server error' });
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
        button.innerHTML = SPINNER + 'Sending...';
        trackUserEvent('forgot_email_submit', { email: e });
        $.ajax({
            url: BASE_URL + 'auth/sendResetEmail',
            type: 'POST',
            data: JSON.stringify({ username: loginData.identifier, email: e }),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    notifications.success('Reset code sent to your email');
                    forgotPasswordData.username = loginData.identifier;
                    forgotPasswordData.email = e;
                    forgotPasswordData.contact = e;
                    document.getElementById('reset-contact-display').textContent = e;
                    document.getElementById('reset-back-link').onclick = function () {
                        showForgotPasswordForm('email');
                    };
                    renderOtpInputs('reset-otp');
                    startOTPTimer('reset-otp', 120);
                    showResetVerifyForm();
                    $('.otp-input[data-otp-target="reset-otp"]').val('');
                    document.getElementById('reset-otp').value = '';
                } else {
                    trackUserEvent('forgot_email_failed', { email: e, errorMessage: response.message || 'Email not found' });
                    showError('forgot-email-error', response.message || 'Email not found');
                }
            },
            error: function (xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    trackUserEvent('forgot_email_failed', { email: e, errorMessage: response.message || 'An error occurred' });
                    showError('forgot-email-error', response.message || 'An error occurred');
                } catch (e) {
                    trackUserEvent('forgot_email_failed', { email: e, errorMessage: 'Server error' });
                    showError('forgot-email-error', 'Server error. Please try again later.');
                }
            }
        });
    }

    function handleForgotPhoneSubmit() {
        const pi = document.querySelector('#forgot-phone');
        const iti = window.intlTelInputGlobals.getInstance(pi);
        if (!iti.isValidNumber()) {
            showError('forgot-phone-error', 'Please enter a valid phone number');
            return;
        }
        const pn = iti.getNumber();
        hideError('forgot-phone-error');
        const button = document.getElementById('forgot-phone-submit-btn');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = SPINNER + 'Sending...';
        trackUserEvent('forgot_phone_submit', { phone: pn });
        $.ajax({
            url: BASE_URL + 'auth/sendResetPhone',
            type: 'POST',
            data: JSON.stringify({ username: loginData.identifier, phone: pn }),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    notifications.success('Reset code sent to your phone');
                    forgotPasswordData.username = loginData.identifier;
                    forgotPasswordData.phone = pn;
                    forgotPasswordData.contact = pn;
                    document.getElementById('reset-contact-display').textContent = pn;
                    document.getElementById('reset-back-link').onclick = function () {
                        showForgotPasswordForm('phone');
                    };
                    renderOtpInputs('reset-otp');
                    startOTPTimer('reset-otp', 120);
                    showResetVerifyForm();
                    $('.otp-input[data-otp-target="reset-otp"]').val('');
                    document.getElementById('reset-otp').value = '';
                } else {
                    trackUserEvent('forgot_phone_failed', { phone: pn, errorMessage: response.message || 'Phone number not found' });
                    showError('forgot-phone-error', response.message || 'Phone number not found');
                }
            },
            error: function (xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    trackUserEvent('forgot_phone_failed', { phone: pn, errorMessage: response.message || 'An error occurred' });
                    showError('forgot-phone-error', response.message || 'An error occurred');
                } catch (e) {
                    trackUserEvent('forgot_phone_failed', { phone: pn, errorMessage: 'Server error' });
                    showError('forgot-phone-error', 'Server error. Please try again later.');
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
        button.innerHTML = SPINNER + 'Verifying...';
        trackUserEvent('reset_otp_submit', { contact: forgotPasswordData.contact, contactType: resetMethod });
        $.ajax({
            url: BASE_URL + 'auth/verifyResetOTP',
            type: 'POST',
            data: JSON.stringify({ contact: forgotPasswordData.contact, contactType: resetMethod, otp: o }),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    trackUserEvent('reset_otp_success');
                    notifications.success('Code verified successfully');
                    forgotPasswordData.otpVerified = true;
                    showResetPasswordForm();
                } else {
                    trackUserEvent('reset_otp_failed', { errorMessage: response.message || 'Invalid verification code' });
                    showError('reset-otp-error', response.message || 'Invalid verification code');
                }
            },
            error: function (xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    trackUserEvent('reset_otp_failed', { errorMessage: response.message || 'An error occurred' });
                    showError('reset-otp-error', response.message || 'An error occurred');
                } catch (e) {
                    trackUserEvent('reset_otp_failed', { errorMessage: 'Server error' });
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
        button.innerHTML = SPINNER + 'Resetting password...';
        trackUserEvent('reset_password_submit');
        $.ajax({
            url: BASE_URL + 'auth/resetPassword',
            type: 'POST',
            data: JSON.stringify({
                username: forgotPasswordData.username,
                contact: forgotPasswordData.contact,
                contactType: resetMethod,
                password: np
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                button.disabled = false;
                button.innerHTML = originalText;
                if (response.success) {
                    trackUserEvent('password_reset_completed', { status: 'success' });
                    notifications.success('Password reset successfully!');
                    setTimeout(() => {
                        showLoginStep('identifier');
                        notifications.info('Please login with your new password');
                    }, 1500);
                } else {
                    trackUserEvent('password_reset_completed', { status: 'failed', errorMessage: response.message || 'Password reset failed' });
                    showError('reset-password-error', response.message || 'Password reset failed');
                }
            },
            error: function (xhr) {
                button.disabled = false;
                button.innerHTML = originalText;
                try {
                    const response = JSON.parse(xhr.responseText);
                    trackUserEvent('password_reset_completed', { status: 'failed', errorMessage: response.message || 'An error occurred' });
                    showError('reset-password-error', response.message || 'An error occurred');
                } catch (e) {
                    trackUserEvent('password_reset_completed', { status: 'failed', errorMessage: 'Server error' });
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
        return (password.length >= 8 && /[A-Z]/.test(password) && /[a-z]/.test(password) && /[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password));
    }

    $(document).on('input', '.otp-input', function () {
        const target = $(this).data('otp-target');
        if (this.value.length === this.maxLength) {
            $(this).next('.otp-input').focus();
        }
        updateOTPValue(target);
    });

    $(document).on('keydown', '.otp-input', function (e) {
        if (e.key === 'Backspace' && this.value === '') {
            $(this).prev('.otp-input').focus();
        }
    });

    $(document).on('click', '#resend-email-otp', function () {
        resendOtp('sendEmailOTP', { email: registrationData.email }, 'email-otp', 'Verification code resent to your email', 'Failed to resend code. Please try again.');
    });

    $(document).on('click', '#resend-phone-otp', function () {
        resendOtp('sendPhoneOTP', { phone: registrationData.phone }, 'phone-otp', 'Verification code resent to your phone', 'Failed to resend code. Please try again.');
    });

    $(document).on('click', '#resend-reset-otp', function () {
        if (!resetMethod) { resetMethod = 'email'; }
        const endpoint = resetMethod === 'email' ? 'sendResetEmail' : 'sendResetPhone';
        const dataPayload = resetMethod === 'email' ? { email: forgotPasswordData.contact } : { phone: forgotPasswordData.contact };
        resendOtp(endpoint, dataPayload, 'reset-otp', 'Verification code resent', 'Failed to resend code. Please try again.');
    });

    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const button = input.nextElementSibling;
        if (input.type === 'password') {
            input.type = 'text';
            button.innerHTML = '<i data-lucide="eye-off" class="w-5 h-5"></i>';
        } else {
            input.type = 'password';
            button.innerHTML = '<i data-lucide="eye" class="w-5 h-5"></i>';
        }
        if (window.lucide && lucide.createIcons) lucide.createIcons();
    }

    function checkPasswordStrength(password, id = 'register-password') {
        const meter = document.querySelector(id === 'register-password' ? '.password-strength-meter-fill' : '#reset-password-form .password-strength-meter-fill');
        const text = document.querySelector(id === 'register-password' ? '.password-strength-text' : '#reset-password-form .password-strength-text');
        if (!password) {
            meter.style.width = '0%';
            meter.style.backgroundColor = '#e0e0e0';
            text.textContent = '';
            return;
        }
        let strength = 0;
        let feedback = [];
        if (password.length >= 8) { strength += 25; } else { feedback.push('at least 8 characters'); }
        if (/[A-Z]/.test(password)) { strength += 25; } else { feedback.push('uppercase letter'); }
        if (/[a-z]/.test(password)) { strength += 25; } else { feedback.push('lowercase letter'); }
        if (/[0-9]/.test(password)) { strength += 12.5; } else { feedback.push('number'); }
        if (/[^A-Za-z0-9]/.test(password)) { strength += 12.5; } else { feedback.push('special character'); }
        meter.style.width = strength + '%';
        if (strength < 40) {
            meter.style.backgroundColor = '#f44336';
            text.textContent = 'Weak password';
        } else if (strength < 70) {
            meter.style.backgroundColor = '#ff9800';
            text.textContent = 'Moderate password';
        } else {
            meter.style.backgroundColor = '#4caf50';
            text.textContent = 'Strong password';
        }
        if (feedback.length > 0) {
            text.textContent += ' - Add ' + feedback.join(', ');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderOtpInputs('email-otp');
        renderOtpInputs('phone-otp');
        renderOtpInputs('reset-otp');
        if (window.lucide && lucide.createIcons) lucide.createIcons();
        const phoneInput = document.querySelector("#phone");
        if (phoneInput) {
            window.intlTelInput(phoneInput, {
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
                preferredCountries: ["ug", "ke", "tz", "rw"],
                separateDialCode: true,
                initialCountry: "auto",
                geoIpLookup: function (callback) {
                    fetch("https://ipapi.co/json")
                        .then(res => res.json())
                        .then(data => callback(data.country_code))
                        .catch(() => callback("ug"));
                }
            });
        }
        const forgotPhoneInput = document.querySelector("#forgot-phone");
        if (forgotPhoneInput) {
            window.intlTelInput(forgotPhoneInput, {
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
                preferredCountries: ["ug", "ke", "tz", "rw"],
                separateDialCode: true,
                initialCountry: "auto",
                geoIpLookup: function (callback) {
                    fetch("https://ipapi.co/json")
                        .then(res => res.json())
                        .then(data => callback(data.country_code))
                        .catch(() => callback("ug"));
                }
            });
        }
    });

    function showForgotPasswordFormEntry(m) {
        showForgotPasswordForm(m);
    }
</script>