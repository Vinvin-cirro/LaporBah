
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - LaporBah!</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f7ff;
            color: #172441;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            width: 100%;
            min-height: 80px;
            background: #020d32;
            display: flex;
            align-items: center;
            padding: 0 6%;
        }

        .brand {
            text-decoration: none;
        }

        .brand h1 {
            color: #ffe45c;
            font-size: 25px;
            font-weight: 700;
        }

        .brand p {
            color: #ffffff;
            font-size: 11px;
            margin-top: 2px;
        }

        .main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 45px 20px;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border: 1px solid #dfe5f1;
            border-radius: 16px;
            padding: 36px;
        }

        .auth-heading {
            margin-bottom: 28px;
        }

        .auth-heading h2 {
            font-size: 28px;
            margin-bottom: 9px;
        }

        .auth-heading p {
            color: #586987;
            font-size: 14px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 19px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            height: 46px;
            border: 1px solid #cbd4e5;
            border-radius: 8px;
            padding: 0 13px;
            font-size: 14px;
            color: #172441;
            outline: none;
            background: #ffffff;
        }

        .form-control:focus {
            border-color: #020d32;
            box-shadow: 0 0 0 3px rgba(2, 13, 50, 0.08);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .error-text {
            display: block;
            color: #dc3545;
            font-size: 12px;
            margin-top: 6px;
        }

        .alert-error {
            background: #fff0f0;
            color: #b42318;
            border: 1px solid #ffd0d0;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .btn-primary {
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 9px;
            background: #020d32;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 5px;
        }

        .btn-primary:hover {
            background: #102451;
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: #586987;
        }

        .auth-footer a {
            color: #020d32;
            font-weight: 700;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .page-footer {
            padding: 22px 6%;
            background: #ffffff;
            border-top: 1px solid #dfe5f1;
            color: #586987;
            font-size: 12px;
            line-height: 1.7;
        }

        .page-footer strong {
            color: #172441;
            font-size: 14px;
        }

        @media (max-width: 480px) {
            .navbar {
                min-height: 70px;
            }

            .auth-card {
                padding: 27px 22px;
            }

            .auth-heading h2 {
                font-size: 24px;
            }
        }

        .brand-logo {
            height: 44px;
            width: auto;
            object-fit: contain;
            margin-right: 24px;
            filter: drop-shadow(0 0 3px rgba(255, 255, 255, 0.25))
            drop-shadow(0 0 7px rgba(255, 255, 255, 0.05));
        }
    </style>
</head>
<body>

    <header class="navbar">
        <img
            src="{{ asset('images/laporbah-logo.png') }}"
            alt="LaporBah!"
            class="brand-logo"
        >
        <a href="/" class="brand">
            <h1>LaporBah!</h1>
            <p>Untuk warga Medan</p>
        </a>
    </header>

    <main class="main">
        @yield('content')
    </main>

    <footer class="page-footer">
        <strong>LaporBah! · Bersama untuk Medan</strong>
        <p>Bukan layanan darurat. Utamakan keselamatan diri.</p>
    </footer>

</body>
</html>
