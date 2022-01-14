
# Fast Crud
CRUD library (create, read, update, and delete) based on php, jquery and ajax to quickly create forms with which to create/modify records and view/delete them

- [Quickstart](https://github.com/iacoposk8/Fast-Crud#quickstart)
- [Method](https://github.com/iacoposk8/Fast-Crud#method)
- [Property](https://github.com/iacoposk8/Fast-Crud#property)
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

## Method

| Method | Params | Description |
| --- | --- | --- |
| `TODO` | TODO | TODO |
	
## Property

| Property | Default | Description |
| --- | --- | --- |
| `TODO` | TODO | TODO |

## Libraries of this project
- [jQuery](https://jquery.com/)
- [DataTables](https://datatables.net/)
