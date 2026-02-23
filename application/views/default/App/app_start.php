<?php
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/functions");

if (isset($_COOKIE['login_session'])) {
    load_url("dashboard", 2);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
/* Splash */
body, html { height: 100%; margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
#splash {
    position: fixed; top:0; left:0; width:100%; height:100%; background:#006964; display:flex; justify-content:center; align-items:center; z-index:9999; opacity:1; transition: opacity 0.5s;
}
.logo-circle { width:200px; height:200px; background:#fff; border-radius:50%; display:flex; justify-content:center; align-items:center; box-shadow:0 4px 20px rgba(0,0,0,0.2); animation:zoomIn 1.5s ease; }
.logo-circle img { width:100%; max-width:220px; height:auto; }
@keyframes zoomIn { 0% {transform:scale(0.3); opacity:0;} 100% {transform:scale(1); opacity:1;} }

/* Login */
body { background:#006964 !important; }
.login-container { min-height: 100vh; }
.card { border:none; border-radius:16px; box-shadow:0 8px 24px rgba(0,0,0,0.1); }
.form-control { border-radius:10px; }
.btn-primary { border-radius:10px; background:#006964 !important; border:none; }
.btn-primary:hover { background:#006964 !important; }
.brand-logo { max-width:160px; }
.login-form { max-width:420px; width:100%; }
</style>
</head>
<body>

<!-- Splash -->
<div id="splash">
    <div class="logo-circle">
        <img src="uploads/<?php echo $set_logo; ?>" alt="Logo">
    </div>
</div>

<!-- Login Form -->
<div class="container-fluid login-container d-flex align-items-center justify-content-center">
    <div class="card p-4 login-form">
        <div class="text-center mb-4">
            <img src="uploads/<?php echo $set_logo; ?>" alt="Logo" class="brand-logo mb-2">
            <h4 class="fw-bold">Connexion à votre compte</h4>
        </div>

        <div id="data_result"></div>

        <form id="loginForm" method="post" action="get_login">
            <div class="mb-3">
                <label for="loginEmail" class="form-label">Adresse e-mail</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input id="loginEmail" name="email" type="email" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="loginPassword" class="form-label">Mot de passe</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input id="loginPassword" name="password" type="password" class="form-control" required>
                </div>
            </div>

            <!-- Hidden tokens -->
            <input type="hidden" name="firebase_token" id="firebase_token">
            <input type="hidden" name="onesignal_token" id="onesignal_token">

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary btn-lg">Se connecter</button>
            </div>

            <p class="text-center text-muted mb-0">
            </p>
        </form>

        <!-- Hidden button for automatic token fill -->
        <button id="fillTokensBtn" style="display:none;">Fill Tokens</button>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Splash fade
window.addEventListener('load', function() {
    setTimeout(function() {
        const splash = document.getElementById('splash');
        splash.style.opacity = 0;
        setTimeout(() => splash.style.display = 'none', 500);
    }, 2000);
});

// Automatic token fill function
document.getElementById('fillTokensBtn').addEventListener('click', function() {
    if (window.firebaseplayerid) {
        document.getElementById('firebase_token').value = window.firebaseplayerid;
    }
    if (window.onesignalplayerid) {
        document.getElementById('onesignal_token').value = window.onesignalplayerid;
    }
    formReady = true;
    console.log("Tokens remplis automatiquement !");
});

// Trigger the hidden button automatically
// formReady = true par défaut, Firebase/OneSignal enrichissent si disponibles
let formReady = true;
setInterval(() => {
    if (window.firebaseplayerid || window.onesignalplayerid) {
        $('#fillTokensBtn').trigger('click');
    }
}, 500);

// AJAX login with token check
$('#loginForm').on('submit', function(e) {
    e.preventDefault();

    if (!formReady) {
        alert("Veuillez patienter jusqu'à ce que les tokens soient prêts...");
        return;
    }

    var formData = $(this).serialize();
    $.post('get_login', formData, function(response) {
        try {
            const res = JSON.parse(response);
            $('#data_result').html(`<div class="alert alert-${res.status}">${res.message}</div>`);
            if(res.status === "success") {
                window.location.href = "dashboard";
            }
        } catch(e) {
            $('#data_result').html(response);
        }
    });
});
</script>

</body>
</html>
