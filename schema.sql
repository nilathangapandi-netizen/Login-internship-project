-- schema.sql
-- Run this once to set up the MySQL databases used by the app

CREATE DATABASE IF NOT EXISTS user_profile_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE DATABASE IF NOT EXISTS admin_dashboard_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE user_profile_db;

CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120)  NOT NULL,
  email         VARCHAR(180)  NOT NULL UNIQUE,
  username      VARCHAR(60)   NOT NULL UNIQUE,
  password_hash VARCHAR(255)  NOT NULL,
  age           INT NULL,
  dob           DATE NULL,
  country       VARCHAR(80)  NULL,
  state         VARCHAR(80)  NULL,
  city          VARCHAR(80)  NULL,
  pincode       VARCHAR(20)  NULL,
  contact       VARCHAR(30)  NULL,
  bio           TEXT NULL,
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

USE admin_dashboard_db;

CREATE TABLE IF NOT EXISTS user_profiles (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL UNIQUE,
  name       VARCHAR(120)  NULL,
  email      VARCHAR(180)  NULL,
  username   VARCHAR(60)   NULL,
  age        INT NULL,
  dob        DATE NULL,
  country    VARCHAR(80)  NULL,
  state      VARCHAR(80)  NULL,
  city       VARCHAR(80)  NULL,
  pincode    VARCHAR(20)  NULL,
  contact    VARCHAR(30)  NULL,
  bio        TEXT NULL,
  created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
