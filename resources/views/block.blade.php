<!DOCTYPE html>
<html>
    <head>
        <title>خطای دسترسی</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%;
                direction: rtl;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                color: #B0BEC5;
                display: table;
                font-weight: 100;
                font-family: 'Lato', sans-serif;
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 72px;
                margin-bottom: 40px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">لطفا اگر از VPN استفاده می‌کنید آن را قطع کنید.</div>
                <p>دسترسی به IP های غیر از ایران ممنوع است.</p>
                <p style="direction:ltr">#403 - Non Iran IPs are prohibited to access.</p>
                <p>IP: {{ \Request::ip() }}</p>
            </div>
        </div>
    </body>
</html>
