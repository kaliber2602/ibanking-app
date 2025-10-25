IBANKING APP - SETUP AND RUN GUIDE
==================================

1. OVERVIEW
------------
The iBanking App is built using a microservices architecture where each service runs independently with FastAPI and PHP (via XAMPP).
It simulates an online banking system supporting concurrent payments and OTP/email verification.

2. SYSTEM REQUIREMENTS
----------------------
Before starting, ensure you have the following installed:
- Python 3.10 or higher
- pip (Python package manager)
- XAMPP (Apache + MySQL)
- PHP 8.x
- Git (optional)

3. DATABASE SETUP
-----------------
1. Open XAMPP Control Panel and start Apache and MySQL.
2. Open phpMyAdmin at: http://localhost/phpmyadmin
3. Create a new database named 'ibanking'.
4. Import the SQL file 'ibanking.sql' into this database.

   Example path: /be/database/ibanking.sql

4. INSTALL PYTHON DEPENDENCIES
------------------------------
Open PowerShell or Terminal in the project root directory and run:

    pip install fastapi
    pip install "uvicorn[standard]"
    pip install pydantic
    pip install httpx
    pip install fastapi[all]

5. RUN FASTAPI MICROSERVICES
-----------------------------
Each service runs independently on a separate port.
Open multiple terminal windows or tabs and run:

    python -m uvicorn frontend_service.main:app --port 8000 --reload
    python -m uvicorn auth_service.main:app --port 8001 --reload
    python -m uvicorn user_service.main:app --port 8002 --reload
    python -m uvicorn otp_service.main:app --port 8003 --reload
    python -m uvicorn transaction_service.main:app --port 8004 --reload
    python -m uvicorn student_service.main:app --port 8005 --reload

Note:
- Each service must run on a different port.
- Ensure that none of the ports are already in use.

6. EMAIL SERVICE CONFIGURATION
------------------------------
Before running the system, make sure that in the OTP service:
- A valid email token (API key or access token) has been added. (inside utils folder: get_oauth_token.php, sendEmail.php)
- The configuration is saved in the config or .env file inside 'otp_service'.

7. DEFAULT TEST ACCOUNT
-----------------------
A sample test account is available:

    Username: 523h0094
    Password: lol2602

8. RUNNING CONCURRENT PAYMENT TEST
----------------------------------
This test simulates two users performing concurrent payments on the same transaction.

Steps:
1. Open PowerShell or Terminal and navigate to the test folder:

       cd C:\xampp\htdocs\ibanking-app\be\php-backend\tests

2. Run the example test:

       C:\xampp\php\php.exe concurrent_payment_test.php --url="http://localhost/ibanking-app/be/php-backend/process-transaction.php" --payment_id=1 --amount=3500000 --users=payer_a,payer_b --iterations=10 --delay_ms=10

Parameters:
- --payment_id : The target payment or transaction ID
- --amount     : Amount to be paid
- --users      : List of payers (comma-separated)
- --iterations : Number of times each user performs the transaction
- --delay_ms   : Delay (in milliseconds) between each request

9. NOTES
--------
- Ensure Apache and MySQL are running in XAMPP.
- Start all FastAPI services before running tests.
- If you face CORS or connection issues, check your ports and BASE_URL configuration.

10. SERVICE SUMMARY
-------------------
| Service Name         | Technology | Port | Description                |
|----------------------|-------------|------|----------------------------|
| frontend_service     | FastAPI     | 8000 | Frontend interface         |
| auth_service         | FastAPI     | 8001 | Authentication & Login     |
| user_service         | FastAPI     | 8002 | User management            |
| otp_service          | FastAPI     | 8003 | OTP and Email service      |
| transaction_service  | FastAPI     | 8004 | Transaction processing     |
| student_service      | FastAPI     | 8005 | Student management         |
| php-backend          | PHP (XAMPP) | N/A  | Payment integration layer  |

End of File
