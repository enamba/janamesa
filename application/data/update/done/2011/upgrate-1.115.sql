/**
 * @author vpriem
 * @since 17.02.2011
 */
ALTER TABLE `courier_plz` ADD UNIQUE `uqCourierPlz` (`courierId` , `plz`);