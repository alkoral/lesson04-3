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

if (empty($_SESSION['user'])) {
	echo "<a href='register.php'>Войти на сайт</a>";
	die;
}
	else {
	echo "<h1>Здравствуйте, <font color=red>".$_SESSION['user']."</font>! Вот ваш список дел:</h1>";
}

try
{

$db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);

function get_param($param_name) {
  if (isset($_REQUEST[$param_name]) and !empty($_REQUEST[$param_name])) {
    return strip_tags(trim($_REQUEST[$param_name]));
  }
  else {
    return "";
  }
}

$action=get_param('action');
$description=get_param('description');
$id=get_param('id');
$id_author=$_SESSION['id_user'];
$author=$_SESSION['user'];

if ($action=='new_desc' and !empty($description)){ // Добавляем новую запись
  $sql = "INSERT INTO `task` (`id`, `description`, `user_id`) VALUES ('', '$description', '$id_author')";
  $result = $db->prepare($sql)->execute();
  header('location: index.php'); // чтобы при нажатии F5 снова не передавать то же значение
}

if ($action=='delete' and $id>0) { // Удаляем запись
  $sql = "DELETE FROM `task` WHERE `id`='$id'";
  $result = $db->prepare($sql)->execute();
  header('location: index.php');
}

if ($action=='done' and $id>0) { // Меняем статус
  $sql = "UPDATE `task` SET `is_done`= '1' WHERE `id`='$id'";
  $result = $db->prepare($sql)->execute();
  header('location: index.php');
}

if ($action=='edit' and $id>0) { // Выводим текст описания для редактирования
  $result = $db->prepare("SELECT description FROM task WHERE `id`='$id' LIMIT 1"); 
  $result->execute();
  $row = $result->fetch();
  $description=$row['description'];
}

if ($action=='update' and $id>0) { // Меняем описание
  $sql = "UPDATE `task` SET `description`= '$description' WHERE `id`='$id'";
  $result = $db->prepare($sql)->execute();
  header('location: index.php');
}

$sort="date_added"; // Параметры для сортировки
if (!empty($_POST['sort_by']) and $_POST['sort_by']=='date_created') {
  $sort="date_added";
}
if (!empty($_POST['sort_by']) and $_POST['sort_by']=='is_done') {
  $sort="is_done";
}
if (!empty($_POST['sort_by']) and $_POST['sort_by']=='description') {
  $sort="description";
}

if (isset($_POST['assign'])) { // Меняем ответственного
  $sql = "UPDATE `task` SET `assigned_user_id`='".$_POST['assigned_user_id']."' WHERE `id`='".$_POST['id']."'";
  $result = $db->prepare($sql)->execute();
  header('location: index.php');
}
}

catch (Exception $e) {
  die('Error: ' . $e->getMessage() . '<br>');
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Домашнее задание к лекции 4.3 «SELECT из нескольких таблиц»</title>
  <style>
    form {
      margin-bottom: 15px;
    }

    table { 
      border-spacing: 0;
      border-collapse: collapse;
    }

    table td, table th {
      border: 1px solid #ccc;
      padding: 5px;
    }
      
    table th {
      background: #eee;
    }

    form {
        margin: 0;
    }

</style>
</head>
<body>

<div style="float: left">
  <form method="POST">
    <input type="text" name="description" placeholder="Описание задачи" value="<?php echo $description; ?>">
<?php
  if ($action=='edit') { 
    echo "
    <input type='hidden' name='action' value='update'>
    <input type='submit' name='save' value='Сохранить'>
    <input type='hidden' name='id' value='$id'>";
}
  else {
    echo "
    <input type='hidden' name='action' value='new_desc'>
    <input type='submit' name='save' value='Добавить'>";
}
?>
  </form>
</div>

<div style="float: left; margin-left: 20px;">
  <form method="POST">
    <label for="sort">Сортировать по:</label>
    <select name="sort_by">
      <option selected="selected" value="date_created">Дате добавления</option>
      <option value="is_done">Статусу</option>
      <option value="description">Описанию</option>
    </select>
    <input type="submit" name="sort" value="Отсортировать">
    </form>
</div>
<div style="clear: both"></div>
<br>

<table>
  <tr>
  <th>Описание задачи</th>
    <th>Дата добавления</th>
    <th>Статус</th>
    <th>Что сделать</th>
    <th>Ответственный</th>
    <th>Автор</th>
	<th>Закрепить задачу за пользователем</th>

  </tr>

<?php
$sql = "SELECT t.*, u.login as assigned 
				FROM task AS t LEFT JOIN user AS u ON u.id=t.assigned_user_id 
				WHERE `user_id` = '$id_author' ORDER BY $sort";
$result = $db->query($sql);
  foreach($result as $row) {
    if ($row['is_done']=="0") {
      $status = "<span style='color: orange;'>В процессе";
    }
    else {
      $status = "<span style='color: green;'>Выполнено";
    }
    echo "
<tr>
  <td>".$row['description']."</td>
  <td>".$row['date_added']."</td>
  <td>".$status."</td>
  <td>
    <a href='?id=".$row['id']."&action=edit'>Изменить</a> |";
			if (NULL==$row['assigned_user_id']) {
				echo "<a href='?id=".$row['id']."&action=done'>Выполнить</a> |";
    	}
    echo "
    <a href='?id=".$row['id']."&action=delete'>Удалить</a>
  </td>";

		if (NULL==$row['assigned_user_id']) {
			echo "<td>Вы сами</td>";
		}
		else {
			echo "<td>".$row['assigned']."</td>";
		}

	echo "
  <td>".$author."</td>

  <td>
  	<form method='POST'>
	    <select name='assigned_user_id'>";
				$ass_sql = "SELECT * FROM user ORDER BY `user`.`login` ASC";
				$ass_result = $db->query($ass_sql);
				  foreach($ass_result as $ass_row) {
				  	$ass_new = $ass_row['id'];
				  	if ($ass_new!==$id_author) { // исключаем автора таска из списка других юзеров
				  	echo "
				  	<option value=".$ass_new.">".$ass_row['login']."</option>";
				}
			}
				echo"
				</select>
			<input type='submit' name='assign' value='Переложить ответственность'>
			<input type='hidden' name='id' value='".$row['id']."'>
		</form>
	</td>
</tr>\n";
}
?>
</table>
<br>
<!-- NEW TABLE -->
<p><strong>Также посмотрите, что от вас требуют другие люди:</strong></p>

<table>
  <tr>
  <th>Описание задачи</th>
    <th>Дата добавления</th>
    <th>Статус</th>
    <th>Что сделать</th>
    <th>Ответственный</th>
    <th>Автор</th>
  </tr>

<?php
$sql = "SELECT t.*, u.login as task_author 
				FROM task AS t LEFT JOIN user AS u ON u.id=t.user_id 
				WHERE `assigned_user_id` = '$id_author' ORDER BY $sort";

$result = $db->query($sql);
  foreach($result as $row) {
    if ($row['is_done']=="0") {
      $status = "<span style='color: orange;'>В процессе";
    }
    else {
      $status = "<span style='color: green;'>Выполнено";
    }
    echo "
<tr>
  <td>".$row['description']."</td>
  <td>".$row['date_added']."</td>
  <td>".$status."</td>

  <td>
    <a href='?id=".$row['id']."&action=edit'>Изменить</a> |
    <a href='?id=".$row['id']."&action=done'>Выполнить</a> |
    <a href='?id=".$row['id']."&action=delete'>Удалить</a>
  </td>

  <td>$author</td>
	<td>".$row['task_author']."</td>";
 }
	echo "
</tr>\n";
?>
</table>

<?php
echo "<br><a href='logout.php'>Выйти</a>";
?>
</body>
</html>