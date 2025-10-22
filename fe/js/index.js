document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.getElementById("loginForm");
  const forgotPasswordLink = document.getElementById("forgotPasswordLink");
  const loginMessage = document.getElementById("loginMessage");

  loginForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value;

    if (!username || !password) {
      loginMessage.textContent = "Vui lòng nhập đầy đủ username và mật khẩu.";
      loginMessage.className = "text-danger mt-2";
      return;
    }

    if (password.length < 8) {
      loginMessage.textContent = "Mật khẩu phải có ít nhất 8 ký tự.";
      loginMessage.className = "text-danger mt-2";
      return;
    }

    try {
      const response = await fetch("http://localhost:8001/login", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          username: username,
          password: password
        })
      });

      const result = await response.json();

      if (result.success && result.redirect) {
        localStorage.setItem("username", username);

        loginMessage.textContent = "Đăng nhập thành công! Đang chuyển hướng...";
        loginMessage.className = "text-success mt-2";

        setTimeout(() => {
          window.location.href = result.redirect;
        }, 2000);
      } else {
        loginMessage.textContent = result.message || "Đăng nhập thất bại.";
        loginMessage.className = "text-danger mt-2";
      }
    } catch (error) {
      loginMessage.textContent = "Lỗi kết nối tới máy chủ. Vui lòng thử lại sau.";
      loginMessage.className = "text-danger mt-2";
    }

  });

  if (forgotPasswordLink) {
    forgotPasswordLink.addEventListener("click", function (e) {
      e.preventDefault();
      window.location.href = "/forgot-password";
    });
  }
});
