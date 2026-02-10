<?php $styles = ob_get_clean();
ob_start(); ?>
<style>
    :root {
        --portal-accent: #159aff;
        --portal-success: #10b981;
        --text-main: #313949;
        --text-muted: #616e88;
        --bg-color: #ffffff;
    }

    body.authentication-background {
        background: var(--bg-color) !important;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .auth-page-container {
        max-width: 450px;
        margin: 0 auto;
        padding: 60px 20px;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .portal-logo-section {
        margin-bottom: 40px;
        text-align: center;
    }

    .portal-logo-img {
        height: 38px;
    }

    .auth-card-clean {
        background: #ffffff;
        padding: 40px;
        border: 1px solid #e1e6ef;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .auth-heading {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 8px;
        text-align: center;
    }

    .auth-description {
        font-size: 15px;
        color: var(--text-muted);
        margin-bottom: 32px;
        text-align: center;
    }

    .form-group-custom {
        margin-bottom: 24px;
        position: relative;
    }

    .form-label-custom {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .clean-input {
        width: 100%;
        padding: 14px 16px;
        font-size: 16px;
        color: var(--text-main);
        background: #f8fafc;
        border: 1px solid #d1d9e6;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .clean-input:focus {
        background: #ffffff;
        border-color: var(--portal-success);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        outline: none;
    }

    .clean-input.otp-field {
        width: 50px;
        height: 60px;
        text-align: center;
        font-size: 24px;
        font-weight: 700;
        padding: 0;
        margin: 0 4px;
    }

    .btn-action-primary {
        width: 100%;
        padding: 14px;
        font-size: 16px;
        font-weight: 600;
        color: #ffffff;
        background-color: var(--portal-success);
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-action-primary:hover {
        background-color: #059669;
    }

    .btn-action-primary:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .portal-auth-footer {
        margin-top: 32px;
        text-align: center;
        font-size: 13px;
        color: var(--text-muted);
    }

    .portal-auth-footer a {
        color: var(--portal-success);
        text-decoration: none;
        font-weight: 600;
    }

    .portal-auth-footer a:hover {
        text-decoration: underline;
    }

    #otp-verify-section .auth-description strong {
        color: var(--text-main);
    }

    /* Animation */
    .fade-in-up {
        animation: fadeInUp 0.4s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
<?php $styles .= ob_get_clean();
$pageTitle = 'Secure Access Portal';
ob_start(); ?>

<div class="auth-page-container">
    <div class="portal-logo-section">
        <img src="<?= asset('images/brand-logos/toggle-logo.png') ?>" alt="HMS Logo" class="portal-logo-img">
    </div>

    <div class="auth-card-clean fade-in-up">
        <!-- Step 1: Mobile Input -->
        <form id="otp-input-section" class="needs-validation" novalidate onsubmit="sendOTP(event)">
            <h1 class="auth-heading">Sign in</h1>
            <p class="auth-description">Enter your mobile number to access your medical records</p>

            <div class="form-group-custom">
                <label class="form-label-custom">Mobile Number</label>
                <div style="display: flex; gap: 8px;">
                    <div
                        style="padding: 14px; border: 1px solid #d1d9e6; border-radius: 8px; background: #f1f5f9; font-weight: 600;">
                        +91</div>
                    <input type="tel" class="clean-input" id="mobile_number" name="mobile" placeholder="99456XXXXX"
                        maxlength="10" required pattern="[0-9]{10}">
                    <div class="invalid-feedback">Valid 10-digit mobile number required.</div>
                </div>
            </div>

            <button type="submit" class="btn-action-primary" id="send-otp-btn">
                Get One Time Password <i class="ri-arrow-right-line"></i>
            </button>
        </form>

        <!-- Step 2: OTP Verification -->
        <div id="otp-verify-section" class="d-none">
            <h1 class="auth-heading">Verify OTP</h1>
            <p class="auth-description">Enter the 6-digit code sent to <strong id="sent-to-number"></strong></p>

            <div class="form-group-custom" style="display: flex; justify-content: center; margin-bottom: 32px;">
                <input type="text" class="clean-input otp-field" maxlength="1" id="otp1"
                    onkeyup="moveFocus(this, 'otp2')">
                <input type="text" class="clean-input otp-field" maxlength="1" id="otp2"
                    onkeyup="moveFocus(this, 'otp3')">
                <input type="text" class="clean-input otp-field" maxlength="1" id="otp3"
                    onkeyup="moveFocus(this, 'otp4')">
                <input type="text" class="clean-input otp-field" maxlength="1" id="otp4"
                    onkeyup="moveFocus(this, 'otp5')">
                <input type="text" class="clean-input otp-field" maxlength="1" id="otp5"
                    onkeyup="moveFocus(this, 'otp6')">
                <input type="text" class="clean-input otp-field" maxlength="1" id="otp6">
            </div>

            <button class="btn-action-primary" id="verify-otp-btn" onclick="verifyOTP()">
                Verify & Sign in
            </button>

            <div style="text-align: center; margin-top: 24px;">
                <button class="btn"
                    style="background: none; border: none; color: var(--portal-success); font-weight: 600; font-size: 14px;"
                    onclick="showMobileInput()">Back to Mobile Number</button>
            </div>
        </div>
    </div>

    <div class="portal-auth-footer">
        <p>© <?= date('Y') ?> <?= e($_ENV['APP_NAME'] ?? 'Melina HMS') ?>. All rights reserved.</p>

    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function moveFocus(current, nextId) {
        if (current.value.length === 1) {
            const next = document.getElementById(nextId);
            if (next) next.focus();
        }
    }

    function showMobileInput() {
        document.getElementById('otp-input-section').classList.remove('d-none');
        document.getElementById('otp-verify-section').classList.add('d-none');
    }

    async function sendOTP(e) {
        if (e) e.preventDefault();
        const mobile = document.getElementById('mobile_number').value;
        if (!mobile || mobile.length < 10) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Number',
                text: 'Please enter a valid 10-digit mobile number',
                confirmButtonColor: '#10b981'
            });
            return;
        }

        const btn = document.getElementById('send-otp-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';

        try {
            const response = await fetch('/api/v1/auth/otp/generate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mobile_number: mobile, purpose: 'login' })
            });
            const result = await response.json();

            if (result.success) {
                document.getElementById('sent-to-number').innerText = '+91 ' + mobile;
                document.getElementById('otp-input-section').classList.add('d-none');
                document.getElementById('otp-verify-section').classList.remove('d-none');
                setTimeout(() => document.getElementById('otp1').focus(), 100);
            } else {
                Swal.fire({
                    title: 'Access Denied',
                    text: result.message || 'Mobile number not found.',
                    icon: 'warning',
                    confirmButtonColor: '#10b981'
                });
            }
        } catch (e) {
            Swal.fire('Error', 'Unable to reach authentication server.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Get One Time Password <i class="ri-arrow-right-line"></i>';
        }
    }

    async function verifyOTP() {
        const mobile = document.getElementById('mobile_number').value;
        const otpArr = [];
        for (let i = 1; i <= 6; i++) { otpArr.push(document.getElementById(`otp${i}`).value); }
        const otp = otpArr.join('');

        if (otp.length < 6) return;

        const btn = document.getElementById('verify-otp-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Verifying...';

        try {
            const response = await fetch('/api/v1/auth/otp/verify', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mobile_number: mobile, otp_code: otp, purpose: 'login' })
            });
            const result = await response.json();

            if (result.success) {
                window.location.href = `<?= baseUrl('/login/otp-callback') ?>?token=${result.data.access_token}`;
            } else {
                Swal.fire({
                    title: 'Invalid OTP',
                    text: 'The code you entered is incorrect.',
                    icon: 'error',
                    confirmButtonColor: '#10b981'
                });
            }
        } catch (e) {
            Swal.fire('Auth Error', 'Could not verify your access code.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Verify & Sign in';
        }
    }
</script>
<?php $scripts = ob_get_clean();
include __DIR__ . '/../layouts/auth.php'; ?>