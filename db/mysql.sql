# Таблицы Мониторинга учебного процесса образовательных учреждений
# $Id: mysql.sql,v 1.18 2009/09/30 05:46:09 Shtifanov Exp $

DROP TABLE IF EXISTS `prefix_monit_school_class_pupil`;

# Таблица - периоды обучения с датами начала и окончания
CREATE TABLE `prefix_monit_school_term` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `termtypeid` int(10) unsigned NOT NULL default '0',
  `yearid` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `datestart` date NOT NULL,
  `dateend` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - типы периодов обучения (четверть, семестр и т.п.) 
CREATE TABLE `prefix_monit_school_term_type` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) not null,
  `countsterm` integer unsigned NOT NULL,
  PRIMARY KEY(`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `prefix_monit_school_term_type` (`id`,`name`,`countsterm`) VALUES  (1,'четверть',4);
INSERT INTO `prefix_monit_school_term_type` (`id`,`name`,`countsterm`) VALUES  (2,'триместр',3);
INSERT INTO `prefix_monit_school_term_type` (`id`,`name`,`countsterm`) VALUES  (3,'полугодие',2);

# Таблица - периоды каникул и праздничных дней
CREATE TABLE `prefix_monit_school_holidays` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL,
  `datestart` date NOT NULL,
  `dateend` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - учителя по предметам
CREATE TABLE `prefix_monit_school_teacher` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `teacherid` int(10) unsigned NOT NULL default '0',
  `disciplineid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`),
  KEY `teacherid_idx` (`teacherid`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - компоненты учебного плана (базовый, школьный и т.п.)
CREATE TABLE `prefix_monit_school_component` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - профили учебного плана (общеобразовательный, технический и т.п.)
CREATE TABLE `prefix_monit_school_profiles_curriculum` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `schoolid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(255) not null,
  `profilenumlist` varchar(30) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - предметы, преподаваемые в школе
CREATE TABLE `prefix_monit_school_discipline` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `disciplinedomainid` int(10) unsigned NOT NULL default '0',
  `dgroupid` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `shortname` varchar(30) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - учебный план
CREATE TABLE `prefix_monit_school_curriculum` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parallelnum` int(10) unsigned NOT NULL default '0',
  `yearid` int(10) unsigned NOT NULL default '0',
  `schoolid` int(10) unsigned NOT NULL default '0',
  `classid` int(10) unsigned NOT NULL default '0',
  `componentid` int(10) unsigned NOT NULL default '0',  
  `profileid` int(10) unsigned NOT NULL default '0',
  `disciplineid` int(10) unsigned NOT NULL default '0',
  `hours` float NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - нагрузка в часах по параллелям в учебном году
CREATE TABLE `prefix_monit_school_curriculum_totals` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `parallelnum` int(10) unsigned NOT NULL default '0',
  `componentid` int(10) unsigned NOT NULL default '0',
  `yearid` int(10) unsigned NOT NULL default '0',
  `hourstotal` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - предметные области дисциплин (естествознание и т.п.)
CREATE TABLE `prefix_monit_school_discipline_domain` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - виды подгрупп в классах (1-ая подгруппа, 2-ая подгруппа, девочки, мальчики)
CREATE TABLE `prefix_monit_school_subgroup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `disciplineid` int(10) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL,
  `shortname` varchar(10) default NULL,
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - ученики, входящие в подгруппы
CREATE TABLE `prefix_monit_school_subgroup_pupil` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `schoolid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `userid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `classdisciplineid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - связь классов(подгупп) с предметами и учителями)
CREATE TABLE `prefix_monit_school_class_discipline` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `schoolid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `classid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `schoolsubgroupid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `disciplineid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `teacherid` INTEGER UNSIGNED NOT NULL DEFAULT 0,    
  `name` varchar(100) not null,
  `shortname` varchar(10) default NULL,  
  `descriptions` varchar(30),
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - итоговые оценки за периоды
CREATE TABLE `prefix_monit_school_marks_totals_term` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `userid` int(10) unsigned NOT NULL default '0',
  `classdisciplineid` int(10) unsigned NOT NULL default '0',
  `termid` int(10) unsigned NOT NULL default '0',
  `mark` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - справочник школьных звонков (начало и конец уроков)
CREATE TABLE `prefix_monit_school_schedule_bells` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `timestart` time NOT NULL default '00:00:00',
  `timeend` time NOT NULL default '00:00:00',
  `smena` tinyint(4) NOT NULL default '1',
  `yearid` int(10) unsigned NOT NULL default '0',
  `lessonnum` tinyint(4) NOT NULL default '1',
  `weekdaynum` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - расписание класса по урокам, по дням и аудиториям
CREATE TABLE `prefix_monit_school_class_schedule` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `classid` int(10) unsigned NOT NULL default '0',  
  `classdisciplineid` int(10) unsigned NOT NULL default '0',  
  `lessonid` int(10) unsigned NOT NULL default '0',
  `teacherid` int(10) unsigned NOT NULL default '0',
  `roomid` int(10) unsigned NOT NULL default '0',
  `datestart` date NOT NULL,
  `schedulebellsid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - кабинеты школы (аудитории)
CREATE TABLE `prefix_monit_school_room` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL,
  `floor` tinyint(4) NOT NULL default '0',
  `seats` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - связь кабинетов с дисциплинами
CREATE TABLE `prefix_monit_school_room_discipline` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `roomid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `disciplineid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`),
  KEY `roomid` (`roomid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - связь кабинетов с учителями
CREATE TABLE `prefix_monit_school_room_teacher` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `roomid` int(10) unsigned NOT NULL default '0',
  `teacherid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `roomid` (`roomid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Тематический план по предмету
CREATE TABLE `prefix_monit_school_discipline_plan` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `yearid` int(10) unsigned NOT NULL default '0',
  `disciplineid` int(10) unsigned NOT NULL default '0',
  `parallelnum` tinyint(4) NOT NULL default '1',
  `name` varchar(255) NOT NULL default '',
  `textbookids` varchar(255) default '',
  `description` varchar(500) default NULL,
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Разделы тематического плана
CREATE TABLE `prefix_monit_school_discipline_unit` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `schoolid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `planid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `number` smallint NOT NULL DEFAULT 1,
  `name` varchar(255) not null default '',    
  `description` varchar(255) default '',
  PRIMARY KEY(`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Темы уроков, входящих в разделы тематического плана
CREATE TABLE `prefix_monit_school_discipline_lesson` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `unitid` int(10) unsigned NOT NULL default '0',
  `number` smallint(6) NOT NULL default '1',
  `name` varchar(255) NOT NULL default '',
  `hours` int(11) NOT NULL default '0',
  `textbookids` varchar(255) default '',
  `description` varchar(500) default NULL,
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Задание урока с указанием его типа
CREATE TABLE `prefix_monit_school_assignments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `classdisciplineid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `datestart` date NOT NULL,
  `datefinish` date NOT NULL,
  `description` varchar(255) default '',
  `type_ass` varchar(1) default 'O',
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Посещаемость
CREATE TABLE `prefix_monit_school_attendance` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `reason` varchar(2) default 'ОТ',
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Таблица - Итоговые оценки за учебный год
CREATE TABLE `prefix_monit_school_marks_totals_year` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `yearid` int(10) unsigned NOT NULL default '0',
  `classdisciplineid` int(10) unsigned NOT NULL default '0',
  `userid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `classdisciplineid` (`classdisciplineid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Таблица - Информация о том какой класс в какую смену учится в каком периоде
CREATE TABLE `prefix_monit_school_class_smena` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `classid` int(10) unsigned NOT NULL default '0',
  `termid` int(10) unsigned NOT NULL default '0',
  `smena` tinyint(4) default '1',
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`),
  KEY `classid_idx` (`classid`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица хранит соответствие номера параллели и учебного периода
CREATE TABLE `prefix_monit_school_class_termtype` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `parallelnum` int(10) unsigned NOT NULL default '0',
  `termtypeid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `schoolid_idx` (`schoolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - группы дисциплин (например, "Иностранный язык" является группой
CREATE TABLE `prefix_monit_school_discipline_group` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `schoolid` int(10) unsigned NOT NULL default '0',
  `disciplinedomainid` int(10) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица содержит список названий областей знаний
CREATE TABLE `prefix_monit_school_datadir_domain` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `dirname` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `prefix_monit_school_datadir_domain` (`id`,`dirname`) VALUES  (1,'Филология');
INSERT INTO `prefix_monit_school_datadir_domain` (`id`,`dirname`) VALUES  (2,'Математика');
INSERT INTO `prefix_monit_school_datadir_domain` (`id`,`dirname`) VALUES  (3,'Обществознание');
INSERT INTO `prefix_monit_school_datadir_domain` (`id`,`dirname`) VALUES  (4,'Естествознание');
INSERT INTO `prefix_monit_school_datadir_domain` (`id`,`dirname`) VALUES  (5,'Искусство');
INSERT INTO `prefix_monit_school_datadir_domain` (`id`,`dirname`) VALUES  (6,'Физическая культура');
INSERT INTO `prefix_monit_school_datadir_domain` (`id`,`dirname`) VALUES  (7,'Технология');

# Таблица содержит список названий областей знаний
CREATE TABLE `prefix_monit_school_datadir_dgroup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `domdatadirid` int(10) unsigned NOT NULL default '0',
  `dgroupname` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `prefix_monit_school_datadir_dgroup` (`id`,`domdatadirid`, `dgroupname`) VALUES  (1, 1, 'Иностранные языки');


# Таблица содержит список предметов, изучаемых в школах области
CREATE TABLE `prefix_monit_school_datadir_discipline` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `domdatadirid` int(10) unsigned NOT NULL default '0',
  `disciplinename` varchar(250) NOT NULL,
  `discipabbreviature` varchar(15) NOT NULL,
  `dgroupdatadirid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (1,2,'Алгебра','Алг',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (2,2,'Геометрия','Геом',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (3,2,'Математика','Матем',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (4,4,'География','Геогр',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (5,1,'Русский язык','Рус.яз.',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (6,5,'МХК','МХК',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (7,3,'История','Ист.',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (8,1,'Литература','Лит-ра',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (9,7,'Черчение','Черч.',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (10,1,'Чтение','Чтен',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (11,1,'Английский язык','Англ.яз.',1);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (12,1,'Немецкий язык','Нем.яз.',1);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (13,1,'Французский язык','Фр.яз.',1);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (14,4,'Биология','Биол.',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (15,4,'Химия','Хим',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (16,5,'Музыка','Муз.',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (17,5,'Изобразительное искусство','ИЗО',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (18,7,'Технология','Техн',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (19,4,'Природоведение','Прир',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (20,2,'Информатика и ИКТ','Инф',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (21,4,'Физика','Физ',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (22,6,'Физкультура','Физ-ра',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (24,6,'Основы безопасности жизнедеятельности','ОБЖ',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (25,3,'Обществознание','Общ',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (26,3,'История России','Ист.Рос.',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (27,2,'Экономика','Эконом.',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (28,3,'Православие','Правос.',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (29,4,'Окружающий мир','Окр.мир.',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (30,3,'Краеведение','Краевед.',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (31,3,'Всеобщая история','Общ.ист.',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (32,3,'Основы правовых знаний','Осн.прав.знан.',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (33,5,'Искусство','Иск.',0);
INSERT INTO `prefix_monit_school_datadir_discipline` (`id`,`domdatadirid`,`disciplinename`,`discipabbreviature`,`dgroupdatadirid`) VALUES  (34,4,'Экология Белгородской области','Эк.Белг.обл.',0);

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_1` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_2` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_3` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_4` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_5` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_6` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_7` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_8` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_9` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_10` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_11` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_12` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_13` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_14` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_15` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_16` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_17` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_18` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_19` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_20` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_21` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Таблица - Оценка за задание на уроке (номер - это id района)
CREATE TABLE `prefix_monit_school_marks_22` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `scheduleid` int(10) unsigned NOT NULL default '0',
  `mark` smallint(6) NOT NULL default '0',
  `datedone` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid_idx` (`userid`),  
  KEY `scheduleid` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
