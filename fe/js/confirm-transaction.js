document.addEventListener('DOMContentLoaded', async function () {
  const username = localStorage.getItem('username');
  const studentId = localStorage.getItem('studentId');

  if (!username || !studentId) {
    alert('Thiếu thông tin đăng nhập hoặc sinh viên.');
    return;
  }

  let user = null;
  let student = null;
  let generatedToken = "";
  let otpExpires = 0;
  let userEmail = "";

  try {
    // Bước 1: Lấy thông tin giao dịch
    const res = await fetch('http://localhost:8004/get-trans-info', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, student_id: studentId })
    });

    const result = await res.json();
    if (!result.success) {
      alert(result.message || 'Không thể lấy thông tin.');
      return;
    }

    user = result.data.user;
    student = result.data.student;

    // Bước 2: Hiển thị thông tin lên giao diện
    document.getElementById('userFullName').textContent = user.full_name;
    document.getElementById('userEmail').textContent = user.email;
    document.getElementById('userBalance').textContent = user.balance.toLocaleString() + ' VND';
    document.getElementById('studentId').textContent = student.student_id;
    document.getElementById('studentFullName').textContent = student.full_name;
    document.getElementById('studentClass').textContent = student.faculty + ' - ' + student.semester;
    document.getElementById('studentTuition').textContent = student.amount.toLocaleString() + ' VND';
    document.getElementById('confirmDate').textContent = new Date().toLocaleString('vi-VN');

    // Bước 3: Xử lý sự kiện xác nhận
    document.getElementById('confirmBtn').onclick = function () {
      document.getElementById('confirmBtn').disabled = true; // Ngăn click lại

      const ruleModal = new bootstrap.Modal(document.getElementById('ruleModal'));
      ruleModal.show();

      document.getElementById('agreeBtn').onclick = async function () {
        ruleModal.hide();

        try {
          userEmail = user.email;

          // Bước 4: Gửi OTP
          const otpRes = await fetch('http://localhost:8003/send-otp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: userEmail })
          });

          const otpResult = await otpRes.json();

          if (!otpResult.success) {
            alert(otpResult.message || 'Không thể gửi OTP.');
            return;
          }

          alert('Mã OTP đã được gửi đến email của bạn.');

          generatedToken = otpResult.token;
          otpExpires = otpResult.expires;

          // Hiển thị phần nhập OTP ngay dưới nút
          document.getElementById('otpSection').style.display = 'block';

          // Bước 5: Xác minh OTP
          document.getElementById('verifyOtpBtn').onclick = async function () {
            const otp = document.getElementById('otpInput').value.trim();
            if (!otp) {
              alert('Vui lòng nhập mã OTP.');
              return;
            }

            const verifyRes = await fetch('http://localhost:8003/verify-otp', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                email: userEmail,
                otp: otp,
                token: generatedToken,
                expires: otpExpires
              })
            });

            const verifyResult = await verifyRes.json();
            if (!verifyResult.success) {
              alert(verifyResult.message || 'Mã OTP không hợp lệ hoặc đã hết hạn.');
              return;
            }

            alert('Xác minh OTP thành công!');
            alert("Đang xử lý giao dịch...");

            // Bước 6: Xác nhận giao dịch
            const confirmRes = await fetch('http://localhost:8004/confirm-payment', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                username,
                student_id: student.student_id,
                amount: student.amount,
              })
            });
            // 
            const rawText = await confirmRes.text();
            console.log("Raw response:", rawText);

            let confirmResult;
            try {
              confirmResult = JSON.parse(rawText);
            } catch (e) {
              alert("Lỗi phân tích JSON từ FastAPI: " + e.message);
              return;
            }

              // 
            if (confirmRes.ok && confirmResult.success) {
              alert(confirmResult.message || 'Giao dịch thành công.');
              window.location.href = '/dashboard';
            } else {
              alert(confirmResult.message || 'Giao dịch thất bại.');
            }
          };
        } catch (err) {
          console.error(err);
          alert('Lỗi khi gửi hoặc xác minh OTP.');
        }
      };
    };
  } catch (error) {
    console.error(error);
    alert('Lỗi kết nối đến máy chủ.');
  }
});