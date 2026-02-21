<?php
global $con;

include get_file("files/sql/get/os_settings");
include_once get_file("files/sql/get/functions");
define("page_title",  " La solution d’envoi de vos colis à domicile");

include get_file("Admin/admin_header");



$count_delivered = $con->query("SELECT COUNT(*) FROM orders WHERE or_state_delivery = 1")->fetchColumn();
$count_clients = $con->query("SELECT COUNT(*) FROM users WHERE user_rank = 'user' AND user_unlink = 0")->fetchColumn();
$count_cities = $con->query("SELECT COUNT(*) FROM city WHERE city_unlink = 0")->fetchColumn();


?>

<!-- Google Fonts + Material Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">




<style>
.bg-dark {
background-color: #006964 !important;
}
.btn-dark {
--bs-btn-color: #fff;
--bs-btn-bg: #006964 !important;
--bs-btn-border-color: #006964 !important;
--bs-btn-hover-color: #fff;
--bs-btn-hover-bg: #006964 !important;
--bs-btn-hover-border-color: #006964 !important;
--bs-btn-focus-shadow-rgb: 66, 70, 73;
--bs-btn-active-color: #fff;
--bs-btn-active-bg: #006964 !important;
--bs-btn-active-border-color: #006964 !important;
--bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
--bs-btn-disabled-color: #fff;
--bs-btn-disabled-bg: #006964 !important;
--bs-btn-disabled-border-color: #006964 !important;
}
.app-footer {
grid-area: lte-app-footer;
width: inherit;
max-width: 100vw;
min-height: 3rem;
padding: 1rem;
color: #fff;
background-color: #006964;
border-top: 1px solid #dee2e600;
transition: 0.3s ease-in-out;
}

body {
font-family: 'Poppins', sans-serif;
}
.navbar {
background-color: white !important;
box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}


.navbar.scrolled {
background-color: #fff !important;
box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.navbar .ki, .navbar .navbar-brand {
transition: color 0.3s;
color: #fff;
}
.navbar.scrolled .ki, .navbar.scrolled .navbar-brand {
color: #000 !important;
}

.hero-section {
position: relative;
background-size: cover;
background-position: center;
}
.hero-overlay {
background: rgba(0,0,0,0.5);
padding: 100px 0;
}
.hero-section h1 {
font-size: 2.5rem;
font-weight: bold;
}
.hero-section p {
font-size: 1.2rem;
}
.bCard {
border: none;
border-radius: 0.5rem;
box-shadow: 0 0 20px rgba(0,0,0,0.05);
transition: transform 0.3s, box-shadow 0.3s;
}
.bCard:hover {
transform: translateY(-10px);
box-shadow: 0 8px 30px rgba(0,0,0,0.1);
}
.form-select {
border-radius: 0.3rem;
}
.loader .progress {
background-color: #e9ecef;
}
.loader .progress-bar {
background-color: #007bff;
}
.footer {
background-color: #111;
padding: 40px 0;
}
.footer h4 {
font-weight: bold;
margin-bottom: 15px;
}
.footer h6 {
font-weight: normal;
margin-bottom: 10px;
}
.footer .copyright {
font-size: 0.9rem;
}



.whatsapp-float {
position: fixed;
width: 60px;
height: 60px;
bottom: 20px;
right: 20px;
background-color: #25D366;
color: #fff;
border-radius: 50%;
text-align: center;
font-size: 30px;
box-shadow: 2px 2px 3px #999;
z-index: 100;
display: flex;
align-items: center;
justify-content: center;
text-decoration: none;
}
.whatsapp-float:hover {
background-color: #20b858;
color: #fff;
}
</style>



<a href="https://wa.me/+212<?=$set_whatsapp;?>" 
class="whatsapp-float" 
target="_blank" 
title="Contact us on WhatsApp">
<i class="fab fa-whatsapp"></i>
</a>



<!-- Navbar -->
<nav class='navbar navbar-expand-lg m-0 fixed-top'>
<div class='container text-center'>
<a class='navbar-brand' href=''><img src='uploads/<?= $set_logo ?>' style='width:120px'></a>
<button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarNav'>
<i class='fa-solid fa-bars fa-2x text-primary'></i>
</button>
<div class='collapse navbar-collapse m-0' id='navbarNav'>
<ul class='navbar-nav ms-auto' style='align-items: center;'>
<li class='nav-item ki'><a class='nav-link' href=''>Accueil</a></li>
<li class='nav-item ki'><a class='nav-link' href='#service'>Nos services</a></li>
<li class='nav-item ki'><a class='nav-link' href='#pricing'>Tarifs</a></li>
<li class='nav-item mx-2'><a class='nav-link bg btn bg-dark text-white my-0 py-4 px-3' href='register' style='border-radius: 0rem;'>Inscription</a></li>
<li class='nav-item'><a class='nav-link bg btn btn-primary text-white bg-primary my-0 py-4 px-3' href='login_account' style='border-radius: 0rem;'><i class='fa-regular fa-user'></i> Espace Client</a></li>
</ul>
</div>
</div>
</nav>



<!-- Hero Section -->
<section class="hero-section" style="background-image: url('uploads/site/slider.jpg'); height:100%; background-size: cover; background-position: center;">
<div class="hero-overlay text-white d-flex align-items-center" style="background:#0000008c; height:100%;">
<div class="container text-center py-5" style='margin-top: 120px;'>
<h1 class="mb-3 fw-bold display-5">Votre colis, notre promesse de livraison</h1>
<p class="lead mb-4">Livraison rapide & sûre</p>
<p class="mb-4">
Simplifiez vos opérations avec notre solution intégrée tout-en-un, et bénéficiez d’une livraison rapide sous 24h vers la majorité des villes du Maroc, tout en offrant une expérience d’achat exceptionnelle à vos clients.
</p>

<form id="tracking-form" class="d-flex justify-content-center mb-4">
<input type="text" name="id" id="track_id" class="form-control w-50" placeholder="Code à barres" style="border-radius: 0;">
<button type="submit" class="btn btn-dark py-3 px-4" style="border-radius: 0;">Suivre</button>
</form>

<div class="d-flex justify-content-center gap-3 flex-wrap">
<a href="https://wa.me/+212<?= $set_whatsapp ?>" 
class="btn btn-success btn-lg d-inline-flex align-items-center px-4 py-2" 
style="border-radius: 0rem; font-weight: 600; font-family: 'Poppins', sans-serif;">
<i class="bi bi-whatsapp me-2 fs-4"></i> Contacter par WhatsApp
</a>

<a href="register" class="btn btn-dark btn-lg" style="border-radius:0rem">
<i class="bi bi-person-plus me-2"></i> Inscription
</a>
</div>
</div>
</div>
</section>












<!-- Services -->
<section id="service" class="py-5">
<div class="container my-5 text-center">
<h1 class="text-center mb-4">
Nos <b class="text-primary">Service</b>
</h1>


<p>
Nous garantissons une livraison rapide sous 24h
Et sûre pour vos colis
</p>
<div class="row g-4 align-items-center">

<!-- ✅ الفيديو المصغر مع الضغط لفتح المودال -->
<!-- ✅ الفيديو المصغر مع الضغط لفتح المودال -->
<div class="col-md-6">
<div class="ratio ratio-16x9 rounded-4 shadow overflow-hidden position-relative" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#videoModal">
<video autoplay muted loop playsinline class="w-100 h-100 object-fit-cover">
<source src="uploads/video.mp4" type="video/mp4">
Votre navigateur ne supporte pas la lecture de vidéo.
</video>

<!-- ✅ أيقونة التشغيل في المركز -->
<div class="position-absolute start-50 translate-middle" style="top: 85% !important;">
<i class="fas fa-play-circle fa-5x text-white" style="text-shadow: 0 0 15px rgba(0,0,0,0.7);"></i>
</div>
</div>
</div>


<!-- ✅ بطاقات الخدمة -->
<div class="col-md-6">
<div class="row g-4">
<?php
$services = [
['icon' => 'fas fa-dolly', 'title' => 'Ramassage', 'desc' => 'Nous nous occupons du ramassage de vos colis depuis vos locaux afin de faciliter vos livraisons.'],
['icon' => 'fas fa-cubes', 'title' => 'Stockage des colis', 'desc' => 'Vos colis seront stockés en sécurité et dans les meilleures conditions.'],
['icon' => 'fas fa-box-open', 'title' => 'Packaging des colis', 'desc' => 'Nous proposons un service d\'emballage sur mesure pour plus de sécurité.'],
['icon' => 'fas fa-shipping-fast', 'title' => 'Livraison des colis', 'desc' => 'Vous vendez, nous livrons comme si c\'était livré par vous.']
];
foreach ($services as $s) {
echo "
<div class='col-md-6'>
<div class='card bCard h-100 shadow-sm border-0'>
<div class='card-body'>
<i class='{$s['icon']} fa-3x text-primary mb-3'></i>
<h5 class='fw-bold'>{$s['title']}</h5>
<p class='text-muted small'>{$s['desc']}</p>
</div>
</div>
</div>";
}
?>
</div>
</div>

</div>
</div>

<!-- ✅ Modal الفيديو الكامل مع الصوت -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
<div class="modal-dialog modal-xl modal-dialog-centered">
<div class="modal-content bg-black border-0">
<div class="modal-body p-0">
<video id="fullVideo" controls autoplay class="w-100" style="max-height: 80vh;">
<source src="uploads/video.mp4" type="video/mp4">
Votre navigateur ne supporte pas la lecture de vidéo.
</video>
</div>
</div>
</div>
</div>

</section>




<!-- Comment ça marche ? -->
<section class="py-5 bg-light">
<div class="container my-5 text-center text-center">
<h1 class="mb-4">Comment <b class='text-primary'>ça marche ?</b></h1>
<div class="row g-4">
<div class="col-md-4">
<div class="card bCard h-100">
<div class="card-body">
<i class="material-icons text-primary" style="font-size:48px;">person_add</i>
<h5 class="fw-bold mt-3">1. Créez votre compte</h5>
<p>Inscrivez-vous gratuitement et accédez à votre tableau de bord expéditeur.</p>
</div>
</div>
</div>
<div class="col-md-4">
<div class="card bCard h-100">
<div class="card-body">
<i class="material-icons text-primary" style="font-size:48px;">local_shipping</i>
<h5 class="fw-bold mt-3">2. Déposez vos colis</h5>
<p>Planifiez un ramassage ou déposez vos colis dans l’un de nos points relais.</p>
</div>
</div>
</div>
<div class="col-md-4">
<div class="card bCard h-100">
<div class="card-body">
<i class="material-icons text-primary" style="font-size:48px;">track_changes</i>
<h5 class="fw-bold mt-3">3. Suivi en temps réel</h5>
<p>Suivez vos colis et informez vos clients à chaque étape de la livraison.</p>
</div>
</div>
</div>
</div>
</div>
</section>





















<section class="py-5 bg-light">
<div class="container">
<div class="text-center mb-5">
<h1>Pourquoi <b class="text-primary"><?= $set_name; ?></b> ?</h1>
<p class="lead">Est le meilleur partenaire pour vos livraisons e-commerce</p>
</div>

<div class="row align-items-center g-5">
<!-- Image -->
<div class="col-lg-5">
<img src="uploads/site/lapss.png" alt="Application <?= $set_name ?>" class="img-fluid rounded-3 ">
</div>

<!-- Features -->
<div class="col-lg-7">
<div class="row g-4">
<!-- Live Tracking -->
<div class="col-md-6">
<div class="card border-0 shadow-sm h-100">
<div class="card-body d-flex align-items-start">
<span class="material-icons-outlined text-primary fs-2 me-3">track_changes</span>
<div>
<h5 class="fw-bold">Live Tracking</h5>
<p class="mb-0">Suivez vos commandes en temps réel où que vous soyez.</p>
</div>
</div>
</div>
</div>

<!-- Cash on Delivery -->
<div class="col-md-6">
<div class="card border-0 shadow-sm h-100">
<div class="card-body d-flex align-items-start">
<span class="material-icons-outlined text-success fs-2 me-3">attach_money</span>
<div>
<h5 class="fw-bold">Cash On Delivery</h5>
<p class="mb-0">Paiements à la livraison et gestion des retours optimisée.</p>
</div>
</div>
</div>
</div>

<!-- Support Client -->
<div class="col-md-6">
<div class="card border-0 shadow-sm h-100">
<div class="card-body d-flex align-items-start">
<span class="material-icons-outlined text-primary fs-2 me-3">support_agent</span>
<div>
<h5 class="fw-bold">Centre relation client</h5>
<p class="mb-0">Une équipe réactive et à votre écoute.</p>
</div>
</div>
</div>
</div>

<!-- API -->
<div class="col-md-6">
<div class="card border-0 shadow-sm h-100">
<div class="card-body d-flex align-items-start">
<span class="material-icons-outlined text-info fs-2 me-3">api</span>
<div>
<h5 class="fw-bold">Connecteurs & API</h5>
<p class="mb-0">Envoi automatique via nos connecteurs e-commerce.</p>
</div>
</div>
</div>
</div>
</div> <!-- end row -->
</div> <!-- end col -->
</div> <!-- end row -->
</div>
</section>



<!-- ✅ Section FAQ -->
<section class="py-5">
<div class="container my-5 text-center">
<h1 class="text-center mb-4">Questions <b class='text-primary'>Fréquentes</b></h1>
<div class="accordion" id="faqAccordion">
<div class="accordion-item">
<h2 class="accordion-header" id="q1">
<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#a1">
Quels sont vos délais de livraison ?
</button>
</h2>
<div id="a1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
<div class="accordion-body">
Nous livrons partout au Maroc sous 24h à 72h selon la destination.
</div>
</div>
</div>
<div class="accordion-item">
<h2 class="accordion-header" id="q2">
<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a2">
Comment suivre mon colis ?
</button>
</h2>
<div id="a2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
<div class="accordion-body">
Vous pouvez suivre votre colis grâce au code à barres via notre page de suivi.
</div>
</div>
</div>
<div class="accordion-item">
<h2 class="accordion-header" id="q3">
<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a3">
Proposez-vous le Cash On Delivery ?
</button>
</h2>
<div id="a3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
<div class="accordion-body">
Oui, nous collectons vos paiements à la livraison et vous les reversons rapidement.
</div>
</div>
</div>
</div>
</div>
</section>

<section class="py-5">
<div class="container my-5 px-4 py-5 bg-dark text-white" style="border-radius: 0.90rem;">
<div class="text-center mb-5">
<h1 class="mb-3">Nos <b class="text-primary">chiffres</b></h1>
<p class="lead">Ce qui fait notre force chez <b><?= $set_name ?></b></p>
</div>

<div class="row align-items-center g-5">
<!-- Image à gauche -->
<div class="col-md-4">
<img src="uploads/site/blue.png" alt="Statistiques <?= $set_name ?>" class="img-fluid rounded-3 shadow">
</div>

<!-- Chiffres à droite -->
<div class="col-md-8">
<div class="row g-4">

<div class="col-sm-6">
<div class="d-flex align-items-center">
<span class="material-icons-outlined fs-1 text-primary me-3">local_shipping</span>
<div>
<h2 class="fw-bold text-white mb-0"><?= number_format($count_delivered + 150000) ?></h2>
<p class="mb-0">Colis livrés</p>
</div>
</div>
</div>

<div class="col-sm-6">
<div class="d-flex align-items-center">
<span class="material-icons-outlined fs-1 text-primary me-3">groups</span>
<div>
<h2 class="fw-bold text-white mb-0"><?= number_format($count_clients + 2000) ?></h2>
<p class="mb-0">Clients actifs</p>
</div>
</div>
</div>

<div class="col-sm-6">
<div class="d-flex align-items-center">
<span class="material-icons-outlined fs-1 text-primary me-3">location_city</span>
<div>
<h2 class="fw-bold text-white mb-0"><?= number_format($count_cities) ?></h2>
<p class="mb-0">Villes desservies</p>
</div>
</div>
</div>

<div class="col-sm-6">
<div class="d-flex align-items-center">
<span class="material-icons-outlined fs-1 text-primary me-3">alarm_on</span>
<div>
<h2 class="fw-bold text-white mb-0">99%</h2>
<p class="mb-0">Livraison à temps</p>
</div>
</div>
</div>

<div class="col-sm-6">
<div class="d-flex align-items-center">
<span class="material-icons-outlined fs-1 text-primary me-3">calendar_today</span>
<div>
<h2 class="fw-bold text-white mb-0">7+</h2>
<p class="mb-0">Ans d'expérience</p>
</div>
</div>
</div>

</div> <!-- end row chiffres -->
</div> <!-- end col-md-8 -->
</div> <!-- end row align-items -->
</div> <!-- end container -->
</section>



<!-- Tarifs -->
<section id="pricing" class="py-5">
<div class="container my-5 ">
<h1 class="text-center mb-4">Nos <b class="text-primary">Tarifs</b></h1>
<div class="row mb-3">


<div class="col-md-12">
<input type="text" class="form-control searchbox" placeholder="Chercher ici">
</div>

<div class="col-md-4 my-2">
<h6>Agence</h6>
<select class="form-select warehouse">
<option value="0">Sélectionner Agence</option>
<?php
$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = '0' ORDER BY wh_name ASC");
$stmt->execute();
foreach ($stmt->fetchAll() as $w) {
echo "<option value='{$w['wh_id']}'>{$w['wh_name']}</option>";
}
?>
</select>
</div>

<div class="col-md-4 my-2">
<h6>Ville</h6>
<select class="form-select city">
<option value="0">Sélectionner Ville</option>
<?php
$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name ASC");
$stmt->execute();
foreach ($stmt->fetchAll() as $c) {
echo "<option value='{$c['city_id']}'>{$c['city_name']}</option>";
}
?>
</select>
</div>

<div class="col-md-4 my-2">
<h6>Afficher</h6>
<select class="form-select display">
<option value="10">10</option>
<option value="50">50</option>
<option value="100">100</option>
<option value="200">200</option>
</select>
</div>


</div>

<div class="row mb-3">
<div class="loader"></div>
<div id="dynamic_content"></div>
</div>


</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-center">
<div class="container my-5 text-center">
<h2 class="mb-4 text-white">Prêt à simplifier vos livraisons ?</h2>
<a href="register" class="btn btn-dark btn-lg" style='border-radius:0rem'>Créer mon compte</a>
</div>
</section>

<!-- Footer -->
<footer class="footer bg-dark text-white pt-5 pb-3">
<div class="container text-center text-md-start">
<div class="row" style="align-items: center;">

<!-- Logo -->
<div class="col-md-3 mb-4 text-center">
<img src="uploads/<?= $set_logo ?>" alt="<?= $set_name ?>" style="width:180px; filter: brightness(0) invert(1);">
<div class="my-2">
<a class="mx-2 text-white" href=''><i class="fa-brands fa-square-facebook fa-2x"></i></a>
<a class="mx-2 text-white" href=''><i class="fa-brands fa-square-instagram fa-2x"></i></a>
</div>
</div>

<!-- Menu -->
<div class="col-md-3 mb-4">
<h5 class="text-uppercase mb-3">Menu</h5>
<ul class="list-unstyled">
<li><a href="#" class="text-white text-decoration-none d-block py-1 hover-opacity">Accueil</a></li>
<li><a href="#service" class="text-white text-decoration-none d-block py-1 hover-opacity">Nos services</a></li>
<li><a href="#tarif" class="text-white text-decoration-none d-block py-1 hover-opacity">Tarif</a></li>
</ul>
</div>

<!-- Contact -->
<div class="col-md-3 mb-4">
<h5 class="text-uppercase mb-3">Contact</h5>
<ul class="list-unstyled">
<li class="py-1"><i class="fas fa-envelope me-2"></i> <?= $set_email ?></li>
<li class="py-1"><i class="fas fa-phone me-2"></i> <?= $set_phone ?></li>
<li class="py-1"><i class="fas fa-map-pin me-2"></i> <?= $set_location ?></li>
</ul>
</div>


<div class="col-md-3 mb-4">
</div>


</div>


</div>
</footer>

<style>
.hover-opacity:hover {
opacity: 0.8;
transition: 0.3s;
}
</style>




<div class="modal fade" id="trackingModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-scrollable modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Suivi de colis</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body" id="tracking-result">
<div class="text-center">Chargement...</div>
</div>
</div>
</div>
</div>







<script>
$(document).ready(function(){
$('#tracking-form').on('submit', function(e){
e.preventDefault();
let id = $('#track_id').val().trim();
if(id === "") return alert("Veuillez entrer le code à barres");

$('#tracking-result').html('<div class="text-center">Chargement...</div>');
$('#trackingModal').modal('show');

$.post('tracking_fetch', {id: id}, function(data){
$('#tracking-result').html(data);
}).fail(function(){
$('#tracking-result').html('<div class="alert alert-danger">Erreur lors du chargement</div>');
});
});
});
</script>



<!-- AJAX Script pour les tarifs -->
<script>
$(document).ready(function(){
const load = `<div class="progress" style="height:10px;">
<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
</div>`;
function load_data(page = 1) {
$.ajax({
url: 'get_price_site',
method: 'POST',
data: {
page: page,
search: $('.searchbox').val(),
display: $('.display').val(),
warehouse: $('.warehouse').val(),
city: $('.city').val()
},
beforeSend: () => $('.loader').html(load),
success: data => {
$('#dynamic_content').html(data);
$('.loader').html('');
}
});
}
load_data();
$(document).on('click', '.page-link', function() {
load_data($(this).data('page_number'));
});
$('.searchbox, .display, .warehouse, .city').on('input change', function() {
load_data();
});
});
</script>

<script>
window.addEventListener('scroll', function() {
const navbar = document.querySelector('.navbar');
if (window.scrollY > 50) {
navbar.classList.add('scrolled');
} else {
navbar.classList.remove('scrolled');
}
});
</script>


<script>
const modal = document.getElementById('videoModal');
modal.addEventListener('hidden.bs.modal', function () {
const video = document.getElementById('fullVideo');
video.pause();
video.currentTime = 0;
});
</script>

<hr class="my-0">


<div class='bg-dark text-center'>
<?php include get_file("Admin/admin_footer"); ?>
</div>