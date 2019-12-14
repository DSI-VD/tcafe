# TYPO3 tcafe #


## Introduction ##
The backend TYPO3 is able to display any TCA tables.
This extension try just do the same for the frontend.


### Components ###
- Data
- Filters
- Infos
- Pagination
- List view
- Detail view

## Requirements ##
- TYPO3 CMS 8.7.x - 9.5.99
- PHP 7.2.0-7.2.99

## Installation ##

### Installation using composer ###

## Create a view ###
- Create a new TCA or use an existing one
- Create a myTable.yaml configuration
- Create a List.html or use the default template
- Create a Detail.html or use the default template

## Code ##
- The TCA is the base of this extension
- tcafe.yaml is the extended TCA configuration for the frontend
- The controller reads the TCA, the tcafe and the additional filter, retrieve the datas, then inject the content to fluid
- Fluid is the view
- Use the same backend code of BE.List and BE.Edit views
- No Data model
- No tests
- No flexform
- No DB
- No css
- Use (doesn't use) SQL configuration ?




    <!-- tcafe.data.tables.[v].fields.[v].value -->
    <!-- tcafe.data.tables.[fe_users].fielsds.[username].value = name -->
    <!-- tcafe.data.[fe_users].[username] = name -->
    <!-- [fe_users].[username] = name convention raccourci-->
    
    <!-- tcafe.conf.tables.[v].fields.[v]._params_.[v] ->
    <!-- tcafe.conf.tables.[fe_users].fields.[username].label = FE USERS ->
    <!-- [fe_users].fields.[username].label = FE USERS ->
    <!-- [fe_users].html.title = TABLE OF FE USERS ->
    <!-- [fe_users].html.th.display = none ->
    <!-- [fe_users].paginate.activ = 1 ->
    <!-- [fe_users].paginate.itemsPerPage = 10 -> ou prendre du TCA
    <!-- [fe_users].infos.activ = 1 ->
