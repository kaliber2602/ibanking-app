pip install fastapi
pip install "uvicorn[standard]"  #bắt buộc để chạy ứng dụng FastAPI
pip install pydantic
pip install httpx
pip install fastapi[all]


python -m uvicorn frontend_service.main:app --port 8000 --reload
python -m uvicorn auth_service.main:app --port 8001 --reload
python -m uvicorn user_service.main:app --port 8002 --reload
python -m uvicorn otp_service.main:app --port 8003 --reload
python -m uvicorn transaction_service.main:app --port 8004 --reload
python -m uvicorn student_service.main:app --port 8005 --reload


Mở terminal (PowerShell / bash) trong tests và chạy ví dụ:
# chạy ví dụ: 2 user payer_a và payer_b, payment_id=1, 10 lần mỗi user, delay 10ms
cd C:\xampp\htdocs\ibanking-app\be\php-backend\tests
C:\xampp\php\php.exe concurrent_payment_test.php --url="http://localhost/ibanking-app/be/php-backend/process-transaction.php" --payment_id=1 --amount=3500000 --users=payer_a,payer_b --iterations=10 --delay_ms=10
Thay --payment_id hoặc --student_id, --amount, --users theo dữ liệu của bạn.