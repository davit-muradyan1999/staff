<!DOCTYPE html>
<html>
<head>
    <title>Staff Stayl</title>
</head>
<body>
    <h1>Подтвердите адрес электронной почты</h1>
    <strong>Перейдите по ссылке в этом сообщении, чтобы активировать учетную запись</strong>
    <a target="_blank" href="{{ env('APP_URL') }}/verify/email/{{ $verifyToken }}">{{ env('APP_URL') }}/verify/email/{{ $verifyToken }}</a>
</body>
</html>
