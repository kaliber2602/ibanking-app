# transaction_service/main.py
from fastapi import FastAPI
from transaction_service.routes import router as trans_router
from starlette.middleware.cors import CORSMiddleware

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://127.0.0.1:8000"],  
    allow_methods=["*"],
    allow_headers=["*"],
)
app.include_router(trans_router)
