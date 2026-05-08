USE `it9_project-db`;

DROP TRIGGER IF EXISTS `before_items_insert_defaults`;
DROP TRIGGER IF EXISTS `after_claims_insert_mark_item_pending`;
DROP TRIGGER IF EXISTS `after_claims_update_resolve_item`;
DROP TRIGGER IF EXISTS `after_found_reports_insert_mark_item_found`;

DROP VIEW IF EXISTS `vw_items_overview`;
DROP VIEW IF EXISTS `vw_claims_overview`;
DROP VIEW IF EXISTS `vw_lost_found_reports`;

DELIMITER $$

CREATE TRIGGER `before_items_insert_defaults`
BEFORE INSERT ON `items`
FOR EACH ROW
BEGIN
    IF NEW.`status` IS NULL OR NEW.`status` = '' THEN
        SET NEW.`status` = 'open';
    END IF;

    IF NEW.`reported_at` IS NULL THEN
        SET NEW.`reported_at` = NOW();
    END IF;
END$$

CREATE TRIGGER `after_claims_insert_mark_item_pending`
AFTER INSERT ON `claims`
FOR EACH ROW
BEGIN
    IF NEW.`status` = 'pending' THEN
        UPDATE `items`
        SET `status` = 'under_review',
            `updated_at` = NOW()
        WHERE `id` = NEW.`item_id`
          AND `status` = 'open';
    END IF;
END$$

CREATE TRIGGER `after_claims_update_resolve_item`
AFTER UPDATE ON `claims`
FOR EACH ROW
BEGIN
    IF NEW.`status` = 'approved' AND OLD.`status` <> 'approved' THEN
        UPDATE `items`
        SET `status` = 'claimed',
            `updated_at` = NOW()
        WHERE `id` = NEW.`item_id`;
    END IF;
END$$

CREATE TRIGGER `after_found_reports_insert_mark_item_found`
AFTER INSERT ON `found_reports`
FOR EACH ROW
BEGIN
    IF NEW.`item_id` IS NOT NULL THEN
        UPDATE `items`
        SET `status` = 'under_review',
            `updated_at` = NOW()
        WHERE `id` = NEW.`item_id`
          AND `status` = 'open';
    END IF;
END$$

DELIMITER ;

CREATE VIEW `vw_items_overview` AS
SELECT
    `i`.`id` AS `item_id`,
    `i`.`type` AS `item_type`,
    `i`.`category` AS `category`,
    `i`.`title` AS `item_title`,
    `i`.`description` AS `description`,
    `i`.`location` AS `location`,
    `i`.`reported_at` AS `reported_at`,
    `i`.`status` AS `item_status`,
    `i`.`reward_amount` AS `reward_amount`,
    `u`.`id` AS `reporter_id`,
    `u`.`name` AS `reporter_name`,
    `u`.`email` AS `reporter_email`,
    `u`.`phone` AS `reporter_phone`
FROM `items` `i`
JOIN `users` `u`
    ON `i`.`user_id` = `u`.`id`
WHERE `i`.`deleted_at` IS NULL;

CREATE VIEW `vw_claims_overview` AS
SELECT
    `c`.`id` AS `claim_id`,
    `c`.`item_id` AS `item_id`,
    `i`.`title` AS `item_title`,
    `i`.`type` AS `item_type`,
    `i`.`status` AS `item_status`,
    `owner`.`name` AS `item_owner_name`,
    `claimant`.`id` AS `claimant_id`,
    `claimant`.`name` AS `claimant_name`,
    `claimant`.`email` AS `claimant_email`,
    `c`.`contact_name` AS `claim_contact_name`,
    `c`.`contact_email` AS `claim_contact_email`,
    `c`.`contact_phone` AS `claim_contact_phone`,
    `c`.`status` AS `claim_status`,
    `c`.`created_at` AS `claim_date`
FROM `claims` `c`
JOIN `items` `i`
    ON `c`.`item_id` = `i`.`id`
JOIN `users` `claimant`
    ON `c`.`claimant_id` = `claimant`.`id`
JOIN `users` `owner`
    ON `i`.`user_id` = `owner`.`id`;

CREATE VIEW `vw_lost_found_reports` AS
SELECT
    `lr`.`lost_report_id` AS `report_id`,
    'lost' AS `report_type`,
    NULL AS `item_id`,
    `lr`.`description` AS `description`,
    `lr`.`location` AS `location`,
    `lr`.`date_reported` AS `date_reported`,
    `lr`.`status` AS `report_status`,
    `u`.`id` AS `user_id`,
    `u`.`name` AS `reporter_name`,
    `u`.`email` AS `reporter_email`
FROM `lost_reports` `lr`
JOIN `users` `u`
    ON `lr`.`user_id` = `u`.`id`
UNION ALL
SELECT
    `fr`.`found_report_id` AS `report_id`,
    'found' AS `report_type`,
    `fr`.`item_id` AS `item_id`,
    `fr`.`description` AS `description`,
    `fr`.`location` AS `location`,
    `fr`.`date_reported` AS `date_reported`,
    `fr`.`status` AS `report_status`,
    `u`.`id` AS `user_id`,
    `u`.`name` AS `reporter_name`,
    `u`.`email` AS `reporter_email`
FROM `found_reports` `fr`
JOIN `users` `u`
    ON `fr`.`user_id` = `u`.`id`;
