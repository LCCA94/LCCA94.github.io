CREATE DATABASE IF NOT EXISTS everwear
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE everwear;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  admin TINYINT(1) NOT NULL DEFAULT 0,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET @tem_admin = (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'usuarios'
    AND COLUMN_NAME = 'admin'
);
SET @sql_admin = IF(
  @tem_admin = 0,
  'ALTER TABLE usuarios ADD COLUMN admin TINYINT(1) NOT NULL DEFAULT 0 AFTER senha_hash',
  'SELECT 1'
);
PREPARE stmt_admin FROM @sql_admin;
EXECUTE stmt_admin;
DEALLOCATE PREPARE stmt_admin;

CREATE TABLE IF NOT EXISTS produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(160) NOT NULL,
  slug VARCHAR(160) NOT NULL UNIQUE,
  preco DECIMAL(10,2) NOT NULL,
  imagem VARCHAR(255) NOT NULL,
  cor VARCHAR(80) NOT NULL,
  descricao TEXT NOT NULL,
  estoque INT NOT NULL DEFAULT 0,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS carrinho_itens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL,
  sessao_id VARCHAR(128) NULL,
  produto_id INT NOT NULL,
  tamanho VARCHAR(10) DEFAULT '',
  quantidade INT NOT NULL DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_carrinho_usuario (usuario_id),
  INDEX idx_carrinho_sessao (sessao_id),
  CONSTRAINT fk_carrinho_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_carrinho_produto FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  email VARCHAR(180) NOT NULL,
  cep VARCHAR(20) NOT NULL,
  endereco VARCHAR(255) NOT NULL,
  numero VARCHAR(40) NOT NULL,
  complemento VARCHAR(160) DEFAULT '',
  bairro VARCHAR(120) NOT NULL,
  cidade VARCHAR(120) NOT NULL,
  estado VARCHAR(2) NOT NULL,
  pagamento VARCHAR(60) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'confirmado',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedido_itens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  produto_id INT NOT NULL,
  nome_produto VARCHAR(160) NOT NULL,
  preco_unitario DECIMAL(10,2) NOT NULL,
  quantidade INT NOT NULL,
  tamanho VARCHAR(10) DEFAULT '',
  cor VARCHAR(80) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_pedido_itens_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  CONSTRAINT fk_pedido_itens_produto FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB;

INSERT INTO produtos (id, nome, slug, preco, imagem, cor, descricao, estoque, ativo)
VALUES
  (1, 'Camiseta EcoWear', 'camiseta-ecowear', 99.90, 'novacamisamarrom.png', 'Marrom', 'Camiseta sofisticada com visual clean e acabamento premium.', 20, 1),
  (2, 'Camisa DryFit', 'camisa-dryfit', 119.90, 'camisavv.png', 'Verde', 'Modelo esportivo com presença forte e estilo moderno.', 20, 1),
  (3, 'Camisa Bege Premium', 'camisa-bege-premium', 129.90, 'novacamisaareia.png', 'Areia', 'Versão clara e elegante para um visual refinado.', 20, 1)
ON DUPLICATE KEY UPDATE
  nome = VALUES(nome),
  preco = VALUES(preco),
  imagem = VALUES(imagem),
  cor = VALUES(cor),
  descricao = VALUES(descricao),
  ativo = VALUES(ativo);

INSERT INTO usuarios (nome, email, senha_hash, admin)
VALUES ('Administrador', 'admin@everwear.com', '$2y$10$u9VfJOd3z4lvSdIlEy5Bie.6DNTn2UP./g0/CSu5vBmKJUsz76F/i', 1)
ON DUPLICATE KEY UPDATE
  nome = VALUES(nome),
  senha_hash = VALUES(senha_hash),
  admin = 1;
