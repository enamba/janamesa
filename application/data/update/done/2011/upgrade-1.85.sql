-- Database upgrade v1.85
-- @author vpriem
-- @since 16.12.2010

TRUNCATE TABLE `links_from`;

ALTER TABLE `links_from`
    ADD UNIQUE `uqLinkUrl` (`linkId`, `url`);
