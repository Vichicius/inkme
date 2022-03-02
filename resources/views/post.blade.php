<!DOCTYPE html>
<html lang="en">
<head>
    <title>Inkme</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="icon" href="http://www.desarrolladorapp.com/inkme/storage/app/public/archivos/yXoEjJnwnujR0nwMApPWVnKSkUOiZUirwxwRHs1Z.png">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap');

        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        *:focus{
            outline: none;
        }

        body{
            width: 100%;
            background: #fafafa;
            position: relative;
            font-family: 'roboto', sans-serif;
        }
        .user-profile{
            width: 22px;
            border-radius: 50%;
            background-image: url(img/profile-pic.png);
            background-size: cover;
        }
        .main{
            width: 100%;
            padding: 0px 0;
            display: flex;
            justify-content: center;
            margin-top: 0px;
        }

        .wrapper{
            width: 70%;
            max-width: 700px;
            min-width: 480px;
            max-height: 700px;
            min-height: 480px;
        }
        .profile-pic{
            width: 70px;
            height: 70px;
            border-radius: 50%;
            overflow: hidden;
            padding: 3px;
            background: linear-gradient(45deg, rgb(255, 230, 0), rgb(255, 0, 128) 80%);
        }

        .profile-pic img{
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .username{
            width: 100%;
            overflow: hidden;
            text-align: center;
            font-size: 12px;
            margin-top:5px;
            color: rgba(0, 0, 0, 0.5)
        }

        .post{
            width: 100%;
            height: auto;
            background: #fff;
            border: 1px solid #dfdfdf;
            margin-top: 40px;
            padding: 50px;
        }

        .info{
            width: 100%;
            height: 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .info .username{
            width: auto;
            font-weight: bold;
            color: #000;
            font-size: 14px;
            margin-left: 10px;
        }

        .info .options{
            height: 10px;
            cursor: pointer;
        }

        .info .user{
            display: flex;
            align-items: center;
        }

        .info .profile-pic{
            height: 40px;
            width: 40px;
            padding: 0;
            background: none;
        }

        .info .profile-pic img{
            border: none;
        }

        .post-image{
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        .post-content{
            width: 100%;
            padding: 20px;
        }


        .description{
            margin: 10px 0;
            font-size: 14px;
            line-height: 20px;
        }

        .description span{
            font-weight: bold;
            margin-right: 10px;
        }

        .post-time{
            color: rgba(0, 0, 0, 0.5);
            font-size: 12px;
        }







    </style>
</head>

<body>
    <section class="main">
        <div class="wrapper">
            <div class="post">
                <div class="info">
                    <div class="user">
                        <div class="profile-pic"><img src="{{ $fotoperfil }}" alt=""></div>
                        <p class="username">{{ $nombre }}</p>
                    </div>
                </div>

                <img src="{{ $fotopost }}" class="post-image" alt="">

                <div class="post-content">
                    <p class="description"><span>{{ $nombre }} </span> {{ $descripcion }}</p>
                    <p class="post-time">{{ $fecha }}</p>
                </div>
            </div>

        </div>
    </section>

</body>
</html>
