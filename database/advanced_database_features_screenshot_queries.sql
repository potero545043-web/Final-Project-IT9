USE `it9_project-db`;

SHOW TRIGGERS
WHERE `Trigger` IN (
    'before_items_insert_defaults',
    'after_claims_insert_mark_item_pending',
    'after_claims_update_resolve_item',
    'after_found_reports_insert_mark_item_found'
);

SHOW FULL TABLES
WHERE `Table_type` = 'VIEW';

SHOW CREATE VIEW `vw_items_overview`;
SHOW CREATE VIEW `vw_claims_overview`;
SHOW CREATE VIEW `vw_lost_found_reports`;
