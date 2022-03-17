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
        {{-- {{$nombre = "luis"}}
        {{$telefono = "656 691 691"}}
        {{$comentario = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. In sed vulputate elit. Integer sed magna nisl. Nullam id vulputate enim, vitae posuere diam. Quisque luctus, velit ac volutpat mattis, libero justo ornare turpis, sed rhoncus quam libero eget lectus. Nam sed aliquet ipsum. Ut varius sapien volutpat enim ornare, id euismod massa viverra. Fusce et lobortis massa. Cras bibendum aliquet quam, id dignissim purus commodo quis. Fusce ut neque augue. Sed placerat nulla ac metus dignissim suscipit. Pellentesque sodales lorem purus, at egestas nisl pulvinar quis. Donec rutrum pulvinar purus at dictum. Pellentesque feugiat ex vitae fringilla vestibulum."}} --}}
        {{$url = "http://www.desarrolladorapp.com/inkme/public/activarCita/".$hash_identifier}}
    </p>

    {{-- nombre comentario telefono --}}
    <h1>¡Tienes un nuevo cliente!</h1>
    <h3>{{ $nombre }} quiere hablar contigo</h3>
    <p>{{ $telefono }}</p>
    <p>{{ $comentario }}</p>
    <a href="{{$url}}" target="_blank"><button>Aceptar cita</button></a>

</body>
</html>
