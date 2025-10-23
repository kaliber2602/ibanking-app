from pydantic import BaseModel

# Models
class LoginRequest(BaseModel):
    username: str
    password: str

class UsernameRequest(BaseModel):
    username: str

class OTPRequest(BaseModel):
    email: str
    otp: str
    token: str
    expires: int

class EmailRequest(BaseModel):
    email: str

class ResetPasswordRequest(BaseModel):
    username: str
    new_password: str

class TransInfoRequest(BaseModel):
    username: str
    student_id: str
    
class ConfirmPaymentRequest(BaseModel):
    username: str
    student_id: str
    amount: float
