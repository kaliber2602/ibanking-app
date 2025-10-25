from fastapi import FastAPI
from frontend_service.pages import router as pages_router
from auth_service.routes import router as auth_router
from user_service.routes import router as user_router
from otp_service.routes import router as otp_router
from transaction_service.routes import router as trans_router
from student_service.routes import router as student_router

app = FastAPI()

# Mount static files
from fastapi.staticfiles import StaticFiles
app.mount("/fe/css", StaticFiles(directory="fe/css"), name="css")
app.mount("/fe/js", StaticFiles(directory="fe/js"), name="js")
app.mount("/fe/imgs", StaticFiles(directory="fe/imgs"), name="imgs")

# Include frontend routes
app.include_router(pages_router)

# Include routers
app.include_router(pages_router)
app.include_router(auth_router)
app.include_router(user_router)
app.include_router(otp_router)
app.include_router(trans_router)
app.include_router(student_router)
