<?php
global $con;

include get_file("files/sql/get/os_settings");
include_once get_file("files/sql/get/functions");
define("page_title", " Mon compte");
include get_file("Admin/admin_header");


if (isset($_COOKIE['login_session'])) {
load_url("dashboard", 0);
}
?>






<style>

body {
background: #e8e8e8;
font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
min-height: 100vh;
display: flex;
align-items: center;
justify-content: center;
}


.login-wrapper {
position: relative;
width: 100%;
max-width: 500px;
padding-top: 60px; /* مساحة للشعار */
}

.brand-logo {
width: 180px;
height: auto;
margin:40px;
font-size:15px;
}

.login-card {
background: #fff;
border-radius: 0;
box-shadow: 0 12px 40px rgba(0,0,0,0.15);
padding: 60px 30px 40px; /* top padding كبير للشعار */
position: relative;
}

.login-card h4 {
font-weight: 700;
margin-bottom: 30px;
}

.form-control {
border-radius: 10px;
height: 48px;
}

.input-group-text {
background: #f1f1f1;
border: none;
border-radius: 10px 0 0 10px;
}


.text-link {
color: #4f46e5;
font-weight: 400;
text-decoration: none;
}

.text-link:hover {
text-decoration: underline;
}
</style>
</head>

<body>
<div class="login-wrapper text-center">

<img src="uploads/<?=$set_logo;?>" alt="Logo" class="brand-logo">

<div class="login-card">

<h4>Bienvenue à <?=$set_name;?>!</h4>
<p>Connectez-vous à votre compte <?=$set_name;?></p>

<div id="data_result"></div>

<form id="loginForm" method="post" action="get_login">
<div class="mb-3 text-start">
<label for="loginEmail" class="form-label">Adresse e-mail</label>
<div class="input-group">
<span class="input-group-text"><i class="fas fa-envelope"></i></span>
<input id="loginEmail" name="email" type="email" class="form-control" required>
</div>
</div>

<div class="mb-3 text-start">
<label for="loginPassword" class="form-label">Mot de passe</label>
<div class="input-group">
<span class="input-group-text"><i class="fas fa-lock"></i></span>
<input id="loginPassword" name="password" type="password" class="form-control" required>
</div>
</div>

<div class="d-grid mb-3">
<button type="submit" class="btn btn-primary btn-lg" style="border-radius:0rem">Se connecter</button>
</div>

<p class="mb-0">
Nouveau sur notre plateforme <?=$set_name;?> ?<br>
<a href="register" class="text-link"> Créer un compte</a>
</p>
</form>
</div>
</div>






<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
$('#loginForm').on('submit', function (e) {
e.preventDefault();
var formData = $(this).serialize();
$.post('get_login', formData, function(response) {
$('#data_result').html(response);
});
});
</script>
</body>
</html>
