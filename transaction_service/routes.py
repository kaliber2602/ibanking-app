from fastapi import APIRouter
from fastapi.responses import JSONResponse
from models.schemas import UsernameRequest, TransInfoRequest, ConfirmPaymentRequest
import httpx

router = APIRouter()

TRANSACTION_SERVICE_URL = "http://localhost/ibanking-app/be/php-backend/transaction.php"
GET_TRANS_INFO_URL = "http://localhost/ibanking-app/be/php-backend/get-trans-infor.php"
CONFIRM_PAYMENT_URL = "http://localhost/ibanking-app/be/php-backend/process-transaction.php" 

# get transactions history
@router.post("/transactions")
async def get_transactions(payload: UsernameRequest):
    try:
        async with httpx.AsyncClient() as client:
            resp = await client.post(TRANSACTION_SERVICE_URL, json=payload.dict(), timeout=3.0)
            result = resp.json()
    except httpx.RequestError as e:
        return JSONResponse(content={"success": False, "message": f"Lỗi kết nối Transaction Service: {str(e)}"}, status_code=500)
    return JSONResponse(content=result)

# get trans info
@router.post("/get-trans-info")
async def get_trans_info(payload: TransInfoRequest):
    try:
        async with httpx.AsyncClient() as client:
            resp = await client.post(GET_TRANS_INFO_URL, json=payload.dict(), timeout=5.0)
            result = resp.json()
    except httpx.RequestError as e:
        return JSONResponse(content={"success": False, "message": f"Lỗi kết nối GetTransInfo Service: {str(e)}"}, status_code=500)

    if result.get("success"):
        return JSONResponse(content=result)
    return JSONResponse(content={"success": False, "message": result.get("message", "Không thể lấy thông tin.")}, status_code=404)

# confirm payment
@router.post("/confirm-payment")
async def confirm_payment(payload: ConfirmPaymentRequest):
    try:
        async with httpx.AsyncClient() as client:
            resp = await client.post(CONFIRM_PAYMENT_URL, json=payload.dict(), timeout=10.0)
            result = resp.json()
    except httpx.RequestError as e:
        return JSONResponse(content={"success": False, "message": f"Lỗi kết nối Gateway Service: {str(e)}"}, status_code=500)

    if result.get("success"):
        return JSONResponse(content=result)
    return JSONResponse(content={"success": False, "message": result.get("message", "Thanh toán thất bại.")}, status_code=400)
