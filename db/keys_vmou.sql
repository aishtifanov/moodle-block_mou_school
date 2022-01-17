#=============================================

ALTER TABLE `vmou`.`mdl_monit_school` DROP INDEX `rayonid`,
 ADD INDEX `rid_yid`(`rayonid`, `yearid`, `isclosing`),
 ADD INDEX `uniq_yid`(`uniqueconstcode`, `yearid`);

 ALTER TABLE `vmou`.`mdl_monit_college` DROP INDEX `rayonid`,
 ADD INDEX `rid_yid`(`rayonid`, `yearid`, `isclosing`),
 ADD INDEX `uniq_yid`(`uniqueconstcode`, `yearid`);

ALTER TABLE `vmou`.`mdl_monit_education` DROP INDEX `rayonid`,
 ADD INDEX `rid_yid`(`rayonid`, `yearid`, `isclosing`),
 ADD INDEX `uniq_yid`(`uniqueconstcode`, `yearid`);

ALTER TABLE `vmou`.`mdl_monit_school_class` DROP COLUMN `curriculumid`,
 MODIFY COLUMN `name` CHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 0,
 DROP INDEX `schoolid`,
 ADD INDEX `schoolid` USING BTREE(`schoolid`, `yearid`, `parallelnum`);

ALTER TABLE `vmou`.`mdl_monit_school_class_discipline` DROP INDEX `schoolid_idx`,
 ADD INDEX `classid_idx`(`classid`, `disciplineid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_totals_term` DROP INDEX `schoolid_idx`,
 ADD INDEX `user_disc_term`(`userid`, `classdisciplineid`, `termid`);

ALTER TABLE `vmou`.`mdl_monit_school_pupil_card` DROP INDEX `userid`,
 ADD INDEX `userid` USING BTREE(`userid`, `yearid`);

ALTER TABLE `vmou`.`mdl_monit_form` ADD INDEX `period_idx`(`period`),
 ADD INDEX `level_idx`(`levelmonit`);
 
ALTER TABLE `vmou`.`mdl_monit_razdel` DROP INDEX `formid`,
 ADD INDEX `formid` USING BTREE(`formid`, `shortname`),
 ADD INDEX `shortname1_idx`(`shortname`, `reported`);
 
ALTER TABLE `vmou`.`mdl_monit_school_class` ADD INDEX `yearid_idx`(`yearid`, `schoolid`, `name`),
 ADD INDEX `parallel_idx`(`parallelnum`);  
 
ALTER TABLE `vmou`.`mdl_monit_school_class_termtype` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `parallelnum`, `termtypeid`);
    
ALTER TABLE `vmou`.`mdl_monit_school_curriculum` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `parallelnum`, `profileid`);

ALTER TABLE `vmou`.`mdl_monit_school_curriculum` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `parallelnum`, `profileid`),
 ADD INDEX `classid_idx`(`classid`, `disciplineid`, `profileid`, `componentid`);
 
ALTER TABLE `vmou`.`mdl_monit_school_curriculum_totals` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `parallelnum`, `componentid`);
 
ALTER TABLE `vmou`.`mdl_monit_school_discipline` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `disciplinedomainid`);
 
ALTER TABLE `vmou`.`mdl_monit_school_discipline_plan` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `disciplineid`, `parallelnum`);


ALTER TABLE `vmou`.`mdl_monit_school_gia_dates` DROP INDEX `discegeid_index`,
 ADD INDEX `discegeid_index` USING BTREE(`discegeid`, `yearid`, `timeload`),
 ADD INDEX `discmiid_idx`(`discmiid`, `yearid`),
 ADD INDEX `code_idx`(`codepredmet`);
 
ALTER TABLE `vmou`.`mdl_monit_school_holidays` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `termtypeid`);
 
ALTER TABLE `vmou`.`mdl_monit_school_listforms` DROP INDEX `schoolid`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `datemodified`, `shortname`);  
    
ALTER TABLE `vmou`.`mdl_monit_school_listforms` ADD INDEX `rayon_idx`(`rayonid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_1` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_2` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);
   
ALTER TABLE `vmou`.`mdl_monit_school_marks_3` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_4` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_5` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_6` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_7` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_8` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_9` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_10` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_11` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_12` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_13` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_14` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_15` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_16` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_17` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_18` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_19` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_20` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_21` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_22` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `vmou`.`mdl_monit_school_marks_23` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);
 
ALTER TABLE `vmou`.`mdl_monit_school_marks_totals_year` DROP INDEX `classdisciplineid`,
 ADD INDEX `classdisciplineid` USING BTREE(`classdisciplineid`, `userid`);
 
ALTER TABLE `vmou`.`mdl_monit_school_movepupil` ADD INDEX `userid_idx`(`userid`);

ALTER TABLE `vmou`.`mdl_monit_school_schedule_bells` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `weekdaynum`);
 
ALTER TABLE `vmou`.`mdl_monit_school_subgroup` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `disciplineid`);
 
ALTER TABLE `vmou`.`mdl_monit_school_subgroup_pupil` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `userid`, `classdisciplineid`);    

ALTER TABLE `vmou`.`mdl_monit_school_teacher` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `disciplineid`);
 
ALTER TABLE `vmou`.`mdl_monit_school_term` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `termtypeid`); 

ALTER TABLE `vmou`.`mdl_monit_school_attendance` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);
 
ALTER TABLE `vmou`.`mdl_monit_school_assignments` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `type_ass`); 

ALTER TABLE `vmou`.`mdl_monit_school_discipline_plan` ADD COLUMN `total` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `parallelnum`;

ALTER TABLE `vmou`.`mdl_monit_school_discipline_unit` DROP INDEX `schoolid_idx`,
 ADD INDEX `plan_idx`(`planid`);

ALTER TABLE `vmou`.`mdl_monit_school_discipline_lesson` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `unitid`);