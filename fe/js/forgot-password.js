let generatedToken = "";
let otpExpires = 0;
let userEmail = "";
let username = ""; // ✅ Khai báo toàn cục để dùng xuyên suốt

// Xử lý form nhập username
document.getElementById("usernameForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    username = document.getElementById("username").value.trim();
    const errorDiv = document.getElementById("usernameError");

    try {
        // Bước 1: Kiểm tra username có tồn tại
        const checkResp = await fetch("http://localhost:8001/check-username", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ username })
        });
        const checkResult = await checkResp.json();

        if (!checkResult.success || !checkResult.exists) {
            errorDiv.textContent = "Username không tồn tại!";
            errorDiv.style.display = "block";
            return;
        }

        errorDiv.style.display = "none";

        // Bước 2: Lấy email từ database
        const emailResp = await fetch("http://localhost:8002/get-email", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ username })
        });
        const emailResult = await emailResp.json();

        if (!emailResult.success || !emailResult.email) {
            errorDiv.textContent = "Không thể lấy email của tài khoản.";
            errorDiv.style.display = "block";
            return;
        }

        alert("Email của bạn là: " + emailResult.email);
        userEmail = emailResult.email;

        // Bước 3: Gửi OTP qua backend
        const otpResp = await fetch("http://localhost:8003/send-otp", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email: userEmail })
        });
        const otpResult = await otpResp.json();

        if (!otpResult.success) {
            errorDiv.textContent = "Không thể gửi OTP.";
            errorDiv.style.display = "block";
            return;
        }

        generatedToken = otpResult.token;
        otpExpires = otpResult.expires;

        alert("OTP đã gửi tới email: " + userEmail);
        document.getElementById("section-username").style.display = "none";
        document.getElementById("section-otp").style.display = "block";

    } catch (err) {
        errorDiv.textContent = "Lỗi kết nối máy chủ.";
        errorDiv.style.display = "block";
    }
});

// Xử lý form nhập OTP
document.getElementById("otpForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    const otp = document.getElementById("otp").value.trim();
    const otpMsg = document.getElementById("otpMessage");

    alert("Verifying OTP: " + otp + " for email: " + userEmail + " with token: " + generatedToken + " expiring at: " + otpExpires);

    try {
        const verifyResp = await fetch("http://localhost:8003/verify-otp", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                email: userEmail,
                otp: otp,
                token: generatedToken,
                expires: otpExpires
            })
        });
        const verifyResult = await verifyResp.json();
        alert(JSON.stringify(verifyResult));

        if (verifyResult.success) {
            otpMsg.textContent = "OTP xác thực thành công! Bạn có thể đặt lại mật khẩu.";
            otpMsg.className = "text-success mt-2";

            setTimeout(() => {
                document.getElementById("section-otp").innerHTML = `
                <form id="resetPasswordForm">
                  <div class="mb-3">
                    <label for="newPassword" class="form-label">Nhập mật khẩu mới</label>
                    <input type="password" class="form-control" id="newPassword" placeholder="Mật khẩu mới" required autocomplete="new-password" />
                  </div>
                  <button type="submit" class="btn btn-success w-100">Đặt lại mật khẩu</button>
                  <div id="resetMsg" class="mt-2"></div>
                </form>
              `;

                document.getElementById("resetPasswordForm").addEventListener("submit", function (e) {
                    e.preventDefault();
                    const newPass = document.getElementById("newPassword").value.trim();
                    const resetMsg = document.getElementById("resetMsg");

                    console.log("Đang gửi yêu cầu đặt lại mật khẩu với:");
                    console.log("username:", username);
                    console.log("new_password:", newPass);

                    if (newPass.length < 8) {
                        resetMsg.textContent = "Mật khẩu phải tối thiểu 8 ký tự.";
                        resetMsg.className = "text-danger mt-2";
                    } else {
                        fetch("http://localhost:8001/reset-password", {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({
                                username: username,
                                new_password: newPass
                            })
                        })
                        .then(res => res.json())
                        .then(result => {
                            if (result.success) {
                                resetMsg.textContent = "Đổi mật khẩu thành công!";
                                resetMsg.className = "text-success mt-2";
                                setTimeout(() => {
                                    window.location.href = "/";
                                }, 1000);
                            } else {
                                resetMsg.textContent = result.message || "Không thể đổi mật khẩu.";
                                resetMsg.className = "text-danger mt-2";
                            }
                        })
                        .catch(() => {
                            resetMsg.textContent = "Lỗi kết nối máy chủ.";
                            resetMsg.className = "text-danger mt-2";
                        });
                    }
                });
            }, 1000);
        } else {
            otpMsg.textContent = "OTP không đúng hoặc đã hết hạn.";
            otpMsg.className = "text-danger mt-2";
        }
    } catch (err) {
        otpMsg.textContent = "Lỗi xác thực OTP.";
        otpMsg.className = "text-danger mt-2";
    }
});