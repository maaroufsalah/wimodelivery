<?php
include get_file("files/sql/get/session");

global $con;

// ✅ وظيفة: جلب جميع المستخدمين العاديين
function getUsers($con) {
    $stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = 0 AND user_rank = 'user' ORDER BY user_name ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

// ✅ وظيفة: جلب إعدادات التوصيل العامة
function getShippingCharges($con) {
    $stmt = $con->prepare("SELECT * FROM shipping_charges WHERE sc_unlink = 0 ORDER BY sc_city_name ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

// ✅ وظيفة: جلب معلومات مستودع
function getWarehouse($con, $id) {
    $stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = 0 AND wh_id = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// ✅ وظيفة: جلب معلومات المدينة
function getCity($con, $id) {
    $stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = 0 AND city_id = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// ✅ وظيفة: تحقق هل يوجد تسعيرة مخصصة للمستخدم
function getUserPricing($con, $userId, $warehouseId, $cityId) {
    $stmt = $con->prepare("SELECT * FROM user_pricing WHERE up_user = ? AND up_warehouse = ? AND up_city = ? AND up_unlink = 0 LIMIT 1");
    $stmt->execute([$userId, $warehouseId, $cityId]);
    return $stmt->fetch();
}

// ✅ عرض تحديد المستخدم
function renderUserSelect($users, $selectedUser) {
    echo "<div class='col-sm-12 my-3 successAjax'>";
    echo "<h6>Compte de facturation</h6>";
    echo "<select name='userDelivery' class='single-select w-100 js-select users'>";
    echo "<option value='0'>Choisir compte de facturation</option>";
    foreach ($users as $user) {
        $selected = ($user['user_id'] == $selectedUser) ? "selected" : "";
        echo "<option value='{$user['user_id']}' $selected>{$user['user_name']}</option>";
    }
    echo "</select>";
    echo "</div>";

    echo "
    <script>
    $('.users').change(function(){
      var url = 'pricing?do=user&user=' + $(this).val();
      window.location = url;
    });
    </script>";
}

function renderPricingForm($type, $sc, $city, $warehouse, $userId, $existing = null) {
    $formId = ($type == 'edit') ? "newUserPricing{$sc['sc_id']}" : "newUserPricing{$sc['sc_id']}";
    $resultId = "Results_{$formId}";
    $action = ($type == 'edit') ? "newUserPricing" : "newUserPricing";
    $delivery = $existing['up_delivery'] ?? $sc['sc_delivery'];
    $cancel   = $existing['up_cancel']   ?? $sc['sc_cancel'];
    $return   = $existing['up_return']   ?? $sc['sc_return'];

    echo "<div class='my-0' style='border-radius:0rem'><div class='card-body'>";

    // ✅ عرض التنبيه بدل alert
    if ($type == 'edit') {
        echo "
        <div class='alert alert-info my-2 p-2' role='alert' style='border-radius:0.5rem'>
        Ce tarif existe déjà
        </div>";
    }

    formAwdStart($formId, $resultId, $action, "post");

    echo "<div class='row align-items-center'>";
    if ($type == 'edit') {
        echo "<input type='hidden' name='id' value='" . md5($existing['up_id']) . "'>";
    } else {
        echo "<input type='hidden' name='userDelivery' value='$userId'>";
        echo "<input type='hidden' name='city' value='{$sc['sc_city']}'>";
        echo "<input type='hidden' name='warehouse' value='{$sc['sc_warehouse']}'>";
    }

    echo "<div class='col-sm-12 my-1 successAjax'><h6>De : {$warehouse['wh_name']} ----> À : {$city['city_name']}</h6></div>";

    echo "
    <div class='col-sm-3 my-3'>
      <label class='form-label'>Frais de livraison</label>
      <input type='number' name='delivery' class='form-control' value='$delivery'>
    </div>
    <div class='col-sm-3 my-3'>
      <label class='form-label'>Frais d'annulation</label>
      <input type='number' name='cancel' class='form-control' value='$cancel'>
    </div>
    <div class='col-sm-3 my-3'>
      <label class='form-label'>Frais de retour</label>
      <input type='number' name='return' class='form-control' value='$return'>
    </div>
    <div class='col-sm-3 text-center'>
      <button type='submit' class='btn btn-" . ($type == 'edit' ? "success" : "primary") . "' style='border-radius:50rem'>" . ($type == 'edit' ? "Mise à jour" : "Ajouter") . "</button>
    </div>
    <div class='col-sm-12 my-3'><div id='$resultId'></div></div>
    ";

    echo "</div>";
    formAwdEnd();
    echo "</div></div><div class='col-sm-12'><hr class='m-0'></div>";
}

// ✅ عرض تحديد المستخدم
if ($loginRank == "admin"): ?>
<div class='my-0' style='border-radius:0rem'>
  <div class='card-body'>
    <?php renderUserSelect(getUsers($con), $_GET['user'] ?? 0); ?>
  </div>
</div>

<div class='col-sm-12'><hr class='m-0'></div>


<?php if ($loginRank == "admin" && !empty($_GET['user']) && intval($_GET['user']) > 0): ?>
<div class="my-3 p-3" style="background:#f8f9fa;border-radius:.5rem;">
  <h6 class="mb-3">🔄 Remplacer et Mise à jour sélective</h6>
  <div class="row g-2 align-items-end">
    <!-- إدخال البحث والاستبدال -->
    <div class="col-sm-4">
      <label class="form-label">Valeur à chercher</label>
      <input type="text" id="searchValue" class="form-control" placeholder="ex: 45">
    </div>
    <div class="col-sm-4">
      <label class="form-label">Remplacer par</label>
      <input type="text" id="replaceValue" class="form-control" placeholder="ex: 39">
    </div>

    <!-- زر Remplacer All -->
    <div class="col-sm-2">
      <button id="replaceAllBtn" class="btn btn-warning w-100">Remplacer All</button>
    </div>

    <!-- زر Mise à jour tout -->
    <div class="col-sm-2">
      <button id="updateAllBtn" class="btn btn-success w-100">Mise à jour tout</button>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){

  // 🔹 علامة لكل فورم إذا تغيرت قيمته
  $("form").each(function(){
    $(this).data("modified", false);
  });

  // 🔹 دالة استبدال القيم
  function replaceAllValues(){
    let searchVal = $('#searchValue').val();
    let replaceVal = $('#replaceValue').val();
    if(searchVal === "" || replaceVal === "") return;

    $("form").each(function(){
      let formModified = false;
      $(this).find("input[name='delivery'], input[name='cancel'], input[name='return']").each(function(){
        if($(this).val().includes(searchVal)){
          $(this).val($(this).val().replaceAll(searchVal, replaceVal));
          formModified = true;
        }
      });
      if(formModified){
        $(this).data("modified", true); // علمنا أن هذا الفورم تغيرت قيمه
      }
    });
  }

  // 🔹 زر Remplacer All → تحديث القيم فقط
  $('#replaceAllBtn').on('click', function(e){
    e.preventDefault();
    replaceAllValues();
  });

  // 🔹 زر Mise à jour tout → إرسال فقط الفورمات التي تم تعديلها
  $('#updateAllBtn').on('click', function(e){
    e.preventDefault();
    $("form").each(function(){
      if($(this).data("modified")){ // فقط إذا الفورم تغيرت قيمته
        $(this).submit();
        $(this).data("modified", false); // إعادة تعيين العلم بعد الإرسال
      }
    });
  });

  // 🔹 أي تعديل يدوي على الحقول يعتبر تغيير
  $(document).on('keyup change', "input[name='delivery'], input[name='cancel'], input[name='return']", function(){
    $(this).closest('form').data("modified", true);
  });

});
</script>
<?php endif; ?>



<?php
if (!empty($_GET['user']) && intval($_GET['user']) > 0):
    $userId = intval($_GET['user']);
    $shippingCharges = getShippingCharges($con);

    foreach ($shippingCharges as $sc):
        $city     = getCity($con, $sc['sc_city']);
        $warehouse = getWarehouse($con, $sc['sc_warehouse']);
        $userPricing = getUserPricing($con, $userId, $sc['sc_warehouse'], $sc['sc_city']);

        if ($userPricing) {
            renderPricingForm('edit', $sc, $city, $warehouse, $userId, $userPricing);
        } else {
            renderPricingForm('add', $sc, $city, $warehouse, $userId);
        }
    endforeach;
endif;
?>
</div>
<?php endif; ?>
