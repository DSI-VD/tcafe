
targets for fluid
---------------------
1 array : conf and data mixed
  for each rows as $table => $row
    for each $row as $field => $fv
      print = field.conf.label + fv.value
      label = field.conf.label
      type  = field.conf.type

2 arrays :
  tcafe.data and tcafe.conf
  type = tcafe.conf.tables.{table}.fields.{field}.type


# tcafe.tables."fe_users".data.[n].[field] = mixed:value
# tcafe.tables."fe_users".conf.columns."username" = array:conf

Aware access ASSOC key->value 
  for each tcafe.tables.fe_users.data as fe_user
    print = User name + fe_user.username
    print = tcafe.tables."fe_users".conf.columns."username".label + fe_user.username

      for each fe_user as field
          print = tcafe.tables.{table}.conf.columns.{field}.label + ": " + field

Generic access
  for each tcafe.tables as tk => $tv
    for each tcafe.tables.{tk}.data as row
        for each row as $field => $fv
          print = tk.conf.columns.{$field}.label + ": " + $fv
          type  = tk.conf.columns.{$field}.type


Pagination
-----------
Pagination managed in query or fluid or javascript


1(BE solution)
filterdTableOfUid[] : resolve a filterdTableOfUids for each filter
tableOfUidsFiltered = merge of multi filterdTableOfUid with array_intersect() 
tableOfUids = tableOfUidsFiltered : rename table of results
tcafe:paginate (tableOfUids) : ask for array pagination
result table = geTableWithFieldsFromTableOfUids() : return table with the fields


// use TYPO3\CMS\Core\Pagination\ArrayPaginator;

$itemsToBePaginated = ['apple', 'banana', 'strawberry', 'raspberry', 'ananas'];
$itemsPerPage = 2;
$currentPageNumber = 3;

$paginator = new ArrayPaginator($itemsToBePaginated, $currentPageNumber, $itemsPerPage);
$paginator->getNumberOfPages(); // returns 3
$paginator->getCurrentPageNumber(); // returns 3, basically just returns the input value
$paginator->getKeyOfFirstPaginatedItem(); // returns 5
$paginator->getKeyOfLastPaginatedItem(); // returns 5



Sort
---------------
Sort managed in in query or fluid or javascript 

Filter
--------------
Filter managed in query or fluid or javascript


Relations
------------

check
  items
  renderType
    checkboxToggle
    checkboxLabeledToggle
    
flex    
  \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array().

group
  internal_type : 
    db
    file
    file_reference
  
  API : TYPO3\CMS\Core\Database\RelationHandler


imageManipulation

inline
  API ?

TYPES
---
direct
static
csv
  T1 csv T2
1n
  T1 (count) | (parent_uid, table) T2 
1nn1
  T1 (count) | uid_local T1T2 uid_foreign |  T2
MM
  T1 (count) | uid_local tables_local T1T2 tables_foreign uid_foreign |  T2
MM
  T1 (count) | uid_local tables_local more_local_fields T1T2 more_forein_fields tables_foreign uid_foreign |  T2




Install
-----------
lib.content {
    render = CONTENT
    render {
        table = tt_content
        select {
            orderBy = sorting
            where.cObject = COA
            where.cObject {
                10 = TEXT
                10 {
                    field = colPos
                    intval = 1
                    ifEmpty = 0
                    noTrimWrap = | AND colPos=||
                }
            }
        }
    }
}

page = PAGE
page {
    includeCSS {
        bootstrap = https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css
        bootstrap.external = 1
        bootstrap.integrity = sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh
        bootstrap.crossorigin = anonymous
    }
    10 = FLUIDTEMPLATE
    10 {
       template = FILE
       template.file = fileadmin/template.html
    }
}

<h1>hello</h1>
<f:cObject typoscriptObjectPath="lib.content.render" data="{colPos:0}" />
