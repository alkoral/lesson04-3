<?php
session_start();

$host = '127.0.0.1';
$dbname = 'korzun';
$user = 'korzun';
$pass = 'neto1653';

/*$host = '127.0.0.1';
$dbname = 'lesson04-3';
$user = 'root';
$pass = '';
*/

$db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);

if (!empty($_POST['register']) or !empty($_POST['sign_in'])) {
	$login = $_POST['login'];
	$pass = md5($_POST['password']);
}

if (!empty($_POST['register']) and !empty($_POST['login']) and !empty($_POST['password'])) {
$check = $db->query("SELECT login FROM user WHERE login = '$login'"); 
$result = $check->fetch(PDO::FETCH_ASSOC);
if ($result){
    $warning = "<p>Такой пользователь уже существует в базе данных.</p>";
}
else {
	$sql = "INSERT INTO `user` (`id`, `login`, `password`) VALUES ('', '$login', '$pass')";
  	$result = $db->prepare($sql)->execute();
  	$_SESSION['user'] = $login;
		$check = $db->query("SELECT * FROM user WHERE login = '$login' and password = '$pass'"); 
		$result = $check->fetch(PDO::FETCH_ASSOC);
			if ($result){
				$_SESSION['id_user'] = $result['id'];
	  	header('location: index.php');
		}
	}
}

if (!empty($_POST['sign_in']) and !empty($_POST['login']) and !empty($_POST['password'])) {
	$check = $db->query("SELECT * FROM user WHERE login = '$login' and password = '$pass'"); 
	$result = $check->fetch(PDO::FETCH_ASSOC);
		if ($result){
			$_SESSION['user'] = $login;
			$_SESSION['id_user'] = $result['id'];
	  	header('location: index.php');
	}
	else {
		$warning = "<p>Такого пользователя не существует либо неверный пароль.</p>";
	}
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<title>Вход/Регистрация</title>
</head>
<body>
	
<?php
if (isset($warning)) {
	echo $warning;
}
	else {
	echo "<p>Введите данные для регистрации или войдите, если уже регистрировались:</p>";
}
?>

<form method="POST">
    <input type="text" name="login" placeholder="Логин">&nbsp;
    <input type="password" name="password" placeholder="Пароль">&nbsp;
    <input type="submit" name="sign_in" value="Вход">&nbsp;
    <input type="submit" name="register" value="Регистрация">
</form>

</body>
</html>
