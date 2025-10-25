from fastapi import APIRouter, Query
from fastapi.responses import JSONResponse
from models.schemas import UsernameRequest
import httpx

router = APIRouter()

CUSTOMER_INFO_URL = "http://localhost/ibanking-app/be/php-backend/customer-infor.php"
GET_EMAIL_URL = "http://localhost/ibanking-app/be/php-backend/get-email.php"

# Get User Info
@router.get("/user-info")
async def get_user_info(username: str = Query(..., description="Tên đăng nhập cần lấy thông tin")):
    try:
        async with httpx.AsyncClient() as client:
            resp = await client.post(CUSTOMER_INFO_URL, json={"username": username}, timeout=3.0)
            result = resp.json()
    except httpx.RequestError as e:
        return JSONResponse(content={"success": False, "message": f"Lỗi kết nối Customer Service: {str(e)}"}, status_code=500)

    if result.get("success"):
        return JSONResponse(content={"success": True, "data": result.get("data")})
    return JSONResponse(content={"success": False, "message": result.get("message", "Không thể lấy thông tin người dùng.")}, status_code=404)

# Get Email
@router.post("/get-email")
async def get_email(payload: UsernameRequest):
    try:
        async with httpx.AsyncClient() as client:
            resp = await client.post(GET_EMAIL_URL, json=payload.dict(), timeout=3.0)
            result = resp.json()
    except httpx.RequestError as e:
        return JSONResponse(content={"success": False, "message": f"Lỗi kết nối: {str(e)}"}, status_code=500)
    return JSONResponse(content=result)
