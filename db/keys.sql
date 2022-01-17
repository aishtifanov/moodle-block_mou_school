#=============================================

ALTER TABLE `mdl_monit_school_discipline_lesson` RENAME TO `mdl_monit_school_discipline_lesson___`;

ALTER TABLE `mdl_monit_school_assignments` RENAME TO `mdl_monit_school_assignments___`;

ALTER TABLE `mdl_monit_school_attendance` RENAME TO `mdl_monit_school_attendance___`;

ALTER TABLE `mdl_monit_school_class_schedule` RENAME TO `mdl_monit_school_class_schedule___`;

ALTER TABLE `mdl_monit_school` DROP INDEX `rayonid`,
 ADD INDEX `rid_yid`(`rayonid`, `yearid`, `isclosing`),
 ADD INDEX `uniq_yid`(`uniqueconstcode`, `yearid`);

ALTER TABLE `mdl_monit_school_class` DROP COLUMN `curriculumid`,
 MODIFY COLUMN `name` CHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 0,
 DROP INDEX `schoolid`,
 ADD INDEX `schoolid` USING BTREE(`schoolid`, `yearid`, `parallelnum`);

ALTER TABLE `mdl_monit_school_class_discipline` DROP INDEX `schoolid_idx`,
 ADD INDEX `classid_idx`(`classid`, `disciplineid`);

ALTER TABLE `mdl_monit_school_marks_totals_term` DROP INDEX `schoolid_idx`,
 ADD INDEX `user_disc_term`(`userid`, `classdisciplineid`, `termid`);

ALTER TABLE `mdl_monit_school_pupil_card` DROP INDEX `userid`,
 ADD INDEX `userid` USING BTREE(`userid`, `yearid`);

ALTER TABLE `mdl_monit_school_discipline_unit` DROP INDEX `schoolid_idx`,
 ADD INDEX `plan_idx`(`planid`);
 
#=========================================

DROP TABLE mdl_monit_attestation;
DROP TABLE mdl_monit_attestation_head;
DROP TABLE mdl_monit_attestation_master;
DROP TABLE mdl_monit_criteria;
DROP TABLE mdl_monit_estimates;
DROP TABLE mdl_monit_estimates_typedata;
DROP TABLE mdl_monit_meeting_ak;
 
ALTER TABLE `mou`.`mdl_monit_att_attestation` DROP INDEX `idx_staffid`,
 ADD INDEX `idx_staffid` USING BTREE(`staffid`, `stafftypeid`, `criteriaid`);
 
ALTER TABLE `mou`.`mdl_monit_att_appointment` DROP INDEX `idx_staffid`,
 ADD INDEX `idx_staffid` USING BTREE(`staffid`, `stafftypeid`);
 
ALTER TABLE `mou`.`mdl_monit_att_criteria` DROP INDEX `idx_stafftypeid`,
 ADD INDEX `idx_stafftypeid` USING BTREE(`stafftypeid`, `yearid`),
 ADD INDEX `year_idx`(`yearid`); 
 
ALTER TABLE `mou`.`mdl_monit_att_estimates` DROP INDEX `idx_criteriaid`,
 ADD INDEX `idx_criteriaid` USING BTREE(`criteriaid`, `mark`);
 
ALTER TABLE `mou`.`mdl_monit_att_meeting_ak` ADD INDEX `level_idx`(`level_ak`);

ALTER TABLE `mou`.`mdl_monit_att_staff` DROP INDEX `idx_rayonid`,
 ADD INDEX `idx_rayonid` USING BTREE(`rayonid`, `edutypeid`);
 
ALTER TABLE `mou`.`mdl_monit_att_stst` ADD INDEX `edutype_idx`(`edutypeid`);


ALTER TABLE `mdl_monit_college` DROP INDEX `rayonid`,
 ADD INDEX `rid_yid`(`rayonid`, `yearid`, `isclosing`),
 ADD INDEX `uniq_yid`(`uniqueconstcode`, `yearid`);


ALTER TABLE `mdl_monit_education` DROP INDEX `rayonid`,
 ADD INDEX `rid_yid`(`rayonid`, `yearid`, `isclosing`),
 ADD INDEX `uniq_yid`(`uniqueconstcode`, `yearid`);
 
ALTER TABLE `mou`.`mdl_monit_form` ADD INDEX `period_idx`(`period`),
 ADD INDEX `level_idx`(`levelmonit`);
 
ALTER TABLE `mou`.`mdl_monit_nsop_family` ADD INDEX `deleted_idx`(`deleted`, `rayonid`),
 ADD INDEX `user_idx`(`userid`);

ALTER TABLE `mou`.`mdl_monit_nsop_infopupil` ADD INDEX `famid_idx`(`famid`);

ALTER TABLE `mou`.`mdl_monit_nsop_parent` ADD INDEX `rayon_idx`(`rayonid`);

ALTER TABLE `mou`.`mdl_monit_nsop_pupil` ADD INDEX `rayon_idx`(`rayonid`),
 ADD INDEX `user_idx`(`userid`);
 
ALTER TABLE `mou`.`mdl_monit_rating_school`
 ADD INDEX `yearid_idx`(`yearid`, `schoolid`, `criteriaid`),
 ADD INDEX `rayonid_idx`(`rayonid`, `yearid`);
                      
ALTER TABLE `mou`.`mdl_monit_rating_total` ADD INDEX `yearid_idx`( `schoolid`, `yearid`); 
  
ALTER TABLE `mou`.`mdl_monit_razdel` DROP INDEX `formid`,
 ADD INDEX `formid` USING BTREE(`formid`, `shortname`),
 ADD INDEX `shortname1_idx`(`shortname`, `reported`);
 
ALTER TABLE `mou`.`mdl_monit_school_class` ADD INDEX `yearid_idx`(`yearid`, `schoolid`, `name`),
 ADD INDEX `parallel_idx`(`parallelnum`);  
 
ALTER TABLE `mou`.`mdl_monit_school_class_termtype` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `parallelnum`, `termtypeid`);
    
    

#=========================================    

DROP TABLE `mdl_monit_school_discipline_lesson___`;
DROP TABLE `mdl_monit_school_assignments___`;
DROP TABLE `mdl_monit_school_attendance___`;
DROP TABLE `mdl_monit_school_class_schedule___`;
DROP TABLE mdl_monit_staff;


ALTER TABLE `mou`.`mdl_monit_school_discipline_plan` ADD COLUMN `total` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `parallelnum`;

ALTER TABLE `mou`.`mdl_monit_school_curriculum` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `parallelnum`, `profileid`);

ALTER TABLE `mou`.`mdl_monit_school_curriculum` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `parallelnum`, `profileid`),
 ADD INDEX `classid_idx`(`classid`, `disciplineid`, `profileid`, `componentid`);
 
ALTER TABLE `mou`.`mdl_monit_school_curriculum_totals` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `parallelnum`, `componentid`);
 
ALTER TABLE `mou`.`mdl_monit_school_discipline` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `disciplinedomainid`);
 
ALTER TABLE `mou`.`mdl_monit_school_discipline_plan` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `disciplineid`, `parallelnum`);


ALTER TABLE `mou`.`mdl_monit_school_gia_dates` DROP INDEX `discegeid_index`,
 ADD INDEX `discegeid_index` USING BTREE(`discegeid`, `yearid`, `timeload`),
 ADD INDEX `discmiid_idx`(`discmiid`, `yearid`),
 ADD INDEX `code_idx`(`codepredmet`);
 
ALTER TABLE `mou`.`mdl_monit_school_holidays` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `termtypeid`);
 
ALTER TABLE `mou`.`mdl_monit_school_listforms` DROP INDEX `schoolid`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `datemodified`, `shortname`);  
    
ALTER TABLE `mou`.`mdl_monit_school_listforms` ADD INDEX `rayon_idx`(`rayonid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_1` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_2` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);
   
ALTER TABLE `mou`.`mdl_monit_school_marks_3` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_4` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_5` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_6` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_7` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_8` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_9` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_10` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_11` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_12` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_13` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_14` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_15` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_16` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_17` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_18` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_19` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_20` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_21` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_22` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_23` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);

ALTER TABLE `mou`.`mdl_monit_school_marks_25` DROP INDEX `userid_idx`,
 ADD INDEX `userid_idx` USING BTREE(`userid`, `scheduleid`);
 
ALTER TABLE `mou`.`mdl_monit_school_marks_totals_year` DROP INDEX `classdisciplineid`,
 ADD INDEX `classdisciplineid` USING BTREE(`classdisciplineid`, `userid`);
 
ALTER TABLE `mou`.`mdl_monit_school_movepupil` ADD INDEX `userid_idx`(`userid`);

ALTER TABLE `mou`.`mdl_monit_school_schedule_bells` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `weekdaynum`);
 
ALTER TABLE `mou`.`mdl_monit_school_subgroup` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `disciplineid`);
 
ALTER TABLE `mou`.`mdl_monit_school_subgroup_pupil` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `userid`, `classdisciplineid`);    

ALTER TABLE `mou`.`mdl_monit_school_teacher` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `disciplineid`);
 
ALTER TABLE `mou`.`mdl_monit_school_term` DROP INDEX `schoolid_idx`,
 ADD INDEX `schoolid_idx` USING BTREE(`schoolid`, `termtypeid`); 
