from fastapi import APIRouter, Query
from fastapi.responses import JSONResponse
import httpx

router = APIRouter()

FIND_STUDENT_URL = "http://localhost/ibanking-app/be/php-backend/find-student.php"

@router.get("/gateway/find-student.php")
async def find_student(id: str = Query(..., description="Mã số sinh viên")):
    try:
        async with httpx.AsyncClient() as client:
            resp = await client.get(f"{FIND_STUDENT_URL}?id={id}", timeout=3.0)
            result = resp.json()
    except httpx.RequestError as e:
        return JSONResponse(content={"success": False, "message": f"Lỗi kết nối Find Student Service: {str(e)}"}, status_code=500)

    if result.get("success"):
        return JSONResponse(content={"success": True, "data": result.get("data")})
    return JSONResponse(content={"success": False, "message": result.get("message", "Không tìm thấy sinh viên.")}, status_code=404)
