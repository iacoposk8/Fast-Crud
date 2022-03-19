
# Fast Crud
CRUD library (create, read, update, and delete) based on php, jquery and ajax to quickly create forms with which to create/modify records and view/delete them

- [Quickstart](https://github.com/iacoposk8/Fast-Crud#quickstart)
- [Method](https://github.com/iacoposk8/Fast-Crud#method)
- [Property](https://github.com/iacoposk8/Fast-Crud#property)
- [Json](https://github.com/iacoposk8/Fast-Crud#json)
- [Complete Example](https://github.com/iacoposk8/Fast-Crud#complete-example)
- [Libraries of this project](https://github.com/iacoposk8/Fast-Crud#libraries-of-this-project)

## Quickstart

Create the myjson.json file or string structured like this:  
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
			"placeholder": "Write your nickname address"
		}
	],	
	[
		{
			"type": "email",
			"name": "email",
			"label": "Email",
			"placeholder": "Write your e-mail address"
		}
	]
]
```
In this json go to describe the html attributes of each field. For example the first field translates into the following html code:  
```
<input type="text" name="nickname" placeholder="Write your nickname address" />
```
So you can insert infinite html attributes, even custom. There are 3 proprietary entries that will not be converted: label, validation and option. For more details you can learn more here.

And now the index.php file that will be modified with your connection data to the MySQL database (`$fc->create` will create the mysql table):
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
		$data[$i][0] = '<span class="fast-crud-edit" attr-id="'. $data[$i][0] .'">' . $data[$i][0] . '</span>';
```
Just simply add ```class="fast-crud-edit"``` to the trigger element, Followed by ```attr-id``` with inside the id of the row of the table that you want to modify or delete.
See the [complete example](https://github.com/iacoposk8/Fast-Crud#complete-example) for other details.

Full example:
```
<?php
	require_once("FastCrud.php");

	function db_insert_edit($arg){
		//Edit $arg for process the data before inserting it into the database
		return $arg;
	}

	$fc = new FastCrud(MYSQL_HOST, MYSQ_USER, MYSQL_PASSWORD, MYSQL_DATABASE_NAME);
	//db_insert_edit is optional, if we want to modify the data before inserting it into the database
	$fc->create("myjson.json", "mytable", "id", "db_insert_edit");
?>
<a href="#" id="fast-crud-add">Add</a>

<?php
	$sth = $fc->pdo->prepare("SELECT nickname, email FROM mytable");
	$sth->execute();
	$data = $sth->fetchAll(PDO::FETCH_NUM);

	for($i = 0; $i < count($data); $i++)
		$data[$i][0] = '<span class="fast-crud-edit" attr-id="'. $data[$i][0] .'">' . $data[$i][0] . '</span>';

	$fc->view($data, ["Nickname", "E-Mail"]);
?>

```

To find css styles visit: [https://freefrontend.com/css-forms](https://freefrontend.com/css-forms)

## Method

| Method | Params | Description |
| --- | --- | --- |
| __construct | MYSQL_HOST, MYSQ_USER, MYSQL_PASSWORD, MYSQL_DATABASE_NAME | Connection to database |
| create | json_filename or string, mysql_table_name, id_column_name, (optional) data_manipulation_function | Create the data entry and modification form. With data_manipulation_function you can modify the data obtained from the form before inserting or modifying them in the database. |
| view | data, head, (optional) DataTable_settings | Show data in a table |
	
## Property

| Property | Default | Description |
| --- | --- | --- |
| pdo | PDO | PDO object to query the MySQL database |
| debug | False | Show MySql query and error |
| language | Array("send" => "Send", "delete" => "Delete", "delete_confirm" => "Are you sure you want to delete this item?"); | Here you can translate the interface messages |

## Json
| Special property | Description |
| --- | --- | 
| add_new_item | Option available only for `select` fields. Allows the user to insert an item not in the `option` list |
| label | Each input field will have its own `<label>` element|
| option | Only required for if you have `select`, `radio` or `checkbox` field. Inside it you can insert an array with the list of items to show. If you need to show a value other than the content of option you can use a two-dimensional array like this `[["item to show", "content of value"]]`. For more details see the [complete example](https://github.com/iacoposk8/Fast-Crud#complete-example)|
| validation | Here you can force the user to enter a particular field. For now there is only `email` and `mandatory`. Here is a two-dimensional array where there is [["control", "error message"]]. In control, you can also enter some php code. For more details see the [complete example](https://github.com/iacoposk8/Fast-Crud#complete-example)|
| value | Value for fields such as `date`, `datetime` and `datetime-local` can have the value `NOW()` and to show the current date | 

## Complete Example
Json file

    [
    	[
    	    	{
			    	"type": "datetime-local",
			    	"name": "date",
			    	"label": "Date",
			    	"value": "NOW()"
		    	}
	    	],
	    	[
    		{
    			"type": "email",
    			"name": "email",
    			"label": "Email",
    			"placeholder": "Email",
    			"validation": [
    				["mandatory", "email is mandatory"],
    				["email", "the email address entered is not valid"]
    			]
    		}
    	],
    	[
    		{
    			"type": "tel",
    			"name": "telephone",
    			"label": "Telephone",
    			"validation": [
    				["strlen(%) >= 10 && strlen(%) <= 12", "The length of the phone number is invalid "],
    				["preg_match('/[0-9]/', %)", "Only numbers in the telephone field are allowed "]
    			]
    		}
    	],
    	[
    		{
    			"type": "radio",
    			"name": "gender",
    			"options":[
				["Male"],
				["Female"]
			]
    		}
    	],
    	[
    		{
    			"type": "checkbox",
    			"name": "sports[]",
    			"options":[
				["Tennis"],
				["Swim"],
				["Bike"],
				["Running"]
			]
    		}
    	],
    	[
    		{
    			"type": "textarea",
    			"name": "note",
    			"label": "Note",
    			"placeholder": "Note"
    		}
    	],
    	[
    		{
    			"type": "select",
    			"name": "city[]",
    			"label": "Where do you live ",
    			"multiple": "multiple",
    			"add_new_item": 1,
    			"options":[
    				[""],
    				["Milan"],
    				["New York", "new_york"],
    				["Rome"],
    				["Florence"]
    			]
    		}
    	]
    ]


## Libraries of this project
- [jQuery](https://jquery.com/)
- [jQueryUI](https://jqueryui.com/)
- [DataTables](https://datatables.net/)
- [Selectize.js](https://selectize.dev/)
