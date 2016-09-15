<?php
// подключение библиотек:
require("../secure/session.inc.php");
require("../inc/lib.inc.php");
require("../inc/db.inc.php");

$count = abs((int)$_POST['count']);
$result = selectItemToCatalog($_POST['type'], $count);
echo <<<HTML
	<table>
		<th>
			<td>ID<td>
			<td>Название<td>
			<td>Автор<td>
			<td>Год издания<td>
			<td>Цена<td>
		</th>"
HTML;
foreach($result as $array)
{
	echo <<<BOOK
	<tr>;
		<td>{$array['id']}</td>
		<td>{$array['title']}</td>
		<td>{$array['author']}</td>
		<td>{$array['pubyear']}</td>
		<td>{$array['price']}</td>
	</tr>
BOOK;
}
echo "</table>";
?>