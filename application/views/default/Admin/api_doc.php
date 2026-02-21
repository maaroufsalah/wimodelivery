<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");
include get_file("Admin/admin_header");

define ("page_title","Api");

?>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

<div class="app-wrapper">

<?php include get_file("Admin/admin_nav_top");?>
<?php include get_file("Admin/admin_nav_left");?>

<main class="app-main">

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0"><?php print page_title ;?></h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a>Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php print page_title ;?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
<div class="container-fluid">

<?php
$do = isset ($_GET['do']) ? $_GET ['do'] : 'Manage' ;
if ($do == 'Manage'){
?>

<style>
pre {
    background: #f1f1f1;
    padding: 15px;
    border-radius: 5px;
}
hr {
    margin: 60px 0;
}
</style>

<div class="">
<p class="lead">Cette page décrit les deux endpoints principaux : <strong>Ajouter un Colis</strong> & <strong>Changer l'État d'une Commande</strong>.</p>

<!-- ======================== -->
<!-- ✅ SECTION 1: Ajouter un Colis -->
<!-- ======================== -->

<h2 class="mt-5">API - Ajouter un Colis</h2>

<h5>Endpoint</h5>
<pre>POST /add_package_api</pre>

<h5>Méthode</h5>
<p><code>POST</code></p>

<h5>En-têtes requis</h5>
<pre>Content-Type: application/x-www-form-urlencoded</pre>

<h5>Paramètres de la requête</h5>
<div class="table-responsive">
<table class="table table-bordered">
<thead class="table-light">
<tr>
<th>Paramètre</th>
<th>Type</th>
<th>Requis</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr><td>email</td><td>string</td><td>✅</td><td>Email de l'utilisateur</td></tr>
<tr><td>password</td><td>string</td><td>✅</td><td>Mot de passe</td></tr>
<tr><td>Code Colis</td><td>int</td><td>✅</td><td>ID de colis</td></tr>
<tr><td>warehouse</td><td>int</td><td>✅</td><td>ID de l'entrepôt</td></tr>
<tr><td>city_name</td><td>string</td><td>✅</td><td>Nom de la ville (sera converti en ID)</td></tr>
<tr><td>name</td><td>string</td><td>✅</td><td>Nom du destinataire</td></tr>
<tr><td>phone</td><td>string</td><td>✅</td><td>Numéro de téléphone (10 chiffres)</td></tr>
<tr><td>location</td><td>string</td><td>✅</td><td>Adresse de livraison</td></tr>
<tr><td>item</td><td>string</td><td>❌</td><td>Description du colis</td></tr>
<tr><td>note</td><td>string</td><td>❌</td><td>Note supplémentaire</td></tr>
<tr><td>qty</td><td>int</td><td>❌</td><td>Quantité</td></tr>
<tr><td>fragile</td><td>int</td><td>❌</td><td>0 ou 1</td></tr>
<tr><td>try</td><td>int</td><td>❌</td><td>0 ou 1</td></tr>
<tr><td>open</td><td>int</td><td>❌</td><td>0 ou 1</td></tr>
<tr><td>change</td><td>int</td><td>❌</td><td>0 ou 1</td></tr>
<tr><td>price</td><td>float</td><td>❌</td><td>Prix du colis</td></tr>
<tr><td>change_code</td><td>int</td><td>❌</td><td>ID de la commande échange</td></tr>
<tr><td>pickup</td><td>date</td><td>❌</td><td>Date d'enlèvement</td></tr>
<tr><td>box</td><td>int</td><td>❌</td><td>ID de la boîte</td></tr>
</tbody>
</table>
</div>

<h5>Réponse en cas de succès</h5>
<pre>
{
  "success": true,
  "message": "Colis ajouté avec succès.",
  "data": {
    "order_id": 123
  }
}
</pre>

<h5>Réponse en cas d'erreur</h5>
<pre>
{
  "success": false,
  "message": "Message d'erreur"
}
</pre>

<h5>Exemple CURL</h5>
<pre>
curl -X POST https://<?php echo $_SERVER["HTTP_HOST"]; ?>/add_package_api \
-d "email=test@gmail.com" \
-d "password=123456" \
-d "code=1" \
-d "warehouse=1" \
-d "city_name=Casablanca" \
-d "name=Ali" \
-d "phone=0612345678" \
-d "price=500" \
-d "location=Centre ville"
</pre>

<hr>

<!-- =============================== -->
<!-- ✅ SECTION 2: Changer État Commande -->
<!-- =============================== -->

<h2 class="mt-5">API - Changer l'État d'une Commande</h2>

<h5>Endpoint</h5>
<pre>POST /change_order_state_api</pre>

<h5>Méthode</h5>
<p><code>POST</code></p>

<h5>En-têtes requis</h5>
<pre>Content-Type: application/x-www-form-urlencoded</pre>

<h5>Paramètres de la requête</h5>
<div class="table-responsive">
<table class="table table-bordered">
<thead class="table-light">
<tr>
<th>Paramètre</th>
<th>Type</th>
<th>Requis</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr><td>email</td><td>string</td><td>✅</td><td>Email de l'employé</td></tr>
<tr><td>password</td><td>string</td><td>✅</td><td>Mot de passe</td></tr>
<tr><td>order_id</td><td>int</td><td>✅</td><td>ID de la commande</td></tr>
<tr><td>state_name</td><td>string</td><td>✅</td><td>Nom de l'état (sera converti en ID). Exemple: « Livré », « Reporté »</td></tr>
<tr><td>postponed_date</td><td>date</td><td>❌</td><td>Date de report (obligatoire si l'état est Reporté)</td></tr>
<tr><td>note</td><td>string</td><td>❌</td><td>Note supplémentaire</td></tr>
</tbody>
</table>
</div>

<h5>Réponse en cas de succès</h5>
<pre>
{
  "success": true,
  "message": "Commande mise à jour en état: Reporté."
}
</pre>

<h5>Réponse en cas d'erreur</h5>
<pre>
{
  "success": false,
  "message": "Message d'erreur"
}
</pre>

<h5>Exemple CURL</h5>
<pre>
curl -X POST https://<?php echo $_SERVER["HTTP_HOST"]; ?>/change_order_state_api \
-d "email=employe@example.com" \
-d "password=secret123" \
-d "order_id=101" \
-d "state_name=Reporté" \
-d "postponed_date=2025-07-20" \
-d "note=Commande reportée au 20 Juillet"
</pre>

</div>

<?php
}
?>
</div>
</div>

</main>

<?php include get_file("Admin/admin_footer");?>
