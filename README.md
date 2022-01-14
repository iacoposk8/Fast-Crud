
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
	$res = $sth->fetchAll(PDO::FETCH_NUM);

	$fc->view($res, ["Nickname", "E-Mail"]);
?>
```

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
| TODO | default language | TODO |


`$language = Array(
			"send" => "Send",
			"delete" => "Delete",
			"delete_confirm" => "Are you sure you want to delete this item?"
		);` | TODO | TODO |

## Complete Example
TODO

## Libraries of this project
- [jQuery](https://jquery.com/)
- [DataTables](https://datatables.net/)
