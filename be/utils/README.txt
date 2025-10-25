Lab2_SOA - Email Sending Web App
================================

This project is a simple web application for sending HTML emails with a random embedded image using PHPMailer and Google OAuth2 SMTP, designed to run on XAMPP.

Requirements
------------
- XAMPP (Apache, PHP 7+)
- Composer (for PHP dependencies)
- Google account (for OAuth2 SMTP)


1. **Access the Web App**
	- Open your browser and go to: http://localhost/Lab2_SOA/fe/index.html

Usage
-----
1. Fill in the recipient email, subject, and message.
2. Click **Send**. The backend will send an HTML email with a random image embedded.
3. Success or error messages will be shown below the form.

Project Structure
-----------------
- `fe/index.html` - Frontend form for sending emails
- `sendEmail.php` - Backend PHP script for sending emails
- `fe/img/` - Folder for two PNG images used in emails
- `vendor/` - Composer dependencies (PHPMailer, OAuth2, etc.)

Troubleshooting
---------------
- Make sure Apache is running in XAMPP.
- If you get authentication errors, check your Google OAuth2 credentials and refresh token.
- For image issues, ensure both `img1.png` and `img2.png` exist in `fe/img/`.
