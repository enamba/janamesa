INSERT INTO admin_access_resources (action)
VALUES ('administration_definitions');

INSERT IGNORE INTO `admin_access_rights` (groupId, resourceId)
SELECT aar.id as groupId, 
(SELECT id from `admin_access_resources` where action = 'administration_definitions') as resourceId
FROM `admin_access_groups` aar;
