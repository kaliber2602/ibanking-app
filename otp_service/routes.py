from fastapi import APIRouter
from fastapi.responses import JSONResponse
from models.schemas import EmailRequest, OTPRequest
import httpx

router = APIRouter()

SEND_OTP_URL = "http://localhost/ibanking-app/be/php-backend/send-otp.php"
VERIFY_OTP_URL = "http://localhost/ibanking-app/be/php-backend/verify-otp.php"

# Send OTP
@router.post("/send-otp")
async def send_otp(payload: EmailRequest):
    try:
        async with httpx.AsyncClient(timeout=30.0) as client:
            resp = await client.post(SEND_OTP_URL, json=payload.dict())
        return JSONResponse(content=resp.json(), status_code=resp.status_code)
    except Exception:
        return JSONResponse(
            content={"success": False, "message": "Phản hồi từ PHP không hợp lệ"},
            status_code=500
        )

@router.post("/verify-otp")
async def verify_otp(payload: OTPRequest):
    try:
        async with httpx.AsyncClient() as client:
            resp = await client.post(VERIFY_OTP_URL, json=payload.dict())
            print("PHP response:", resp.text)  # kiểm tra nội dung trả về
        return JSONResponse(content=resp.json(), status_code=resp.status_code)
    except Exception as e:
        print("Error:", e)
        return JSONResponse(content={"success": False, "message": "Phản hồi không hợp lệ"}, status_code=500)
