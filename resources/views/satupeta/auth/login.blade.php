<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN SATUPETA</title>
    <link rel="stylesheet" href="{{ satupeta_asset('vendor/bootstrap/css/bootstrap5.2.min.css') }}">
    <link rel="stylesheet" href="{{ satupeta_asset('css/login.css') }}">
</head>
<body>
    <section class="login">
        <div class="login_box">
            <div class="left">
                <div class="contact">
                    @if (session('pesan'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('pesan') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <form action="{{ route('satupeta.auth.proses_login_api') }}" method="post">
                        @csrf
                        <h3>SIGN IN</h3>
                        <input name="username" class="login-input" type="text" placeholder="NRP">
                        <input name="password" class="login-input" id="password" type="password" placeholder="PASSWORD">
                        <div>
                            <input type="checkbox" class="form-check-input" id="showpw">
                            <span class="ms-1" style="font-weight:600;font-size:14px;color:#696969;">show password</span>
                        </div>
                        <button type="submit" class="submit">LET'S GO</button>
                    </form>
                </div>
            </div>
            <div class="right">
                <div class="right-text">
                    <h2>SATUPETA</h2>
                    <h5>SISTEM INFORMASI PEMETAAN</h5>
                </div>
            </div>
        </div>
    </section>
    <script src="{{ satupeta_asset('vendor/bootstrap/js/jquery3.5.1.min.js') }}"></script>
    <script src="{{ satupeta_asset('vendor/bootstrap/js/bootstrap5.2.min.js') }}"></script>
    <script src="{{ satupeta_asset('js/login/login.js') }}"></script>
    <script src="{{ satupeta_asset('vendor/sweet-alert/sweetalert.min.js') }}"></script>
    @if (session('gagal'))
        <script>
            swal('Error!', @json(session('gagal')), 'error');
        </script>
    @endif
</body>
</html>
