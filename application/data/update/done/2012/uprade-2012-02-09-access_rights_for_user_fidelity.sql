
-- @author alex
-- @since 09.02.12

insert into admin_access_rights (groupId, resourceId) 
    (select groupId, 
        (select id from admin_access_resources where action='administration_user_edit_fidelity') 
            from admin_access_rights where resourceId=(select id from admin_access_resources where action='administration_user_edit_assoc'));