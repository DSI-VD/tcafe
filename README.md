# TYPO3 tcafe #


## Introduction ##
The backend TYPO3 is able to display any TCA table.
This extension just do the same for the frontend.

## Requirements ##
- TYPO3 CMS 8.7.x - 9.5.99
- PHP 7.2.0-7.2.99

## Installation ##


### Installation using composer ###

## Create a view ###
- Create a new TCA or use an existing one
- Add a myTable.yaml configuration
- Add a List.html or use the default template
- Add a Detail.html or use the default template

## Code ##
- The TCA is the base of this extension
- tcafe.yaml is the configuration of the frontend
- The controller reads the TCA and the tcafe and inject datas and/or data in fluid
- Fluid is the view
- No flexform
- No DB
