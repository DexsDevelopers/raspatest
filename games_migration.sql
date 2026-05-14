-- ============================================================
-- MIGRAÇÃO: Casa de Apostas (Games)
-- Execute APÓS lunarpay_migration.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `bets` (
  `id`         bigint(20)      NOT NULL AUTO_INCREMENT,
  `user_id`    int(11)         NOT NULL,
  `game`       varchar(30)     NOT NULL,
  `amount`     decimal(10,2)   NOT NULL,
  `multiplier` decimal(12,4)   DEFAULT NULL,
  `profit`     decimal(10,2)   DEFAULT 0,
  `status`     enum('active','won','lost','cancelled') DEFAULT 'active',
  `bet_data`   json            DEFAULT NULL,
  `created_at` timestamp       NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user`   (`user_id`),
  KEY `idx_game`   (`game`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `crash_rounds` (
  `id`               bigint(20)    NOT NULL AUTO_INCREMENT,
  `server_seed`      varchar(64)   NOT NULL,
  `server_seed_hash` varchar(64)   NOT NULL,
  `crash_point`      decimal(12,4) NOT NULL,
  `status`           enum('waiting','running','crashed') DEFAULT 'waiting',
  `created_at_ms`    bigint(20)    NOT NULL,
  `started_at_ms`    bigint(20)    DEFAULT NULL,
  `crashed_at`       timestamp     NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `crash_bets` (
  `id`        bigint(20)    NOT NULL AUTO_INCREMENT,
  `round_id`  bigint(20)    NOT NULL,
  `user_id`   int(11)       NOT NULL,
  `amount`    decimal(10,2) NOT NULL,
  `cashout_at` decimal(12,4) DEFAULT NULL,
  `profit`    decimal(10,2) DEFAULT 0,
  `status`    enum('pending','won','lost') DEFAULT 'pending',
  `created_at` timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_round`  (`round_id`),
  KEY `idx_user`   (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_seeds` (
  `id`               bigint(20)   NOT NULL AUTO_INCREMENT,
  `user_id`          int(11)      NOT NULL UNIQUE,
  `server_seed`      varchar(64)  NOT NULL,
  `server_seed_hash` varchar(64)  NOT NULL,
  `client_seed`      varchar(64)  NOT NULL,
  `nonce`            int(11)      NOT NULL DEFAULT 0,
  `updated_at`       timestamp    NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mines_games` (
  `id`          bigint(20)    NOT NULL AUTO_INCREMENT,
  `user_id`     int(11)       NOT NULL,
  `amount`      decimal(10,2) NOT NULL,
  `mines_count` int(2)        NOT NULL DEFAULT 3,
  `mines_grid`  json          NOT NULL,
  `revealed`    json          DEFAULT NULL,
  `status`      enum('active','won','lost') DEFAULT 'active',
  `created_at`  timestamp     NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user`   (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
