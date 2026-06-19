<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auth SSO</title>
</head>
<body>
<script src="{{ satupeta_asset('vendor/bootstrap/js/jquery3.5.1.min.js') }}"></script>
<script>
    let newWindow = open('{{ $url_ci }}Auth/login_sso/{{ $kode_login }}/{{ $bearer }}?{{ $filter }}', '', 'width=1000,height=600');

    newWindow.onload = function() {
        window.setTimeout(function(){ window.close(); }, 1000);
    };
</script>
</body>
</html>
