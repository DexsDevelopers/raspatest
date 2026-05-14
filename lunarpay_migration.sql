-- ============================================================
-- MIGRAÇÃO: Integração LunarPay
-- Execute este script UMA ÚNICA VEZ no seu banco de dados.
-- ============================================================

-- 1. Criar tabela de credenciais LunarPay
CREATE TABLE IF NOT EXISTS `lunarpay` (
  `id`         int(11)      NOT NULL AUTO_INCREMENT,
  `api_key`    varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp    NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp    NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserir/atualizar com a API Key configurada
INSERT INTO `lunarpay` (`id`, `api_key`) VALUES (1, 'ghost_a4b73df409d1eb56f6e22ed063e8b913369226f89fc169cb')
  ON DUPLICATE KEY UPDATE `api_key` = 'ghost_a4b73df409d1eb56f6e22ed063e8b913369226f89fc169cb';

-- 2. Atualizar coluna gateway da tabela gateway para VARCHAR (sem enum SigiloPay)
ALTER TABLE `gateway`
  MODIFY `active` varchar(50) NOT NULL DEFAULT 'lunarpay';

-- 3. Ativar LunarPay como gateway padrão
UPDATE `gateway` SET `active` = 'lunarpay' WHERE id = 1;

-- 4. Atualizar coluna gateway da tabela depositos para VARCHAR
ALTER TABLE `depositos`
  MODIFY `gateway` varchar(50) NOT NULL DEFAULT 'lunarpay';

-- 5. Atualizar coluna gateway da tabela saques para VARCHAR
ALTER TABLE `saques`
  MODIFY `gateway` varchar(50) DEFAULT 'lunarpay';

-- ============================================================
-- Após executar: acesse Admin → Gateway e cole sua API Key.
-- Configure o webhook no painel LunarPay apontando para:
--   https://SEU_DOMINIO/callback/lunarpay.php
-- ============================================================
