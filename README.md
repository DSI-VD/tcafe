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
