# frontend_service/main.py
from fastapi import FastAPI
from fastapi.staticfiles import StaticFiles
from frontend_service.pages import router as pages_router

app = FastAPI()

# Mount static files
app.mount("/fe/css", StaticFiles(directory="fe/css"), name="css")
app.mount("/fe/js", StaticFiles(directory="fe/js"), name="js")
app.mount("/fe/imgs", StaticFiles(directory="fe/imgs"), name="imgs")

# Include page routes
app.include_router(pages_router)
