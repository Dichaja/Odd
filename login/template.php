<div class="p-6 border-b">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold text-secondary"><?= getStepTitle($mode, $step) ?></h2>
        <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
    </div>

    <?php if ($mode === 'login' && $step === 'username'): ?>
        <?php if (!empty($message)): ?>
            <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-lg">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <p class="mb-4 text-center text-sm text-gray-600">Don't have an account?
            <a href="javascript:void(0)" onclick="switchAuthMode('register')" class="text-primary hover:text-red-700 font-medium">Create Account</a>
        </p>
    <?php elseif ($mode === 'register' && $step === 'username'): ?>
        <p class="mb-4 text-center text-sm text-gray-600">Already have an account?
            <a href="javascript:void(0)" onclick="switchAuthMode('login')" class="text-primary hover:text-red-700 font-medium">Sign In</a>
        </p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="" class="space-y-4" autocomplete="off" data-mode="<?= $mode ?>" data-step="<?= $step ?>">
        <?php if ($mode === 'login'): ?>
            <?php if ($step === 'username'): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="identifier" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your username or email" autofocus value="<?= htmlspecialchars($auth_state['data']['identifier'] ?? '') ?>" autocomplete="off">
                    </div>
                </div>
            <?php elseif ($step === 'password'): ?>
                <div class="relative">
                    <p class="mb-4">Logging in as <strong><?= htmlspecialchars($auth_state['data']['identifier']) ?></strong></p>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your password" id="login-password" autofocus autocomplete="off">
                        <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePasswordVisibility('login-password')"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="flex items-center justify-end">
                    <a href="javascript:void(0)" onclick="switchAuthMode('forgot_password')" class="text-sm text-primary hover:text-red-700">Forgot Password?</a>
                </div>
            <?php endif; ?>
        <?php elseif ($mode === 'register'): ?>
            <?php if ($step === 'username'): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="username" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Choose a username (letters only)" autofocus value="<?= htmlspecialchars($auth_state['data']['username'] ?? '') ?>" autocomplete="off" minlength="3" pattern="[a-zA-Z]+">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Username must be at least 3 characters and contain only letters.</p>
                </div>
            <?php elseif ($step === 'email'): ?>
                <div>
                    <p class="mb-4">Creating account for <strong><?= htmlspecialchars($auth_state['data']['username']) ?></strong></p>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your email" autofocus value="<?= htmlspecialchars($auth_state['data']['email'] ?? '') ?>" autocomplete="off">
                    </div>
                </div>
            <?php elseif ($step === 'verify_email'): ?>
                <div>
                    <p class="mb-4">We've sent a verification code to <strong><?= htmlspecialchars($auth_state['data']['email']) ?></strong></p>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Verification Code</label>
                    <div class="relative">
                        <i class="fas fa-key absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="email_otp" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter verification code" autofocus maxlength="6" autocomplete="off">
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        Didn't receive the code? <button type="button" class="text-primary hover:text-red-700" onclick="resendOTP('email')">Resend</button>
                    </p>
                    <p class="mt-2 text-xs text-gray-500">For demo purposes, the code is: <?= $auth_state['data']['email_otp'] ?? '' ?></p>
                </div>
            <?php elseif ($step === 'phone'): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required placeholder="Phone Number" class="w-full py-2 border border-gray-300 rounded-lg" autofocus value="<?= htmlspecialchars($auth_state['data']['phone'] ?? '') ?>" autocomplete="off">
                </div>
            <?php elseif ($step === 'verify_phone'): ?>
                <div>
                    <p class="mb-4">We've sent a verification code to <strong><?= htmlspecialchars($auth_state['data']['phone']) ?></strong></p>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Verification Code</label>
                    <div class="relative">
                        <i class="fas fa-key absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="phone_otp" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter verification code" autofocus maxlength="6" autocomplete="off">
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        Didn't receive the code? <button type="button" class="text-primary hover:text-red-700" onclick="resendOTP('phone')">Resend</button>
                    </p>
                    <p class="mt-2 text-xs text-gray-500">For demo purposes, the code is: <?= $auth_state['data']['phone_otp'] ?? '' ?></p>
                </div>
            <?php elseif ($step === 'password'): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Create a password" id="register-password" autofocus oninput="checkPasswordStrength(this.value)" autocomplete="off">
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
                        <input type="password" name="confirm_password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Confirm your password" id="register-confirm-password" autocomplete="off">
                        <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePasswordVisibility('register-confirm-password')"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="flex items-start">
                    <input type="checkbox" required class="mt-1 rounded border-gray-300 text-primary focus:ring-primary">
                    <span class="ml-2 text-sm text-gray-600">I agree to the
                        <a href="<?= BASE_URL ?>terms-and-conditions" class="text-primary hover:text-red-700">Terms of Service</a> and
                        <a href="#" class="text-primary hover:text-red-700">Privacy Policy</a>
                    </span>
                </div>
            <?php endif; ?>
        <?php elseif ($mode === 'forgot_password'): ?>
            <?php if ($step === 'select_method'): ?>
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
            <?php elseif ($step === 'email'): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your email" autofocus autocomplete="off">
                    </div>
                </div>
            <?php elseif ($step === 'phone'): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required placeholder="Phone Number" class="w-full py-2 border border-gray-300 rounded-lg" autofocus autocomplete="off">
                </div>
            <?php elseif ($step === 'verify_otp'): ?>
                <div>
                    <?php if (isset($auth_state['data']['reset_method']) && $auth_state['data']['reset_method'] === 'email'): ?>
                        <p class="mb-4">We've sent a verification code to <strong><?= htmlspecialchars($auth_state['data']['email']) ?></strong></p>
                    <?php else: ?>
                        <p class="mb-4">We've sent a verification code to <strong><?= htmlspecialchars($auth_state['data']['phone']) ?></strong></p>
                    <?php endif; ?>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
                    <div class="relative">
                        <i class="fas fa-key absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="reset_otp" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter verification code" autofocus maxlength="6" autocomplete="off">
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        Didn't receive the code? <button type="button" class="text-primary hover:text-red-700" onclick="resendOTP('reset')">Resend</button>
                    </p>
                    <p class="mt-2 text-xs text-gray-500">For demo purposes, the code is: <?= $auth_state['data']['reset_otp'] ?? '' ?></p>
                </div>
            <?php endif; ?>
        <?php elseif ($mode === 'reset_password'): ?>
            <?php if ($step === 'new_password'): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="new_password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter new password" id="new-password" autofocus oninput="checkPasswordStrength(this.value, 'new-password')" autocomplete="off">
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
                        <input type="password" name="confirm_new_password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Confirm new password" id="confirm-new-password" autocomplete="off">
                        <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePasswordVisibility('confirm-new-password')"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?= getFormButtons($mode, $step) ?>
    </form>

    <?php if ($mode === 'forgot_password' && $step === 'select_method'): ?>
        <p class="mt-4 text-center text-sm text-gray-600">Remember your password?
            <a href="javascript:void(0)" onclick="switchAuthMode('login')" class="text-primary hover:text-red-700 font-medium">Back to Sign In</a>
        </p>
    <?php endif; ?>
</div>