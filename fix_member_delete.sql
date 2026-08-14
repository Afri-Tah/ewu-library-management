-- Run this once against your EXISTING database if you don't want to
-- re-import the whole database.sql from scratch. It only fixes the
-- foreign key so deleting a member cascades to their borrows/fines.

USE ewu_library;

ALTER TABLE `borrows` DROP FOREIGN KEY `borrows_ibfk_1`;

ALTER TABLE `borrows`
  ADD CONSTRAINT `borrows_ibfk_1`
  FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`)
  ON DELETE CASCADE;
