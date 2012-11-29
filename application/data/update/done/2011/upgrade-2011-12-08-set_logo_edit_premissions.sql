-- @author alex
-- 

insert into admin_access_resources (`action`) values ('administration_service_edit_logo');

insert into admin_access_rights (`groupId`, `resourceId`) values (
    (select id from admin_access_groups where name = 'Design'),
    (select id from admin_access_resources where action ='administration_service_edit_logo')
);

insert into admin_access_rights (`groupId`, `resourceId`) values (
    (select id from admin_access_groups where name = 'Admin_ohne_statistik'),
    (select id from admin_access_resources where action ='administration_service_edit_logo')
);