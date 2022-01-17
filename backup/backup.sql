CREATE TABLE  mdl_monit_school_backup (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `nameb` varchar(255) NOT NULL default '',
  `databackup` DATETIME,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO mdl_capabilities (name,captype,contextlevel,component)
VALUES('block/mou_school:createbackup','write',10,'block/mou_school');

INSERT INTO mdl_capabilities (name,captype,contextlevel,component)
VALUES('block/mou_school:restoringbackup','write',10,'block/mou_school');

INSERT INTO mdl_capabilities (name,captype,contextlevel,component)
VALUES('block/mou_school:downloadbackup','read',10,'block/mou_school');

INSERT INTO mdl_capabilities (name,captype,contextlevel,component)
VALUES('block/mou_school:viewbackup','read',10,'block/mou_school');