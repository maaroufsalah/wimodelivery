<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include_once get_file("files/sql/get/functions");

if (hasUserPermission($con, $loginId, 33 ,'admin')){

include get_file("Admin/admin_header");


define ("page_title","scanner");


?>






<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<!--begin::App Wrapper-->
<div class="app-wrapper">
<!--begin::Header-->





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

// select state
$stmt = $con->prepare("SELECT * FROM state WHERE state_unlink = 0 ORDER BY state_name");
$stmt->execute();
$states = $stmt->fetchAll();

?>



<div class="row">


<div class='col-sm-4' style="text-align: left;">
<div class="card" style="border-radius:0rem">
<div class="card-body">
<h5>Scanner QR ou Code-Barres</h5>


<div class="my-3">
<div class="input">État du colis</div>
<select id="state_select" name="state" class="js-select w-100 state">
<option value="0" disabled selected>Choisir État</option>
<?php foreach ($states as $row): ?>
<option value='<?= $row['state_id'] ?>'><?= $row['state_name'] ?></option>
<?php endforeach; ?>
</select>
</div>

<input type="text" id="scan_input" placeholder="Scannez un code..." style="width: 100%; padding: 10px; font-size: 18px;">
<div id="reader"></div>
</div>
</div>
</div>

<div class='col-sm-8' style="">

<div id="result"></div>
</div>


</div>








<script>
let lastScannedCode = "";
let typingTimer;
const delay = 500;

function processCode(code) {
    if (code === lastScannedCode) return;
    lastScannedCode = code;

    const state = $('#state_select').val();  // ← Récupère l'état sélectionné

    $('#scan_input').val(code);
    $('#result').html("Code détecté : <strong>" + code + "</strong><br>Chargement...");

    $.ajax({
        url: 'check_code_state',
        type: 'POST',
        data: { code: code, state: state },  // ← Envoie les deux
        success: function(response) {
            $('#result').html("<strong>Code détecté :</strong> " + code + "<hr>" + response);
            $("#scan_input").val('');
        },
        error: function() {
            $('#result').html("<span style='color:red;'>Erreur lors de la requête AJAX.</span>");
        }
    });
}



function onScanSuccess(decodedText, decodedResult) {
processCode(decodedText);
}

const html5QrCode = new Html5Qrcode("reader");
html5QrCode.start(
{ facingMode: "environment" },
{
fps: 10,
qrbox: { width: 250, height: 250 },
formatsToSupport: [
Html5QrcodeSupportedFormats.QR_CODE,
Html5QrcodeSupportedFormats.EAN_13,
Html5QrcodeSupportedFormats.CODE_128,
Html5QrcodeSupportedFormats.UPC_A
]
},
onScanSuccess
).catch(err => {
$('#result').html("<span style='color:red;'>Erreur caméra : " + err + "</span>");
});

$('#scan_input').on('input', function() {
clearTimeout(typingTimer);
typingTimer = setTimeout(function() {
let manualCode = $('#scan_input').val().trim();
if (manualCode !== "" && manualCode !== lastScannedCode) {
processCode(manualCode);
}
}, delay);
});
</script>

<?php
}elseif($do == "new"){



}else{






}
?>






</div>
</div>

























</main>

<?php include get_file("Admin/admin_footer");?>

<?php
}
?>