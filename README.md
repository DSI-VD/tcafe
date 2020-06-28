# TYPO3 tcafe #


## Introduction ##
The backend TYPO3 is able to display any TCA tables.
This extension try just to do the same for the frontend.


### Components ###
- Data
- Filter and Search
- Infos nb/nb
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
- Create a MyTList.html or use the default template
- Create a MyDetail.html or use the default template
- Add the plugin on a page
- Select the myTable.yaml configuration


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


## How it works ##
- Create a new TCA or use an existing one
- Create a myTable.yaml configuration
    - set the table to work with
    - configure the VIEW
- Create a MyTableList.html or use the default template
- Create a MyDetail.html or use the default template
- Controller read myTable.yaml configuration 
    - DataFinder get first level records with filter (input, select) relation
    - DataFinder get and set the Type of the field used in FLUID Types/{field.config.type}
    - 
       
 
## Add the demo table ##
- update tcafe table
- import tcafe-demo.t3d


## External calls ##
- https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.css