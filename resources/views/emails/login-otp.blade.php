<!DOCTYPE html>
<html>
<head>
    <title>Login OTP Code</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #fff; padding: 20px; border-radius: 8px;">
        <h2 style="color: #333; text-align: center;">Login Verification Code</h2>
        <p style="color: #555; text-align: center;">Your one-time password (OTP) for login is:</p>
        <div style="margin: 20px 0; text-align: center;">
            <span style="display: inline-block; font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #006EC4; background-color: #f0f8ff; padding: 10px 20px; border-radius: 4px; border: 1px solid #cce5ff;">
                {{ $otp }}
            </span>
        </div>
        <p style="color: #777; text-align: center; font-size: 14px;">This code will expire in 5 minutes.</p>
        <p style="color: #999; text-align: center; font-size: 12px; margin-top: 30px;">If you did not request this code, please ignore this email.</p>
    </div>
</body>
</html>
