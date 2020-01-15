SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

DROP TABLE IF EXISTS `prefix_booki_project` ;
DROP TABLE IF EXISTS `prefix_booki_cascading_list` ;
DROP TABLE IF EXISTS `prefix_booki_cascading_item` ;
DROP TABLE IF EXISTS `prefix_booki_calendar` ;
DROP TABLE IF EXISTS `prefix_booki_calendar_day` ;
DROP TABLE IF EXISTS `prefix_booki_coupons` ;
DROP TABLE IF EXISTS `prefix_booki_event_log` ;
DROP TABLE IF EXISTS `prefix_booki_form_element` ;
DROP TABLE IF EXISTS `prefix_booki_optional` ;
DROP TABLE IF EXISTS `prefix_booki_order` ;
DROP TABLE IF EXISTS `prefix_booki_order_cascading_item` ;
DROP TABLE IF EXISTS `prefix_booki_order_days` ;
DROP TABLE IF EXISTS `prefix_booki_order_form_elements` ;
DROP TABLE IF EXISTS `prefix_booki_order_optionals` ;
DROP TABLE IF EXISTS `prefix_booki_settings` ;
DROP TABLE IF EXISTS `prefix_booki_quantity_element` ;
DROP TABLE IF EXISTS `prefix_booki_quantity_element_item` ;
DROP TABLE IF EXISTS `prefix_booki_order_quantity_element` ;
DROP TABLE IF EXISTS `prefix_booki_quantity_element_calendar` ;
DROP TABLE IF EXISTS `prefix_booki_quantity_element_calendarday` ;
DROP TABLE IF EXISTS `prefix_booki_roles` ;
DROP TABLE IF EXISTS `prefix_booki_gcal` ;
DROP TABLE IF EXISTS `prefix_booki_gcal_projects` ;
DROP TABLE IF EXISTS `prefix_booki_gcal_events` ;
DROP TABLE IF EXISTS `prefix_booki_trashed` ;
DROP TABLE IF EXISTS `prefix_booki_trashed_project` ;
DROP TABLE IF EXISTS `prefix_booki_reminders` ;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;