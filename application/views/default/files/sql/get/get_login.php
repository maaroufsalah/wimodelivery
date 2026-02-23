<?php 
global $con;

if(SRM("POST")){

$email = POST("email");
$password = POST("password");    

$firebase_token = POST("firebase_token");


if(empty($email) || empty($password)){
print "Veuillez remplir tous les champs obligatoires (*)";
exit();    
}        

if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
print "Adresse e-mail invalide";
exit();    
}

$email = trim($email);
$passwordAttempt = trim($password);

// استرجاع بيانات المستخدم
$sql = "SELECT user_id, user_phone, user_pass, user_token FROM users WHERE user_email = :email AND user_unlink = '0' ";
$stmt = $con->prepare($sql);
$stmt->bindValue(':email', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if($user === false){
print "E-mail ou mot de passe invalide";
} else {
// التحقق من كلمة السر
$validPassword = password_verify($passwordAttempt, $user['user_pass']);

if($validPassword){
$expirationTime = time() + (3600 * 24 * 365); // سنة واحدة

// إذا كان user_token موجود بالفعل نحتفظ به، وإلا نخزنه لأول مرة
if(empty($user['user_token'])){
$newToken = md5(uniqid(rand(), true));
$update = $con->prepare("UPDATE users SET user_token = :token WHERE user_id = :id");
$update->execute([
':token' => $newToken,
':id'    => $user['user_id']
]);
} else {
$newToken = $user['user_token'];
}


if(!empty($firebase_token)) {
    $stmt = $con->prepare("UPDATE users SET user_firebase_id = ? WHERE user_id = ?");
    $stmt->execute([$firebase_token, $user['user_id']]);
}


// حفظ الكوكيز: user_id + token
setcookie('login_session', $user["user_id"] . ":" . $newToken, $expirationTime, '/', '', false, true);

print "<div class='alert alert-success w-100'>Connexion réussie</div>";
load_url("dashboard",0);        

} else {
print "E-mail ou mot de passe invalide";
}
}
}
$con = null;
?>
