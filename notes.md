
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

Sort
---------------
Sort managed in in query or fluid or javascript 

Filter
--------------
Filter managed in query or fluid or javascript



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
page.10 = FLUIDTEMPLATE
page.10 {
   template = FILE
   template.file = fileadmin/template.html
}

