
# delete database (just for tripal)
syntenic_region 2348

## delect syntenic blocks from feature table 
select count("feature_id") from feature where type_id = 2348
Delete FROM "chado"."feature" WHERE "type_id" = '2348'

* the block location and block relationship will be deleted automatically
* the gene-block-relationship (member_of) will be deleted automatically

## delete gene-gene relationship
paralogous_to : 166
orthologous_to : 164

SELECT count("feature_relationship_id") FROM "chado"."feature_relationship" WHERE "type_id" = '164'
SELECT count("feature_relationship_id") FROM "chado"."feature_relationship" WHERE "type_id" = '166'

164: 11552
166: 300829

DELETE FROM "chado"."feature_relationship" WHERE "type_id" = '164'
DELETE FROM "chado"."feature_relationship" WHERE "type_id" = '166'

## delete all from synblock table in public 
empty function of phppgadmin


# dataset design 

X1: find RO for synteny homologous, find RO for gene and it’s blockID 
From SO - Relationship:

	Homologous
	http://www.sequenceontology.org/browser/current_svn/term/orthologous_to 
	http://www.sequenceontology.org/browser/current_svn/term/paralogous_to

	Block – Homologous
	http://www.sequenceontology.org/browser/current_svn/term/member_of
	http://www.sequenceontology.org/browser/current_svn/term/part_of

X2: Name of Block:  Org Short Name, BL, block number 
	Example: CuPIBL00001

X3. Find SO for describe block?
	http://www.sequenceontology.org/browser/current_svn/term/SO:0000860
    Attribute describing sequence regions occurring in same order on chromosome of different species.

	http://www.sequenceontology.org/browser/current_svn/term/SO:0005858
    A region in which two or more pairs of homologous markers occur on the same chromosome in two or more species.

X4. Big Synteny Table save to block info 
	geneA   genea
	geneB
		    geneb
	geneC	genec

Example on icugi3


# example input

1. check cvterm table to find RO and SO

```
  cvterm_id  cv_id  name            dbxref_id
- 164        11     orthologous_to  245
- 166        11     paralogous_to   247
- 162        11     member_of       243
- 158        11     part_of         239
- 2363       11     syntenic_region 2943
```

2. insert block to database

PI183967 org id : 2
Gy14 org id: 3

table: featrue
id     orgid name        type
609069 2     CuPIBL00001 2363
609070 2     CuPIBL00002 2363
609071 3     CuGyBL00001 2363
609072 3     CuGyBL00002 2363

```
Prepare info: for srcfeature and members
fid   fname org
11253 Chr1  2
Chr1    EVM     gene    3006    4307    .       -       .       ID=CSPI01G00010; fid=22219
Chr1    EVM     gene    5486    14236   .       -       .       ID=CSPI01G00020; fid=22231 
Chr1    EVM     gene    18702   21805   .       +       .       ID=CSPI01G00030; fid=22252
Chr1    EVM     gene    22872   27698   .       -       .       ID=CSPI01G00040; fid=22267
Chr1    EVM     gene    30493   32725   .       +       .       ID=CSPI01G00050; fid=22275

fid    fname         org
290797 scaffold00154 3
scaffold00154   phytozome       gene    1188    7737    .       +       .       ID=Cucsa.010570; fid=298237
scaffold00154   phytozome       gene    8432    12167   .       -       .       ID=Cucsa.010580; fid=298252
scaffold00154   phytozome       gene    23964   24509   .       +       .       ID=Cucsa.010590; fid=298331 
scaffold00154   phytozome       gene    29240   32002   .       +       .       ID=Cucsa.010600; fid=298336 
scaffold00154   phytozome       gene    32705   41232   .       -       .       ID=Cucsa.010610; fid=298342 
```
table: featureloc

featureID  srcfeatureID 

3. create relationship for blocks and genes

3.1 relationship for genes

CSPI01G00010 (22219) Cucsa.010570 (298237)  166
CSPI01G00020 (22231) Cucsa.010580 (298252)  166
CSPI01G00050 (22275) Cucsa.010610 (298342)  166

3.2 relationship for blocks
(CuPIBL00001) 609069 (CuGyBL00001) 609071 166

3.3 relationship between gene and blocks

22219  609069 162
22231  609069 162
22275  609069 162

298237 609071 162
298252 609071 162
298342 609071 162

609069 ref:chr1:11253            start:3006 end:32725  
609071 ref:scaffold00154:290797  start:1188 end:41232








Tripal Job Launcher
Running as user 'icugi3'
-------------------
Calling: synview_parse_synfile(3, 2, /var/www/html/icugi3rd/sites/default/files/cpi_cpy.block.tripal.txt, 73)

NOTE: Checking the synteny file:
  /var/www/html/icugi3rd/sites/default/files/cpi_cpy.block.tripal.txt

NOTE: Loading of synteny file is performed using a database transaction.
If the load fails or is terminated prematurely then the entire set of
insertions/updates is rolled back and will not be found in the database

WD tripal_core: chado_insert_record; Cannot insert duplicate record into feature_relationship table: Array    [error]
(   
    [subject_id] => 430888
    [object_id] => 178098
    [type_id] => 166
)

ERROR (TRIPAL_CORE): chado_insert_record; Cannot insert duplicate record into feature_relationship table: Array
(   
    [subject_id] => 430888
    [object_id] => 178098
    [type_id] => 166
)

[site http://default] [TRIPAL ERROR] [TRIPAL_CORE] chado_insert_record; Cannot insert duplicate record into feature_relationship table: Array(    [subject_id] => 430888    [object_id] => 178098    [type_id] => 166)
WD tripal_core: chado_insert_record; Cannot insert duplicate record into feature_relationship table: Array    [error]
(   
    [subject_id] => 505587
    [object_id] => 56255
    [type_id] => 166
)

ERROR (TRIPAL_CORE): chado_insert_record; Cannot insert duplicate record into feature_relationship table: Array
(   
    [subject_id] => 505587
    [object_id] => 56255
    [type_id] => 166
)

[site http://default] [TRIPAL ERROR] [TRIPAL_CORE] chado_insert_record; Cannot insert duplicate record into feature_relationship table: Array(    [subject_id] => 505587    [object_id] => 56255    [type_id] => 166)
WD tripal_core: chado_insert_record; Cannot insert duplicate record into feature_relationship table: Array    [error]
(   
    [subject_id] => 518659
    [object_id] => 80470
    [type_id] => 166
)

ERROR (TRIPAL_CORE): chado_insert_record; Cannot insert duplicate record into feature_relationship table: Array
(   
    [subject_id] => 518659
    [object_id] => 80470
    [type_id] => 166
)

[site http://default] [TRIPAL ERROR] [TRIPAL_CORE] chado_insert_record; Cannot insert duplicate record into feature_relationship table: Array(    [subject_id] => 518659    [object_id] => 80470    [type_id] => 166)
WD tripal_core: chado_insert_record; Cannot insert duplicate record into feature_relationship table: Array    [error]
(   
    [subject_id] => 561534
    [object_id] => 72845
    [type_id] => 166
)

ERROR (TRIPAL_CORE): chado_insert_record; Cannot insert duplicate record into feature_relationship table: Array
(   
    [subject_id] => 561534
    [object_id] => 72845
    [type_id] => 166
)

[site http://default] [TRIPAL ERROR] [TRIPAL_CORE] chado_insert_record; Cannot insert duplicate record into feature_relationship table: Array(    [subject_id] => 561534    [object_id] => 72845    [type_id] => 166)
Parsing block 583 of 583. Memory: 89,982,272 bytes.







