CREATE DATABASE `cook_app`;

USE `cook_app`;

CREATE TABLE `cook_app`.`recipe` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NULL,
  `serving_count` INT NULL,
  `cook_time` TIME NULL,
  `cook_temperature` VARCHAR(20) NULL,
  `instructions` TEXT NULL,
  `chef_email` VARCHAR(100) NULL,
  `status` ENUM('active','inactive') NULL DEFAULT 'active',
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`));

CREATE TABLE `cook_app`.`recipe_ingredient` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `recipe_id` INT NULL,
  `ingredient_name` VARCHAR(255) NULL,
  `quantity` FLOAT NULL,
  `status` ENUM('active','inactive') NULL DEFAULT 'active',
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `recipe_id_idx` (`recipe_id` ASC),
  CONSTRAINT `recipe_id`
    FOREIGN KEY (`recipe_id`)
    REFERENCES `cook_app`.`recipe` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

