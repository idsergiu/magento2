<?php

$create = $conn->raw_fetchRow("show create table eav_attribute", 'Create Table');
if (strpos($create, 'CONSTRAINT `FK_eav_attribute` FOREIGN KEY')!==false) {
    $conn->raw_query('ALTER TABLE eav_attribute DROP FOREIGN KEY `FK_eav_attribute`');
}

$create = $conn->raw_fetchRow("show create table eav_attribute_group", 'Create Table');
if (strpos($create, 'CONSTRAINT `FK_eav_attribute_group` FOREIGN KEY')!==false) {
    $conn->raw_query('ALTER TABLE eav_attribute_group DROP FOREIGN KEY `FK_eav_attribute_group`');
}

$create = $conn->raw_fetchRow("show create table eav_attribute_set", 'Create Table');
if (strpos($create, 'CONSTRAINT `FK_eav_attribute_set` FOREIGN KEY')!==false) {
    $conn->raw_query('ALTER TABLE eav_attribute_set DROP FOREIGN KEY `FK_eav_attribute_set`');
}

$create = $conn->raw_fetchRow("show create table eav_entity", 'Create Table');
if (strpos($create, 'CONSTRAINT `FK_eav_attribute` FOREIGN KEY')!==false) {
    $conn->raw_query('ALTER TABLE eav_entity DROP FOREIGN KEY `FK_eav_attribute`');
}

$create = $conn->raw_fetchRow("show create table eav_entity_attribute", 'Create Table');
if (strpos($create, 'CONSTRAINT `FK_eav_entity` FOREIGN KEY')!==false) {
    $conn->raw_query('ALTER TABLE eav_entity_attribute DROP FOREIGN KEY `FK_eav_entity`');
}
if (strpos($create, 'CONSTRAINT `FK_eav_entity_store` FOREIGN KEY')!==false) {
    $conn->raw_query('ALTER TABLE eav_entity_attribute DROP FOREIGN KEY `FK_eav_entity_store`');
}

$conn->multi_query(<<<EOT
delete from eav_attribute_set
where entity_type_id not in (select entity_type_id from eav_entity_type)
;

delete from eav_attribute_group
where attribute_set_id not in (select attribute_set_id from eav_attribute_set)
;

delete from eav_attribute
where entity_type_id not in (select entity_type_id from eav_entity_type)
;

delete from eav_entity_attribute 
where attribute_id not in (select attribute_id from eav_attribute)
    or entity_type_id not in (select entity_type_id from eav_entity_type)
    or attribute_set_id not in (select attribute_set_id from eav_attribute_set)
    or attribute_group_id not in (select attribute_group_id from eav_attribute_group)
;

alter table `eav_attribute`
    ,drop key `entity_type_id`, add unique `entity_type_id` (`entity_type_id`, `attribute_code`)
    ,add constraint `FK_eav_attribute` foreign key(`entity_type_id`) references `eav_entity_type` (`entity_type_id`) on delete cascade  on update cascade
; 

alter table `eav_attribute_group`
    ,add constraint `FK_eav_attribute_group` foreign key(`attribute_set_id`) references `eav_attribute_set` (`attribute_set_id`) on delete cascade  on update cascade
; 

alter table `eav_attribute_set` 
    ,add constraint `FK_eav_attribute_set` foreign key(`entity_type_id`) references `eav_entity_type` (`entity_type_id`) on delete cascade  on update cascade
; 

alter table `eav_entity` 
    ,change `entity_type_id` `entity_type_id` smallint (8)UNSIGNED  DEFAULT '0' NOT NULL 
    ,add constraint `FK_eav_entity` foreign key(`entity_type_id`) references `eav_entity_type` (`entity_type_id`) on delete cascade  on update cascade
    ,add constraint `FK_eav_entity_store` foreign key(`store_id`)references `core_store` (`store_id`) on delete cascade  on update cascade
;


alter table `eav_entity_attribute` 
    ,add constraint `FK_eav_entity_attribute` foreign key(`attribute_id`) references `eav_attribute` (`attribute_id`) on delete cascade  on update cascade;
    ,add constraint `FK_eav_entity_attribute_group` foreign key(`attribute_group_id`) references `eav_attribute_group` (`attribute_group_id`) on delete cascade  on update cascade 
;

alter table `eav_entity_type` 
    ,change `entity_name` `entity_type_code` varchar (50)  NOT NULL  COLLATE utf8_general_ci
; 
EOT
);