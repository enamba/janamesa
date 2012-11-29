-- @author alex
-- @since 17.07.2012

insert ignore into admin_access_resources (`action`) values ('administration_service_edit_notepad');

insert into `admin_access_rights` (`groupId`, `resourceId`) 
(select groupId, (select id from admin_access_resources where `action`='administration_service_edit_notepad') from `admin_access_rights` where resourceId=
                    (select id from `admin_access_resources` where `action`='administration_service_edit_index') order by groupId);

