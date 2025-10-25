// Hàm load dữ liệu user từ Gateway
async function loadUserInfo() {
  const username = localStorage.getItem("username");
  if (!username) {
    window.location.href = "/"; // nếu chưa đăng nhập → về trang chủ
    return;
  }

  try {
    const response = await fetch(`http://localhost:8002/user-info?username=${encodeURIComponent(username)}`);
    const result = await response.json();

    if (result.success) {
      const user = result.data;
      document.getElementById("fullName").value = user.full_name;
      document.getElementById("phoneNumber").value = user.phone;
      document.getElementById("emailAddress").value = user.email;
      document.getElementById("balance").value = parseFloat(user.balance).toLocaleString() + " VND";
    } else {
      alert("Không thể tải thông tin người dùng.");
    }
  } catch (error) {
    alert("Lỗi kết nối tới máy chủ.");
  }
}

// Hàm tải lịch sử giao dịch
async function loadTransactionHistory() {
  const username = localStorage.getItem("username");
  if (!username) return;

  try {
    const response = await fetch("http://localhost:8004/transactions", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ username })
    });

    const result = await response.json();
    
    if (result.success) {
      const tableBody = document.getElementById("transactionTable");
      tableBody.innerHTML = "";

      result.data.forEach(tx => {
        const row = document.createElement("tr");
        row.innerHTML = `
          <td>${tx.transaction_id}</td>
          <td>${new Date(tx.created_at).toLocaleString()}</td>
          <td>${tx.username}</td>
          <td>${tx.payment_id ?? '-'}</td>
          <td>${parseFloat(tx.amount).toLocaleString()} VND</td>
          <td><span class="badge ${tx.status === 'success' ? 'bg-success' : 'bg-danger'}">${tx.status}</span></td>
        `;
        tableBody.appendChild(row);
      });
    } else {
      alert("Không thể tải lịch sử giao dịch.");
    }
  } catch (error) {
    alert("Lỗi khi tải giao dịch.");
  }
}

// Hàm gọi Gateway để tìm sinh viên theo ID
async function findStudentById(id) {
  try {
    const response = await fetch(`http://localhost:8005/find-student?id=${encodeURIComponent(id)}`);
    const result = await response.json();
    return result.success ? result.data : null;
  } catch (error) {
    console.error("Lỗi khi gọi find-student:", error);
    return null;
  }
}

// Hàm hiển thị popup thông tin sinh viên hoặc thông báo đã thanh toán
function showStudentPopup(student) {
  const oldPopup = document.getElementById("studentPopup");
  if (oldPopup) oldPopup.remove();

  if (student.status && student.status.toLowerCase() === 'paid') {
    const paidHtml = `
      <div id="studentPopup" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;z-index:9999;">
        <div style="background:#fff;padding:32px 24px;border-radius:8px;min-width:320px;max-width:90vw;box-shadow:0 2px 16px rgba(0,0,0,0.2);position:relative;">
          <button id="closePopupBtn" style="position:absolute;top:8px;right:8px;font-size:20px;background:none;border:none;cursor:pointer;">&times;</button>
          <h4 class="mb-3 text-danger">Học phí đã được thanh toán</h4>
          <p>Sinh viên <b>${student.full_name}</b> (MSSV: ${student.student_id}) đã hoàn tất thanh toán học phí.</p>
          <div class="d-flex justify-content-end mt-3">
            <button id="closePaidPopupBtn" class="btn btn-secondary">Đóng</button>
          </div>
        </div>
      </div>
    `;
    document.body.insertAdjacentHTML("beforeend", paidHtml);
    document.getElementById("closePopupBtn").onclick = () => document.getElementById("studentPopup").remove();
    document.getElementById("closePaidPopupBtn").onclick = () => document.getElementById("studentPopup").remove();
    document.getElementById("studentPopup").onclick = (e) => {
      if (e.target.id === "studentPopup") e.target.remove();
    };
    return;
  }

  const popupHtml = `
    <div id="studentPopup" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;z-index:9999;">
      <div style="background:#fff;padding:32px 24px;border-radius:8px;min-width:320px;max-width:90vw;box-shadow:0 2px 16px rgba(0,0,0,0.2);position:relative;">
        <button id="closePopupBtn" style="position:absolute;top:8px;right:8px;font-size:20px;background:none;border:none;cursor:pointer;">&times;</button>
        <h4 class="mb-3">Thông tin sinh viên</h4>
        <ul style="list-style:none;padding:0;">
          <li><b>Mã số sinh viên:</b> ${student.student_id}</li>
          <li><b>Họ và tên:</b> ${student.full_name}</li>
          <li><b>Khoa:</b> ${student.faculty}</li>
          <li><b>Học kỳ:</b> ${student.semester}</li>
          <li><b>Số tiền cần đóng học phí:</b> ${parseFloat(student.amount).toLocaleString()} VND</li>
        </ul>
        <div class="d-flex justify-content-end gap-2 mt-4">
          <button id="cancelPopupBtn" class="btn btn-secondary">Cancel</button>
          <button id="payPopupBtn" class="btn btn-primary">Thanh toán</button>
        </div>
      </div>
    </div>
  `;
  document.body.insertAdjacentHTML("beforeend", popupHtml);

  document.getElementById("closePopupBtn").onclick = () => document.getElementById("studentPopup").remove();
  document.getElementById("cancelPopupBtn").onclick = () => document.getElementById("studentPopup").remove();
  document.getElementById("studentPopup").onclick = (e) => {
    if (e.target.id === "studentPopup") e.target.remove();
  };

  document.getElementById("payPopupBtn").onclick = () => {
    localStorage.setItem("studentInfo", JSON.stringify(student));
    localStorage.setItem("studentId", student.student_id);
    window.location.href = "/confirm-transaction";
  };
}

// Hàm xử lý tìm kiếm sinh viên
async function handleStudentSearch() {
  const studentID = document.getElementById("studentID").value.trim();
  if (!studentID) {
    alert("Vui lòng nhập mã số sinh viên.");
    return;
  }

  const student = await findStudentById(studentID);
  console.log("Student data:", student);

  if (student) {
    showStudentPopup(student);
    document.getElementById("studentResult").innerHTML = "";
  } else {
    document.getElementById("studentResult").innerHTML =
      '<div class="alert alert-danger">Không tìm thấy sinh viên!</div>';
  }
}

// Khởi động khi DOM sẵn sàng
window.addEventListener("DOMContentLoaded", () => {
  loadUserInfo();
  loadTransactionHistory();

  const searchForm = document.getElementById("searchForm");
  if (searchForm) {
    searchForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      await handleStudentSearch();
    });

    const searchBtn = searchForm.querySelector('button[type="submit"]');
    if (searchBtn) {
      searchBtn.addEventListener("click", async function (e) {
        e.preventDefault();
        await handleStudentSearch();
      });
    }
  }
});

// Xử lý đăng xuất
const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn) {
  logoutBtn.addEventListener("click", () => {
    localStorage.removeItem("username");
    window.location.href = "/";
  });
}