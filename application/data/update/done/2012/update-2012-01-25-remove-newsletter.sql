-- removes deprecated stuff
-- @author allen
-- @since 25.01.12

delete from admin_access_resources where id in (612122, 612215, 612290) and `action` like '%news%';
delete from admin_access_rights where resourceId in (612122, 612215, 612290);