<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Encargo Inkme</title>
</head>
<body>
    <p hidden>
        {{$url = "http://www.desarrolladorapp.com/inkme/public/activarCita/".$hash_identifier}}
    </p>

    <h1>¡Tienes un nuevo cliente!</h1>
    <h2>{{ $nombre }} quiere hablar contigo</h3>
    <h3>Teléfono de contacto: {{ $telefono }}</h3>
    <p>{{ $comentario }}</p>
    <p>Le gustaría que la fecha fuese: {{$date}}</p>
    <a href="{{$url}}" target="_blank"><button>Aceptar cita</button></a>

</body>
</html>
