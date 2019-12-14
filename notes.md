
targets for fluid
  data full  tcafe.data.tables.[table].fields.[field].value
  data short tcafe.data.[table].[field]
ou
  data full  tcafe.tables.[table].data.fields.[field].value
  data short tcafe.[table].data.[field]

  tcafe.tables.{table}.conf.fields.{field}.type


1 array, conf and data mixed
  for each rows as $table => $row
    for each $row as $field => $fv
      print = field.configuration.label + fv.value
      label = field.configuration.label
      type  = field.configuration.type

2 arrays :
  tcafe.data and tcafe.conf
  type = tcafe.conf.tables.{table}.fields.{field}.type


Aware access ASSOC key->value  check MYSQLI_ASSOC for TYPO3 SQL ?
  for each tcafe.data.tables.fe_users as fe_user
    print = User name + fe_user.username
    print = tcafe.conf.tables.{table}.fields.{field}.label + fe_user.username
    type = tcafe.conf.tables.{table}.fields.{field}.type

    for each fe_user as field
      print = tcafe.conf.tables.{table}.fields.{field}.label + field

Generic access
  for each tcafe.data.tables as table => $row
    for each table(=rows) as $field => $fv
      print = tcafe.conf.tables.{$table}.fields.{$field}.label + $fv
      type  = tcafe.conf.tables.{$table}.fields.{$field}.type
