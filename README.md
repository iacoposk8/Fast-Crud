
# Fast Crud
CRUD library (create, read, update, and delete) based on php, jquery and ajax to quickly create forms with which to create/modify records and view/delete them

- [Quickstart](https://github.com/iacoposk8/Fast-Crud#quickstart)
- [Method](https://github.com/iacoposk8/Fast-Crud#method)
- [Property](https://github.com/iacoposk8/Fast-Crud#property)
- [Complete Example](https://github.com/iacoposk8/Fast-Crud#complete-example)
- [Libraries of this project](https://github.com/iacoposk8/Fast-Crud#libraries-of-this-project)

## Quickstart

Create a table on your MySQL database.
For example:

```
CREATE TABLE `mytable` (
  `id` int(11) NOT NULL,
  `nickname` varchar(255) NOT NULL
  `email` varchar(255) NOT NULL
)
```
Create the myjson.json file structured like this:  
```
Rows
|_ Fields (in the same row)
   |_ Attributes of the field
```
For example:
```
[
	[
		{
			"type": "text",
			"name": "nickname",
			"label": "nickname",
			"placeholder": "Write your nickname address "
		}
	],	
	[
		{
			"type": "email",
			"name": "email",
			"label": "Email",
			"placeholder": "Write your e-mail address ",
		}
	]
]
```
And now the index.php file that will be modified with your connection data to the MySQL database:
```
<?php
	require_once("FastCrud.php");

	$fc = new FastCrud(MYSQL_HOST, MYSQ_USER, MYSQL_PASSWORD, MYSQL_DATABASE_NAME);
	$fc->create("myjson.json", "mytable", "id");
?>
<a href="#" id="fast-crud-add">Add</a>
```
In index.php to view the data, add:
```
<?php
	$sth = $fc->pdo->prepare("SELECT nickname, email FROM mytable");
	$sth->execute();
	$data = $sth->fetchAll(PDO::FETCH_NUM);

	$fc->view($data, ["Nickname", "E-Mail"]);
?>
```
A table can only be a simple list of items, where it is not possible to interact.
It can be a list with one link per row or one per column which, if clicked, will take you to a detail page.  
But in addition to this there can also be an entry that leads you to modify or delete the row.  
If you want to enter the edit screen by clicking the "Nickname" column, just add these two lines before ```$fc->view($res, ["Nickname", "E-Mail"]);```
```
	for($i = 0; $i < count($data); $i++)
		$data[$i][0] = '<span class="fast-crud-edit" attr-id="'. $data[$i][0] .'">' . $data[$i][1] . '</span>';
```
Just simply add ```class="fast-crud-edit"``` to the trigger element, Followed by ```attr-id``` with inside the id of the row of the table that we want to modify or delete.

## Method

| Method | Params | Description |
| --- | --- | --- |
| __construct | MYSQL_HOST, MYSQ_USER, MYSQL_PASSWORD, MYSQL_DATABASE_NAME | Connection to database |
| create | json_filename, mysql_table_name, id_column_name, (optional) data_manipulation_function | Create the data entry and modification form. With data_manipulation_function you can modify the data obtained from the form before inserting or modifying them in the dayabase (See complete example) |
| view | data, head, (optional) DataTable_settings | Show data in a table |
	
## Property

| Property | Default | Description |
| --- | --- | --- |
| pdo | PDO | PDO object to query the MySQL database |
| debug | False | Show MySql query and error |
| TODO | Array("send" => "Send", "delete" => "Delete", "delete_confirm" => "Are you sure you want to delete this item?"); | Here you can translate the interface messages |

## Complete Example
TODO

## Libraries of this project
- [jQuery](https://jquery.com/)
- [DataTables](https://datatables.net/)
