CREATE DATABASE
IF NOT EXISTS ibanking;
USE ibanking;

-- Bảng Customer: Lưu thông tin sinh viên/người nộp tiền
CREATE TABLE Customer
(
    username VARCHAR(20) PRIMARY KEY,
    -- mã số sinh viên, dùng làm username
    password VARCHAR(100) NOT NULL,
    -- mật khẩu
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    balance DECIMAL(18,2) DEFAULT 0
    -- số dư khả dụng
);

-- Bảng Payment: Thông tin học phí cần nộp của từng sinh viên
CREATE TABLE Payment
(
    payment_id INT
    AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR
    (20) unique, -- mã số sinh viên hiển thị
    full_name VARCHAR
    (100), -- họ tên sinh viên
    faculty VARCHAR
    (100), -- khoa (tiếng Anh)
    amount DECIMAL
    (18,2) NOT NULL, -- số tiền học phí cần nộp
    semester VARCHAR
    (20), -- học kỳ
    description VARCHAR
    (255),
    status ENUM
    ('unpaid', 'paid') DEFAULT 'unpaid');

    -- Bảng Transaction: Lưu lịch sử các giao dịch thanh toán
    CREATE TABLE Transaction
    (
        transaction_id INT
        AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR
        (20), -- mã số sinh viên
    payment_id INT, -- liên kết đến học phí đã thanh toán
    amount DECIMAL
        (18,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM
        ('success', 'failed') DEFAULT 'success',
    FOREIGN KEY
        (username) REFERENCES Customer
        (username),
    FOREIGN KEY
        (payment_id) REFERENCES Payment
        (payment_id)
);

        -- Thêm 1 sinh viên vào bảng Customer
        INSERT INTO Customer
            (username, password, full_name, phone, email, balance)
        VALUES
            (
                '523h0094',
                '12345678',
                'Nguyen Van A',
                '0912345678',
                '523H0094@student.tdtu.edu.vn',
                5000000
);

        -- Thêm thông tin học phí cần nộp cho sinh viên này vào bảng Payment
        INSERT INTO Payment
            ( student_id, full_name, faculty, amount, semester, description, status)
        VALUES
            (
                '523h0094',
                'Nguyen Van A',
                'Information Technology',
                3500000,
                '2025-1',
                'Học phí học kỳ 1 năm 2025',
                'unpaid'
);

        -- Thêm một giao dịch thanh toán mẫu
        INSERT INTO Transaction
            (username, payment_id, amount, status)
        VALUES
            (
                '523h0094',
                1,
                3500000,
                'success'
);
