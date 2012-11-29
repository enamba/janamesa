-- @author afrank
-- @since 24-02-2012

insert into admin_access_rights (groupId, resourceId) 
    (select groupId, 
        (select id from admin_access_resources where action='administration_order_massstorno') 
            from admin_access_rights where resourceId=(select id from admin_access_resources where action='administration_order_storno'));