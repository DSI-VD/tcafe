DATA
-----------
- Data for Fluid
- Data for javascript @todo


Demo
-------------
- add test sorts


Fluid
---------------------


Javscript View
---------------------
@nice to have

Pagination
-----------
- done

Sort
---------------
- done

Filter
--------------
- check filter with foreign values field.foreignTitle


Relations
------------
cf code and demo

TYPES of field
--------------
cf code

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

