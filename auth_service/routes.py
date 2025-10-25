from fastapi import APIRouter
from fastapi.responses import JSONResponse
from models.schemas import LoginRequest, UsernameRequest, ResetPasswordRequest
import httpx

router = APIRouter()

# Service URLs
AUTH_SERVICE_URL = "http://localhost/ibanking-app/be/php-backend/auth.php"
CHECK_USERNAME_URL = "http://localhost/ibanking-app/be/php-backend/check-username.php"
RESET_PASSWORD_URL = "http://localhost/ibanking-app/be/php-backend/reset-password.php"


# Auth
@router.post("/login")
async def login(payload: LoginRequest):
    try:
        async with httpx.AsyncClient() as client:
            resp = await client.post(AUTH_SERVICE_URL, json=payload.dict(), timeout=3.0)
            result = resp.json()
    except httpx.RequestError as e:
        return JSONResponse(content={"success": False, "message": f"Lỗi kết nối Auth Service: {str(e)}"}, status_code=500)

    if result.get("success"):
        return JSONResponse(content={"success": True, "redirect": "/dashboard", "username": payload.username})
    return JSONResponse(content={"success": False, "message": result.get("message", "Đăng nhập thất bại.")}, status_code=401)

# Check Username
@router.post("/check-username")
async def check_username(payload: UsernameRequest):
    try:
        async with httpx.AsyncClient() as client:
            resp = await client.post(CHECK_USERNAME_URL, json=payload.dict(), timeout=3.0)
            result = resp.json()
    except httpx.RequestError as e:
        return JSONResponse(content={"success": False, "message": f"Lỗi kết nối: {str(e)}"}, status_code=500)
    return JSONResponse(content=result)

# Reset Password
@router.post("/reset-password")
async def reset_password(payload: ResetPasswordRequest):
    try:
        async with httpx.AsyncClient() as client:
            resp = await client.post(RESET_PASSWORD_URL, json=payload.dict())
        return JSONResponse(content=resp.json(), status_code=resp.status_code)
    except Exception:
        return JSONResponse(content={"success": False, "message": "Phản hồi không hợp lệ từ PHP"}, status_code=500)
