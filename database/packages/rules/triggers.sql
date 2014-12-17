-- -----------------------------------------------------
-- Triggers for package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- trigger bm_rules_audit_insert
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS `bm_rules_audit_insert`$$

CREATE TRIGGER `bm_rules_audit_insert` AFTER INSERT ON `rules`
FOR EACH ROW
INSERT INTO audit_rules (`change_seq`
                         ,`rule_id`
                         ,`rule_name`
                         ,`rule_type`
                         ,`rule_repeat`
                         ,`repeat_minute`
                         ,`repeat_hour`
                         ,`repeat_dayofweek`
                         ,`repeat_dayofmonth`
                         ,`repeat_month`
                         ,`start_from`
                         ,`end_at`
                         ,`schedule_group_id`
                         ,`membership_id`
                         ,`opening_slot_id`
                         ,`closing_slot_id`
                         ,`valid_from`
                         ,`valid_to`
                         ,`rule_duration`
                         ,`changed_by`
                         ,`action`
                         ,`change_time`) 
VALUES (NULL
        , NEW.`rule_id`
        , NEW.`rule_name`
        , NEW.`rule_type`
        , NEW.`rule_repeat`
        , NEW.`repeat_minute`
        , NEW.`repeat_hour`
        , NEW.`repeat_dayofweek`
        , NEW.`repeat_dayofmonth`
        , NEW.`repeat_month`
        , NEW.`start_from`
        , NEW.`end_at`
        , NEW.`schedule_group_id`
        , NEW.`membership_id`
        , NEW.`opening_slot_id`
        , NEW.`closing_slot_id`
        , NEW.`valid_from`
        , NEW.`valid_to`
        , NEW.`rule_duration`
        , USER()
        , 'I'
        , NOW());
$$

-- -----------------------------------------------------
-- trigger bm_rules_audit_update
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS `bm_rules_audit_update`$$

CREATE TRIGGER `bm_rules_audit_update` AFTER UPDATE ON `rules`
FOR EACH ROW
INSERT INTO audit_rules (`change_seq`
                         ,`rule_id`
                         ,`rule_name`
                         ,`rule_type`
                         ,`rule_repeat`
                         ,`repeat_minute`
                         ,`repeat_hour`
                         ,`repeat_dayofweek`
                         ,`repeat_dayofmonth`
                         ,`repeat_month`
                         ,`start_from`
                         ,`end_at`
                         ,`schedule_group_id`
                         ,`membership_id`
                         ,`opening_slot_id`
                         ,`closing_slot_id`
                         ,`valid_from`
                         ,`valid_to`
                         ,`rule_duration`
                         ,`changed_by`
                         ,`action`
                         ,`change_time`) 
VALUES (NULL
        , NEW.`rule_id`
        , NEW.`rule_name`
        , NEW.`rule_type`
        , NEW.`rule_repeat`
        , NEW.`repeat_minute`
        , NEW.`repeat_hour`
        , NEW.`repeat_dayofweek`
        , NEW.`repeat_dayofmonth`
        , NEW.`repeat_month`
        , NEW.`start_from`
        , NEW.`end_at`
        , NEW.`schedule_group_id`
        , NEW.`membership_id` 
        , NEW.`opening_slot_id`
        , NEW.`closing_slot_id`
        , NEW.`valid_from`
        , NEW.`valid_to`
        , NEW.`rule_duration`
        ,USER()
        ,'U'
        ,NOW());
$$

-- -----------------------------------------------------
-- trigger bm_rules_audit_delete
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS `bm_rules_audit_delete`$$

CREATE TRIGGER `bm_rules_audit_delete` AFTER DELETE ON `rules`
FOR EACH ROW
INSERT INTO audit_rules (`change_seq`
                         ,`rule_id`
                         ,`rule_name`
                         ,`rule_type`
                         ,`rule_repeat`
                         ,`repeat_minute`
                         ,`repeat_hour`
                         ,`repeat_dayofweek`
                         ,`repeat_dayofmonth`
                         ,`repeat_month`
                         ,`start_from`
                         ,`end_at`
                         ,`schedule_group_id`
                         ,`membership_id`
                         ,`opening_slot_id`
                         ,`closing_slot_id`
                         ,`valid_from`
                         ,`valid_to`
                         ,`rule_duration`
                         ,`changed_by`
                         ,`action`
                         ,`change_time`) 
VALUES (NULL
        , OLD.`rule_id`
        , OLD.`rule_name`
        , OLD.`rule_type`
        , OLD.`rule_repeat`
        , OLD.`repeat_minute`
        , OLD.`repeat_hour`
        , OLD.`repeat_dayofweek`
        , OLD.`repeat_dayofmonth`
        , OLD.`repeat_month`
        , OLD.`start_from`
        , OLD.`end_at`
        , OLD.`schedule_group_id`
        , OLD.`membership_id`
        , OLD.`opening_slot_id`
        , OLD.`closing_slot_id`
        , OLD.`valid_from`
        , OLD.`valid_to`
        , OLD.`rule_duration`
        , USER()
        ,'D'
        ,NOW());
$$
