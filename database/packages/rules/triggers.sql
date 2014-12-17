-- -----------------------------------------------------
-- Triggers for package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- trigger bm_rules_repeat_audit_insert
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS `bm_rules_repeat_audit_insert`$$

CREATE TRIGGER `bm_rules_repeat_audit_insert` AFTER INSERT ON `rules_repeat`
FOR EACH ROW
INSERT INTO audit_rules_repeat (`change_seq`,`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`repeat_minute`,`repeat_hour`,`repeat_dayofweek`,`repeat_dayofmonth`,`repeat_month`
                                ,`start_from`,`end_at`,`rule_duration`,`changed_by`,`action`,`change_time`,`valid_from`,`valid_to`) 
                        VALUES (NULL, NEW.`rule_id`, NEW.`rule_name`, NEW.`rule_type`, NEW.`rule_repeat`, NEW.`repeat_minute`, NEW.`repeat_hour`, NEW.`repeat_dayofweek`
                                , NEW.`repeat_dayofmonth`, NEW.`repeat_month`, NEW.`start_from`, NEW.`end_at`, NEW.`rule_duration`, USER(), 'I', NOW(),NEW.`valid_from`,NEW.`valid_to`);
$$

-- -----------------------------------------------------
-- trigger bm_rules_repeat_audit_update
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS `bm_rules_repeat_auditupdate`$$

CREATE TRIGGER `bm_rules_repeat_audit_update` AFTER UPDATE ON `rules_repeat`
FOR EACH ROW
INSERT INTO audit_rules_repeat (`change_seq`,`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`repeat_minute`,`repeat_hour`,`repeat_dayofweek`,`repeat_dayofmonth`,`repeat_month`
                                ,`start_from`,`end_at`,`rule_duration`,`changed_by`,`action`,`change_time`,`valid_from`,`valid_to`) 
                        VALUES (NULL, NEW.`rule_id`, NEW.`rule_name`, NEW.`rule_type`, NEW.`rule_repeat`, NEW.`repeat_minute`, NEW.`repeat_hour`, NEW.`repeat_dayofweek`
                                , NEW.`repeat_dayofmonth`, NEW.`repeat_month`, NEW.`start_from`, NEW.`end_at`, NEW.`rule_duration`, USER(), 'U', NOW(),NEW.`valid_from`,NEW.`valid_to`);

$$

-- -----------------------------------------------------
-- trigger bm_rules_audit_delete
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS `bm_rules_repeat_audit_delete`$$

CREATE TRIGGER `bm_rules_repeat_audit_delete` AFTER DELETE ON `rules_repeat`
FOR EACH ROW
INSERT INTO audit_rules_repeat (`change_seq`,`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`repeat_minute`,`repeat_hour`,`repeat_dayofweek`,`repeat_dayofmonth`,`repeat_month`
                                ,`start_from`,`end_at`,`rule_duration`,`changed_by`,`action`,`change_time`,`valid_from`,`valid_to`) 
                        VALUES (NULL, OLD.`rule_id`, OLD.`rule_name`, OLD.`rule_type`, OLD.`rule_repeat`, OLD.`repeat_minute`, OLD.`repeat_hour`, OLD.`repeat_dayofweek`
                                , OLD.`repeat_dayofmonth`, OLD.`repeat_month`, OLD.`start_from`, OLD.`end_at`, OLD.`rule_duration`, USER(), 'D', NOW(),OLD.`valid_from`,OLD.`valid_to`);

$$


-- -----------------------------------------------------
-- trigger bm_rules_adhoc_audit_insert
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS `bm_rules_adhoc_audit_insert`$$

CREATE TRIGGER `bm_rules_adhoc_audit_insert` AFTER INSERT ON `rules_adhoc`
FOR EACH ROW
INSERT INTO audit_rules_adhoc (`change_seq`,`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`rule_duration`,`changed_by`,`action`,`change_time`,`valid_from`,`valid_to`) 
                        VALUES (NULL, NEW.`rule_id`, NEW.`rule_name`, NEW.`rule_type`, NEW.`rule_repeat`, NEW.`rule_duration`, USER(), 'I', NOW(),NEW.`valid_from`,NEW.`valid_to`);
$$


-- -----------------------------------------------------
-- trigger bm_rules_adhoc_audit_update
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS `bm_rules_adhoc_audit_update`$$

CREATE TRIGGER `bm_rules_adhoc_audit_update` AFTER UPDATE ON `rules_adhoc`
FOR EACH ROW
INSERT INTO audit_rules_adhoc (`change_seq`,`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`rule_duration`,`changed_by`,`action`,`change_time`,`valid_from`,`valid_to`) 
                        VALUES (NULL, NEW.`rule_id`, NEW.`rule_name`, NEW.`rule_type`, NEW.`rule_repeat`, NEW.`rule_duration`, USER(), 'U', NOW(),NEW.`valid_from`,NEW.`valid_to`);

$$

-- -----------------------------------------------------
-- trigger bm_rules_adhoc_audit_delete
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS `bm_rules_adhoc_audit_delete`$$

CREATE TRIGGER `bm_rules_adhoc_audit_delete` AFTER DELETE ON `rules_adhoc`
FOR EACH ROW
INSERT INTO audit_rules_adhoc (`change_seq`,`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`rule_duration`,`changed_by`,`action`,`change_time`,`valid_from`,`valid_to`) 
                        VALUES (NULL, OLD.`rule_id`, OLD.`rule_name`, OLD.`rule_type`, OLD.`rule_repeat`,OLD.`rule_duration`, USER(), 'D', NOW(),OLD.`valid_from`,OLD.`valid_to`);

$$