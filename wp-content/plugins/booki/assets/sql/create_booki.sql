CREATE TABLE prefix_booki_project (
  id INT(11) NOT NULL AUTO_INCREMENT,
  status BIT(1) NULL DEFAULT NULL,
  name VARCHAR(256) NOT NULL,
  bookingDaysMinimum INT(11) NULL DEFAULT NULL,
  bookingDaysLimit INT(11) NULL DEFAULT NULL,
  calendarMode TINYINT(4) NULL DEFAULT NULL,
  bookingMode TINYINT(4) NULL DEFAULT NULL,
  description BLOB NULL DEFAULT NULL,
  previewUrl VARCHAR(256) NULL DEFAULT NULL,
  tag VARCHAR(45) NULL DEFAULT NULL,
  defaultStep TINYINT(4) NULL DEFAULT NULL,
  bookingTabLabel VARCHAR(45) NULL DEFAULT NULL,
  customFormTabLabel VARCHAR(45) NULL DEFAULT NULL,
  attendeeTabLabel VARCHAR(45) NULL DEFAULT NULL,
  availableDaysLabel VARCHAR(45) NULL DEFAULT NULL,
  selectedDaysLabel VARCHAR(45) NULL DEFAULT NULL,
  bookingTimeLabel VARCHAR(45) NULL DEFAULT NULL,
  optionalItemsLabel VARCHAR(45) NULL DEFAULT NULL,
  nextLabel VARCHAR(45) NULL DEFAULT NULL,
  prevLabel VARCHAR(45) NULL DEFAULT NULL,
  addToCartLabel VARCHAR(45) NULL DEFAULT NULL,
  fromLabel VARCHAR(45) NULL DEFAULT NULL,
  toLabel VARCHAR(45) NULL DEFAULT NULL,
  proceedToLoginLabel VARCHAR(45) NULL DEFAULT NULL,
  makeBookingLabel VARCHAR(45) NULL DEFAULT NULL,
  bookingLimitLabel VARCHAR(45) NULL DEFAULT NULL,
  notifyUserEmailList BLOB NULL DEFAULT NULL,
  optionalsBookingMode TINYINT(4) NULL DEFAULT NULL,
  optionalsListingMode TINYINT(4) NULL DEFAULT NULL,
  optionalsMinimumSelection INT(11) NULL DEFAULT NULL,
  contentTop BLOB NULL DEFAULT NULL,
  contentBottom BLOB NULL DEFAULT NULL,
  bookingWizardMode TINYINT(4) NULL DEFAULT NULL,
  hideSelectedDays TINYINT(4) NULL DEFAULT NULL,
  displayAttendees TINYINT(4) NULL DEFAULT NULL,
  banList BLOB NULL DEFAULT NULL,
  defaultDateSelected TINYINT(4) NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_cascading_list (
  id INT(11) NOT NULL AUTO_INCREMENT,
  projectId INT(11) NULL DEFAULT NULL,
  label VARCHAR(45) NULL DEFAULT NULL,
  isRequired TINYINT(4) NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_cascading_item (
  id INT(11) NOT NULL AUTO_INCREMENT,
  listId INT(11) NULL DEFAULT NULL,
  parentId VARCHAR(45) NULL DEFAULT NULL,
  value VARCHAR(45) NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  lat DECIMAL(10,8) NULL DEFAULT NULL,
  lng DECIMAL(11,8) NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;
  
CREATE TABLE prefix_booki_calendar (
  id INT(11) NOT NULL AUTO_INCREMENT,
  projectId INT(11) NOT NULL,
  startDate DATE NOT NULL,
  endDate DATE NOT NULL,
  daysExcluded TEXT NULL DEFAULT NULL,
  timeExcluded TEXT NULL DEFAULT NULL,
  weekDaysExcluded TEXT NULL DEFAULT NULL,
  hours INT(11) NOT NULL,
  minutes INT(11) NOT NULL,
  hourStartInterval INT(11) NULL DEFAULT NULL,
  minuteStartInterval INT(11) NULL DEFAULT NULL,
  enableSingleHourMinuteFormat TINYINT(4) NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  period TINYINT(4) NULL DEFAULT NULL,
  seatMode TINYINT(4) NULL DEFAULT NULL,
  bookingLimit INT(11) NULL DEFAULT NULL,
  displayCounter TINYINT(4) NULL DEFAULT NULL,
  minNumDaysDeposit INT(11) NULL DEFAULT NULL,
  deposit DECIMAL(19,4) NULL DEFAULT NULL,
  bookingStartLapse INT(11) NULL DEFAULT NULL,
  bookingStartLapseMode TINYINT(4) NULL DEFAULT NULL,
  quantityElementMode TINYINT(4) NULL DEFAULT NULL,
  availabilityByQuantityElement TINYINT(4) NULL DEFAULT NULL,
  includePriceInQuantityElement TINYINT(4) NULL DEFAULT NULL,
  reminder INT(11) NULL DEFAULT NULL,
  reminderMode TINYINT(4) NULL DEFAULT NULL,
  lat DECIMAL(10,8) NULL DEFAULT NULL,
  lng DECIMAL(11,8) NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_calendar_day (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  calendarId INT(11) NOT NULL,
  cost DECIMAL(19,4) NOT NULL,
  day DATE NOT NULL,
  timeExcluded TEXT NULL DEFAULT NULL,
  hours INT(11) NOT NULL,
  minutes INT(11) NOT NULL,
  hourStartInterval INT(11) NULL DEFAULT NULL,
  minuteStartInterval INT(11) NULL DEFAULT NULL,
  seasonName VARCHAR(45) NULL DEFAULT NULL,
  minNumDaysDeposit INT(11) NULL DEFAULT NULL,
  deposit DECIMAL(19,4) NULL DEFAULT NULL,
  seatMode TINYINT(4) NULL DEFAULT NULL,
  bookingLimit INT(11) NULL DEFAULT NULL,
  lat DECIMAL(10,8) NULL DEFAULT NULL,
  lng DECIMAL(11,8) NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_coupons (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  projectId INT(11) NULL DEFAULT '-1',
  code CHAR(40) NULL DEFAULT NULL,
  couponType TINYINT(4) NULL DEFAULT NULL,
  discount DECIMAL(19,4) NULL DEFAULT NULL,
  orderMinimum DECIMAL(19,4) NULL DEFAULT NULL,
  expirationDate DATETIME NULL DEFAULT NULL,
  emailedTo VARCHAR(256) NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_event_log (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  entryDate DATETIME NULL DEFAULT NULL,
  data BLOB NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_form_element (
  id INT(11) NOT NULL AUTO_INCREMENT,
  projectId INT(11) NOT NULL,
  label VARCHAR(256) NOT NULL,
  elementType INT(11) NULL DEFAULT '0',
  lineSeparator BIT(1) NULL DEFAULT NULL,
  rowindex INT(11) NULL DEFAULT '1',
  colindex INT(11) NULL DEFAULT '1',
  className VARCHAR(50) NULL DEFAULT NULL,
  value VARCHAR(256) NULL DEFAULT NULL,
  bindingData BLOB NULL DEFAULT NULL,
  validation BLOB NULL DEFAULT NULL,
  once BIT(1) NULL DEFAULT NULL,
  capability TINYINT(4) NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_optional (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  projectId INT(11) NOT NULL,
  name VARCHAR(256) NOT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_order (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  userId BIGINT(20) UNSIGNED NOT NULL,
  orderDate DATETIME NOT NULL,
  paymentDate DATETIME NULL DEFAULT NULL,
  status TINYINT(4) NOT NULL,
  token VARCHAR(256) NULL DEFAULT NULL,
  transactionId VARCHAR(256) NULL DEFAULT NULL,
  note BLOB NULL DEFAULT NULL,
  totalAmount DECIMAL(19,4) NULL DEFAULT NULL,
  currency VARCHAR(3) NULL DEFAULT NULL,
  discount DECIMAL(19,4) NULL DEFAULT NULL,
  tax DECIMAL(19,4) NULL DEFAULT NULL,
  invoiceNotification INT(11) NULL DEFAULT NULL,
  refundNotification INT(11) NULL DEFAULT NULL,
  refundAmount DECIMAL(19,4) NULL DEFAULT NULL,
  timezone VARCHAR(256) NULL DEFAULT NULL,
  handlerUserId BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  isRegistered TINYINT(4) NOT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_order_cascading_item (
  id INT(11) NOT NULL AUTO_INCREMENT,
  orderId INT(11) NULL DEFAULT NULL,
  projectId INT(11) NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  handlerUserId BIGINT(20) NULL DEFAULT NULL,
  status TINYINT(4) NULL DEFAULT NULL,
  count INT(11) NULL DEFAULT NULL,
  value VARCHAR(45) NULL DEFAULT NULL,
  deposit DECIMAL(19,4) NULL DEFAULT NULL,
  trails BLOB NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_order_days (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  orderId BIGINT(20) NULL DEFAULT NULL,
  projectId INT(11) NULL DEFAULT NULL,
  bookingDate DATETIME NULL DEFAULT NULL,
  hourStart INT(11) NULL DEFAULT NULL,
  minuteStart INT(11) NULL DEFAULT NULL,
  hourEnd INT(11) NULL DEFAULT NULL,
  minuteEnd INT(11) NULL DEFAULT NULL,
  enableSingleHourMinuteFormat TINYINT(4) NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  status TINYINT(4) NOT NULL,
  handlerUserId BIGINT(20) NULL DEFAULT NULL,
  deposit DECIMAL(19,4) NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_order_form_elements (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  orderId BIGINT(20) NULL DEFAULT NULL,
  projectId INT(11) NULL DEFAULT NULL,
  label VARCHAR(256) NULL DEFAULT NULL,
  elementType INT(11) NULL DEFAULT NULL,
  rowIndex INT(11) NULL DEFAULT '1',
  colIndex INT(11) NULL DEFAULT '1',
  value TEXT NULL DEFAULT NULL,
  capability TINYINT(4) NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_order_optionals (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  orderId BIGINT(20) NULL DEFAULT NULL,
  projectId INT(11) NULL DEFAULT NULL,
  name VARCHAR(256) NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  status TINYINT(4) NULL DEFAULT NULL,
  handlerUserId BIGINT(20) NULL DEFAULT NULL,
  count INT(11) NULL DEFAULT NULL,
  deposit DECIMAL(19,4) NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_settings (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(256) NULL DEFAULT NULL,
  data BLOB NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_quantity_element (
  id INT NOT NULL AUTO_INCREMENT,
  projectId INT NULL,
  quantity INT NULL,
  name VARCHAR(256) NULL,
  cost DECIMAL(19,4) NULL,
  displayMode TINYINT(4) NULL,
  bookingMode TINYINT(4) NULL,
  isRequired TINYINT(4) NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_quantity_element_item (
  id INT NOT NULL AUTO_INCREMENT,
  elementId INT NULL,
  quantityIndex INT NULL,
  cost DECIMAL(19,4) NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_order_quantity_element (
  id INT NOT NULL AUTO_INCREMENT,
  projectId INT NULL,
  orderId BIGINT(20) NULL,
  orderDayId INT NULL,
  elementId INT NULL,
  name VARCHAR(256) NULL,
  cost DECIMAL(19,4) NULL,
  status TINYINT(4) NULL,
  handlerUserId BIGINT(20) NULL,
  deposit DECIMAL(19,4) NULL,
  quantity INT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_quantity_element_calendar (
  calendarId INT NOT NULL,
  quantityElementId INT NOT NULL,
  PRIMARY KEY  (calendarId,quantityElementId)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_quantity_element_calendarday (
  calendarDayId INT NOT NULL,
  quantityElementId INT NOT NULL,
  PRIMARY KEY  (calendarDayId,quantityElementId)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_roles (
  id INT NOT NULL AUTO_INCREMENT,
  userId BIGINT NULL,
  projectId INT NULL,
  role TINYINT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_gcal (
  id INT NOT NULL AUTO_INCREMENT,
  userId BIGINT NULL,
  applicationName VARCHAR(256) NULL,
  clientId BLOB NULL DEFAULT NULL,
  clientSecret VARCHAR(256) NULL,
  accessToken BLOB NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_gcal_projects (
  calendarId VARCHAR(128) NOT NULL,
  projectId INT NOT NULL,
  userId BIGINT NOT NULL,
  PRIMARY KEY  (calendarId,projectId,userId)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_gcal_events (
  eventId VARCHAR(128) NOT NULL,  
  calendarId VARCHAR(128) NOT NULL,
  bookedDayId INT NOT NULL,  
  orderId BIGINT NOT NULL,
  PRIMARY KEY  (eventId)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_trashed (
  id INT NOT NULL AUTO_INCREMENT,
  orderId INT NULL,
  deletionDate DATETIME NULL DEFAULT NULL,
  data BLOB NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;
  
CREATE TABLE prefix_booki_trashed_project (
  trashId INT NOT NULL,
  projectId INT NOT NULL,
  PRIMARY KEY  (trashId,projectId)
  ) charset_collate_placeholder;

CREATE TABLE prefix_booki_reminders (
  id INT NOT NULL AUTO_INCREMENT,
  orderId INT DEFAULT NULL,
  firstname VARCHAR(256) DEFAULT NULL,
  lastname VARCHAR(256) DEFAULT NULL,
  email VARCHAR(256) DEFAULT NULL,
  sentDate DATETIME NULL DEFAULT NULL,
  PRIMARY KEY  (id)
  ) charset_collate_placeholder;