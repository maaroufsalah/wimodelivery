<?php 
$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

require_once 'vendor/autoload.php'; // PhpSpreadsheet

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");
include get_file("Admin/admin_header");

define ("page_title","Gestion des Dépenses");
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
<li class="breadcrumb-item active"><?php print page_title ;?></li>
</ol>
</div>
</div>
</div>
</div>

<div class="app-content">
<div class="container-fluid">

<?php
$do = isset($_GET['do']) ? $_GET['do'] : 'Manage' ;
if ($do == 'Manage'){

// جلب لائحة users برتبة user
$stmt = $con->prepare("SELECT user_id, user_name FROM users WHERE user_rank = 'user' AND user_unlink = 0 ORDER BY user_name");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mt-4">

<!-- اختيار Point de vente -->
<div class="mb-3">
<label class="form-label">Point de vente (User)</label>
<select id="userSelect" class="form-control js-select">
<option value="">-- Sélectionner --</option>
<?php foreach ($users as $u): ?>
<option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['user_name']) ?></option>
<?php endforeach; ?>
</select>
</div>

<!-- Filtres par date -->
<div class="row mb-3">
<div class="col-md-4">
<label>Du</label>
<input type="date" id="date_from" class="form-control">
</div>
<div class="col-md-4">
<label>Au</label>
<input type="date" id="date_to" class="form-control">
</div>
<div class="col-md-4 d-flex align-items-end">
<button class="btn btn-secondary w-100" id="filterExpenses">Filtrer</button>
</div>
</div>

<!-- Formulaire d'ajout -->
<div id="expenseForm" class="card p-3 mb-3" style="display:none;">
<h5>Nouvelle dépense</h5>
<input type="text" id="exp_title" class="form-control mb-2" placeholder="Titre de dépense">
<input type="number" id="exp_amount" class="form-control mb-2" placeholder="Montant" step="0.01">
<button class="btn btn-primary" id="addExpense">Ajouter</button>
</div>

<!-- Liste des dépenses -->
<div id="expensesList"></div>

<!-- Section Analytique -->
<div id="expensesAnalytics" class="mt-4">
<h5>Analytique des Dépenses</h5>
<p>Total dépense: <span id="totalAmount">0</span> DH</p>
<canvas id="expensesChart" height="100"></canvas>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function(){

// Changement de user
$("#userSelect").change(function(){
if($(this).val()){
$("#expenseForm").show();
loadExpenses();
} else {
$("#expenseForm").hide();
$("#expensesList").html("");
$("#totalAmount").text("0");
if(chartInstance) chartInstance.destroy();
}
});

// Bouton Filtrer
$("#filterExpenses").click(function(){
loadExpenses();
});

// Ajouter dépense
$("#addExpense").click(function(){
let userId = $("#userSelect").val();
let title = $("#exp_title").val();
let amount = $("#exp_amount").val();

if(title && amount){
$.post("expenses_action", {
action:"add",
user_id:userId,
title:title,
amount:amount
}, function(res){
if(res.success){
$("#exp_title").val("");
$("#exp_amount").val("");
loadExpenses();
} else {
alert(res.message);
}
},"json");
} else {
alert("Complétez tous les champs !");
}
});

// Charger dépenses + analytics
let chartInstance = null;
function loadExpenses(){
let userId = $("#userSelect").val();
let date_from = $("#date_from").val();
let date_to = $("#date_to").val();

$("#expensesList").html("<p>Chargement...</p>");
$.post("expenses_action", {
action:"list",
user_id:userId,
date_from:date_from,
date_to:date_to
}, function(res){
$("#expensesList").html(res.html);
$("#totalAmount").text(res.total);
renderChart(res.chartLabels, res.chartData);
}, "json");
}

// Dessiner le graphique
function renderChart(labels, data){
const ctx = document.getElementById('expensesChart').getContext('2d');
if(chartInstance) chartInstance.destroy();
chartInstance = new Chart(ctx, {
type: 'bar',
data: {
labels: labels,
datasets: [{
label: 'Montant par mois',
data: data,
backgroundColor: 'rgba(54, 162, 235, 0.6)',
}]
},
options: {
responsive: true,
scales: { y: { beginAtZero: true } }
}
});
}

// Supprimer dépense
$(document).on("click", ".deleteExpense", function(){
if(confirm("Voulez-vous vraiment supprimer cette dépense ?")){
let expId = $(this).data("id");
$.post("expenses_action", {action:"delete", exp_id:expId}, function(res){
if(res.success){
loadExpenses();
} else {
alert(res.message);
}
},"json");
}
});

// Activer édition
$(document).on("click", ".editExpense", function(){
let tr = $(this).closest("tr");
let id = $(this).data("id");
let title = $(this).data("title");
let amount = $(this).data("amount");
let date = $(this).data("date");

tr.html(`
<td><input type="date" class="form-control edit-date" value="${date.split(' ')[0]}"></td>
<td><input type="text" class="form-control edit-title" value="${title}"></td>
<td><input type="number" step="0.01" class="form-control edit-amount" value="${amount}"></td>
<td>
<button class="btn btn-success btn-sm saveExpense" data-id="${id}">Enregistrer</button>
<button class="btn btn-secondary btn-sm cancelEdit">Annuler</button>
</td>
`);
});

// Annuler édition
$(document).on("click", ".cancelEdit", function(){
loadExpenses();
});

// Sauvegarder modifications
$(document).on("click", ".saveExpense", function(){
let tr = $(this).closest("tr");
let id = $(this).data("id");
let title = tr.find(".edit-title").val();
let amount = tr.find(".edit-amount").val();
let date = tr.find(".edit-date").val();

$.post("expenses_action", {
action:"update",
exp_id:id,
title:title,
amount:amount,
date:date
}, function(res){
if(res.success){
loadExpenses();
} else {
alert(res.message);
}
}, "json");
});

});
</script>

<?php } ?>

</div>
</div>
</main>
<?php include get_file("Admin/admin_footer");?>
