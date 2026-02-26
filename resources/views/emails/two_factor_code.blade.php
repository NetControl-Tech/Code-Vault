<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رمز التحقق الثنائي</title>
</head>

<body
    style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="direction: rtl;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table border="0" cellpadding="0" cellspacing="0" width="600"
                    style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 40px 40px 20px 40px; background-color: #4f46e5;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 700;">رمز التحقق الثنائي
                                (2FA)</h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <p
                                style="margin: 0 0 20px 0; color: #475569; font-size: 16px; line-height: 1.6; text-align: center;">
                                مرحباً،
                                <br>
                                لقد قمت بمحاولة تسجيل الدخول إلى حسابك. يرجى استخدام الرمز التالي لإكمال العملية:
                            </p>

                            <!-- Code Box -->
                            <div
                                style="background-color: #f1f5f9; border-radius: 12px; padding: 30px; margin: 30px 0; text-align: center;">
                                <span
                                    style="display: block; font-size: 42px; font-weight: 800; color: #1e293b; letter-spacing: 12px; font-family: monospace;">{{ $code }}</span>
                            </div>

                            <p
                                style="margin: 0 0 20px 0; color: #64748b; font-size: 14px; line-height: 1.6; text-align: center; background-color: #fff7ed; padding: 12px; border-right: 4px solid #f97316; border-radius: 4px;">
                                💡 هذا الرمز صالح لمدة <strong>10 دقائق</strong> فقط.
                            </p>

                            <p
                                style="margin: 30px 0 0 0; color: #94a3b8; font-size: 13px; line-height: 1.6; text-align: center; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                                إذا لم تقم بطلب هذا الرمز، يرجى تجاهل هذه الرسالة أو التواصل مع فريق الدعم فوراً.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 0 40px 40px 40px;">
                            <p style="margin: 0; color: #cbd5e1; font-size: 12px; text-align: center;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. جميع الحقوق محفوظة.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>