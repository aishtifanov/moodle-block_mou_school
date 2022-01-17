# Резервное копирование прошлогодних оценок в базу mou_archive

CREATE DATABASE IF NOT EXISTS mou_archive;
CREATE TABLE mou_archive.mdl_monit_school_class_discipline_5 LIKE mou.mdl_monit_school_class_discipline; 
INSERT INTO mou_archive.mdl_monit_school_class_discipline_5 SELECT * FROM mou.mdl_monit_school_class_discipline; 
CREATE TABLE mou_archive.mdl_monit_school_class_termtype_5 LIKE mou.mdl_monit_school_class_termtype; 
INSERT INTO mou_archive.mdl_monit_school_class_termtype_5 SELECT * FROM mou.mdl_monit_school_class_termtype; 
CREATE TABLE mou_archive.mdl_monit_school_marks_totals_term_5 LIKE mou.mdl_monit_school_marks_totals_term; 
INSERT INTO mou_archive.mdl_monit_school_marks_totals_term_5 SELECT * FROM mou.mdl_monit_school_marks_totals_term; 
CREATE TABLE mou_archive.mdl_monit_school_marks_totals_year_5 LIKE mou.mdl_monit_school_marks_totals_year; 
INSERT INTO mou_archive.mdl_monit_school_marks_totals_year_5 SELECT * FROM mou.mdl_monit_school_marks_totals_year; 
CREATE TABLE mou_archive.mdl_monit_school_subgroup_pupil_5 LIKE mou.mdl_monit_school_subgroup_pupil; 
INSERT INTO mou_archive.mdl_monit_school_subgroup_pupil_5 SELECT * FROM mou.mdl_monit_school_subgroup_pupil; 
TRUNCATE TABLE mou.mdl_monit_school_class_discipline; 
TRUNCATE TABLE mou.mdl_monit_school_class_termtype; 
TRUNCATE TABLE mou.mdl_monit_school_marks_totals_term; 
TRUNCATE TABLE mou.mdl_monit_school_marks_totals_year; 
TRUNCATE TABLE mou.mdl_monit_school_subgroup_pupil; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_1; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_2; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_3; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_4; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_5; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_6; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_7; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_8; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_9; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_10; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_11; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_12; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_13; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_14; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_15; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_16; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_17; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_18; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_19; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_20; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_21; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_22; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_23; 
TRUNCATE TABLE mou.mdl_monit_school_assignments_25; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_1; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_2; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_3; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_4; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_5; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_6; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_7; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_8; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_9; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_10; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_11; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_12; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_13; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_14; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_15; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_16; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_17; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_18; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_19; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_20; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_21; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_22; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_23; 
TRUNCATE TABLE mou.mdl_monit_school_attendance_25; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_1; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_2; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_3; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_4; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_5; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_6; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_7; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_8; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_9; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_10; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_11; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_12; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_13; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_14; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_15; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_16; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_17; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_18; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_19; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_20; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_21; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_22; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_23; 
TRUNCATE TABLE mou.mdl_monit_school_class_schedule_25; 
TRUNCATE TABLE mou.mdl_monit_school_marks_1; 
TRUNCATE TABLE mou.mdl_monit_school_marks_2; 
TRUNCATE TABLE mou.mdl_monit_school_marks_3; 
TRUNCATE TABLE mou.mdl_monit_school_marks_4; 
TRUNCATE TABLE mou.mdl_monit_school_marks_5; 
TRUNCATE TABLE mou.mdl_monit_school_marks_6; 
TRUNCATE TABLE mou.mdl_monit_school_marks_7; 
TRUNCATE TABLE mou.mdl_monit_school_marks_8; 
TRUNCATE TABLE mou.mdl_monit_school_marks_9; 
TRUNCATE TABLE mou.mdl_monit_school_marks_10; 
TRUNCATE TABLE mou.mdl_monit_school_marks_11; 
TRUNCATE TABLE mou.mdl_monit_school_marks_12; 
TRUNCATE TABLE mou.mdl_monit_school_marks_13; 
TRUNCATE TABLE mou.mdl_monit_school_marks_14; 
TRUNCATE TABLE mou.mdl_monit_school_marks_15; 
TRUNCATE TABLE mou.mdl_monit_school_marks_16; 
TRUNCATE TABLE mou.mdl_monit_school_marks_17; 
TRUNCATE TABLE mou.mdl_monit_school_marks_18; 
TRUNCATE TABLE mou.mdl_monit_school_marks_19; 
TRUNCATE TABLE mou.mdl_monit_school_marks_20; 
TRUNCATE TABLE mou.mdl_monit_school_marks_21; 
TRUNCATE TABLE mou.mdl_monit_school_marks_22; 
TRUNCATE TABLE mou.mdl_monit_school_marks_23; 
TRUNCATE TABLE mou.mdl_monit_school_marks_25;