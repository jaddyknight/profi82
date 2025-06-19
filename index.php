<?php
require 'config.php';
if (!empty($_SESSION['logged_in'])) header('Location: /search.php');
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if(trim($_POST['username'])===ADMIN_USER && trim($_POST['password'])===ADMIN_PASS) {
        $_SESSION['logged_in']=true;
        header('Location:/search.php'); exit;
    } else $error='Неверный логин или пароль.';
}
?>
<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8"><title>Вход</title></head><body>
<?php if($error):?><p style="color:red"><?php echo $error;?></p><?php endif;?>
<form method="post" action="/">
  <input name="username" placeholder="Логин" required>
  <input name="password" type="password" placeholder="Пароль" required>
  <button>Войти</button>
</form>
</body></html>