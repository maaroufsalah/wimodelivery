<?php
global $con;

include get_file("files/sql/get/os_settings");
include_once get_file("files/sql/get/functions");
define("page_title"," Créer un compte client");
include get_file("Admin/admin_header");


$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name ASC");
$stmt->execute();
$cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Créer un compte client</title>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<style>
body {
font-family: 'Inter', sans-serif;
background-color: #f8f9fa;
}
.card {
border: none;
border-radius: 0rem;
}
label {
font-weight: 500;
}
.form-control:focus {
box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
}
.form-icon {
position: absolute;
top: 50%;
left: 15px;
transform: translateY(-50%);
color: #6c757d;
}
.form-group {
position: relative;
}
.form-group input, .form-group select {
padding-left: 2.5rem;
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


</head>
<body>



<div class="card">
<div class="container my-0 py-0">
<div class="row align-items-center">

<div class="col-4">
<img src="uploads/<?=$set_logo;?>" alt="Logo" class="brand-logo my-0" style='width:180px'>
</div>

<div class="col-8" style="text-align: right;">
<a class='bg btn btn-primary text-white bg-primary my-0 py-4 px-3' href='login_account' style='border-radius: 0rem;'><i class='fa-regular fa-user'></i> Espace Client</a>
</div>

</div>
</div>
</div>



<div class="container my-5">
<div class="text-center">
</div>

<?php 
$id = "formId";
$result = "data_result"; 
$action = "new_account"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 
?>
<input type="hidden" name="rank" value="user" />

<div class="card shadow p-4">
<h4 class="text-center mb-4 text-dark">Créer un compte client</h4>

<div class="row g-3">

<!-- Nom & prénom -->
<div class="col-md-6">
<label>Nom et prénom</label>
<div class="form-group">
<i class="fas fa-user form-icon"></i>
<input name="owner" type="text" class="form-control" placeholder="Ex: Youssef Ben Ali">
</div>
</div>

<!-- Nom commercial -->
<div class="col-md-6">
<label>Nom commercial</label>
<div class="form-group">
<i class="fas fa-store form-icon"></i>
<input name="name" type="text" class="form-control" placeholder="Ex: Mon Magasin">
</div>
</div>

<!-- Téléphone -->
<div class="col-md-6">
<label>Téléphone</label>
<div class="form-group">
<i class="fas fa-phone form-icon"></i>
<input name="phone" type="number" class="form-control" placeholder="06...">
</div>
</div>

<!-- Email -->
<div class="col-md-6">
<label>Email</label>
<div class="form-group">
<i class="fas fa-envelope form-icon"></i>
<input name="email" type="email" class="form-control" placeholder="email@example.com">
</div>
</div>

<!-- Téléphone magasin -->
<div class="col-md-6">
<label>Télé - Commercial</label>
<div class="form-group">
<i class="fas fa-phone-volume form-icon"></i>
<input name="phone_store" type="number" class="form-control" placeholder="06...">
</div>
</div>

<!-- Ville -->
<div class="col-md-6">
<label>Ville</label>
<div class="form-group">
<i class="fas fa-city form-icon"></i>
<select name="city" class="form-select">
<option disabled selected>Choisir Ville</option>
<?php foreach ($cities as $city): ?>
<option value="<?= $city['city_id']; ?>"><?= htmlspecialchars($city['city_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
</div>

<!-- Adresse -->
<div class="col-12">
<label>Adresse</label>
<div class="form-group">
<i class="fas fa-location-dot form-icon"></i>
<input name="location" type="text" class="form-control" placeholder="Adresse complète">
</div>
</div>

<!-- CIN -->
<div class="col-md-6">
<label>Numéro CIN</label>
<div class="form-group">
<i class="fas fa-id-card form-icon"></i>
<input name="cin" type="text" class="form-control" placeholder="AB123456">
</div>
</div>

<div class="col-md-6">
  <label>Numéro RIB</label>
  <input name="bank_number" id="rib" type="text" class="form-control" placeholder="Ex: 001011234567890123456789">
  <small id="bank-name" class="text-muted mt-1"></small>
</div>


<!-- Password -->
<div class="col-md-6">
<label>Mot de passe</label>
<div class="form-group">
<i class="fas fa-lock form-icon"></i>
<input name="new_password" type="password" class="form-control" required>
</div>
</div>

<!-- Confirm Password -->
<div class="col-md-6">
<label>Confirmer mot de passe</label>
<div class="form-group">
<i class="fas fa-lock form-icon"></i>
<input name="confirm_password" type="password" class="form-control" required>
</div>
</div>

<div class="col-sm-12 text-center">
<div id ='<?= htmlspecialchars($result); ?>'></div>
</div>

<!-- Résultat + Bouton -->
<div class="col-12 text-center">
<button class="btn btn-primary btn-lg mt-2 px-5" style="border-radius:0rem">
Créer un compte
</button>
</div>

</div>
</div>

<?php formAwdEnd(); ?>
</div>

<!-- ✅ Scripts -->
<!-- ✅ jQuery -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById("<?= $id ?>").addEventListener("submit", function(e) {
const pass = document.querySelector("[name='new_password']").value;
const confirm = document.querySelector("[name='confirm_password']").value;
if (pass !== confirm) {
e.preventDefault();
Swal.fire({
icon: 'error',
title: 'Erreur',
text: 'Les mots de passe ne correspondent pas.'
});
}
});
</script>



<script>
document.getElementById('rib').addEventListener('input', function() {
    const ribVal = this.value.trim();

    if (ribVal.length >= 3) {
        fetch('get_bank_name?rib=' + encodeURIComponent(ribVal))
        .then(response => response.json())
        .then(data => {
            document.getElementById('bank-name').textContent = data.bankName || '';
        })
        .catch(() => {
            document.getElementById('bank-name').textContent = '';
        });
    } else {
        document.getElementById('bank-name').textContent = '';
    }
});
</script>



<script>
  function initSelect2(context = document) {
    $(context).find("select").each(function () {
      if (!$(this).hasClass("select2-hidden-accessible")) {
        const $modalParent = $(this).closest('.modal');

        $(this).select2({
          dropdownParent: $modalParent.length ? $modalParent : $('body'),
          width: '100%',
          minimumResultsForSearch: 0
        });
      }
    });
  }

  $(document).ready(function () {
    initSelect2();
  });

  $(document).on('shown.bs.modal', '.modal', function () {
    initSelect2(this);
  });
</script>




</body>
</html>
